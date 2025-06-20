<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdnjs.cloudflare.com; style-src \'self\' https://cdn.jsdelivr.net \'unsafe-inline\'; img-src \'self\' data: https://via.placeholder.com;');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/errors.log');

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, __DIR__ . '/logs/errors.log');
}

function logCalculation($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, __DIR__ . '/logs/calculations.log');
}

// Fonction pour calculer l'irradiation en fonction de la latitude
function calculateIrradiation($latitude) {
    // Formule basée sur des données moyennes pour l'Afrique de l'Ouest
    $baseIrradiation = 5.5; // Moyenne pour le Togo
    $irradiation = $baseIrradiation - abs($latitude - 6.1) * 0.05;
    return max(4.0, min(6.5, $irradiation)); // Limiter entre 4.0 et 6.5 kWh/m²/jour
}

// Fonction pour calculer l'inclinaison optimale
function calculateOptimalTilt($latitude) {
    // Formule simplifiée pour l'inclinaison optimale
    if ($latitude >= 0) {
        return round($latitude * 0.9);
    } else {
        return round(abs($latitude) * 0.9);
    }
}

// Fonction pour calculer l'azimut optimal
function calculateOptimalAzimuth($latitude) {
    // Pour l'hémisphère nord, l'azimut optimal est généralement 180° (Sud)
    return $latitude >= 0 ? 180 : 0;
}

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError('Méthode non autorisée: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Validation CSRF
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    logError('Échec de la validation CSRF');
    http_response_code(403);
    echo json_encode(['error' => 'Échec de la validation CSRF']);
    exit;
}

try {
    // Nettoyage des données d'entrée
    $appliances = isset($_POST['appliance']) && is_array($_POST['appliance']) ? array_map(function($app) {
        return [
            'name' => filter_var($app['name'] ?? '', FILTER_SANITIZE_STRING),
            'hours' => filter_var($app['hours'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
        ];
    }, $_POST['appliance']) : [];

    $latitude = filter_var($_POST['latitude'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $longitude = filter_var($_POST['longitude'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $system_type = filter_var($_POST['system-type'] ?? 'connecte', FILTER_SANITIZE_STRING);
    $system_voltage = filter_var($_POST['system-voltage'] ?? 12, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $panel_type = filter_var($_POST['panel-type'] ?? 'mono', FILTER_SANITIZE_STRING);
    $tilt = filter_var($_POST['tilt'] ?? calculateOptimalTilt($latitude), FILTER_SANITIZE_NUMBER_INT);
    $autonomy_days = filter_var($_POST['autonomy-days'] ?? 1, FILTER_SANITIZE_NUMBER_INT);
    $battery_type = filter_var($_POST['battery-type'] ?? 'lithium', FILTER_SANITIZE_STRING);
    $panel_cost = filter_var($_POST['panel-cost'] ?? 50000, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $battery_cost = filter_var($_POST['battery-cost'] ?? 28000, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $inverter_cost = filter_var($_POST['inverter-cost'] ?? 120000, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $panel_efficiency = filter_var($_POST['panel-efficiency'] ?? 15, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) / 100;
    $azimuth = filter_var($_POST['azimuth'] ?? calculateOptimalAzimuth($latitude), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $system_losses = filter_var($_POST['system-losses'] ?? 15, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) / 100;

    // Calcul automatique de l'irradiation
    $Ir = calculateIrradiation($latitude);

    // Validation des données
    $valid_system_types = ['connecte', 'autonome', 'hybride'];
    $valid_panel_types = ['mono', 'poly', 'amorphe'];
    $valid_battery_types = ['lithium', 'plomb'];
    $valid_voltages = [12, 24, 48];

    if (
        !in_array($system_type, $valid_system_types) ||
        !in_array($panel_type, $valid_panel_types) ||
        !in_array($battery_type, $valid_battery_types) ||
        !in_array($system_voltage, $valid_voltages) ||
        $latitude < -90 || $latitude > 90 ||
        $longitude < -180 || $longitude > 180 ||
        $tilt < 0 || $tilt > 90 ||
        $autonomy_days < 1 ||
        $panel_efficiency < 0.12 || $panel_efficiency > 0.19 ||
        $azimuth < -180 || $azimuth > 180 ||
        $system_losses < 0 || $system_losses > 0.5 ||
        $panel_cost < 0 || $battery_cost < 0 || $inverter_cost < 0 ||
        $Ir < 4.0 || $Ir > 6.5
    ) {
        throw new Exception('Données invalides');
    }

    // Calcul de la consommation
    $appliance_powers = [
        'refrigerateur' => 150,
        'ventilateur' => 50,
        'lampe' => 10,
        'television' => 100,
        'ordinateur' => 200
    ];

    $consumption = 0;
    $peak_power = 0;
    foreach ($appliances as $appliance) {
        if (!isset($appliance_powers[$appliance['name']]) || $appliance['hours'] < 0) {
            continue;
        }
        $power = $appliance_powers[$appliance['name']];
        $hours = floatval($appliance['hours']);
        $consumption += $power * $hours / 1000; // Convertir en kWh
        $peak_power += $power;
    }

    if ($consumption <= 0) {
        throw new Exception('Consommation invalide');
    }

    // Calculs
    $k = SYSTEM_EFFICIENCY * (1 - $system_losses);
    
    // Détermination de la puissance de référence et de l'efficacité
    switch($panel_type) {
        case 'mono':
            $P_ref = PANEL_POWER_MONO;
            $panel_efficiency = 0.15; // 15% d'efficacité pour les panneaux mono
            break;
        case 'poly':
            $P_ref = PANEL_POWER_POLY;
            $panel_efficiency = 0.14; // 14% d'efficacité pour les panneaux poly
            break;
        case 'amorphe':
            $P_ref = PANEL_POWER_AMORPHE;
            $panel_efficiency = 0.08; // 8% d'efficacité pour les panneaux amorphes
            break;
        default:
            $P_ref = PANEL_POWER_MONO;
            $panel_efficiency = 0.15;
    }
    
    $DOD = $battery_type === 'lithium' ? DOD_LITHIUM : DOD_PLOMB;
    $battery_efficiency = $battery_type === 'lithium' ? BATTERY_EFFICIENCY_LITHIUM : BATTERY_EFFICIENCY_PLOMB;
    
    // Détermination du courant de court-circuit
    switch($panel_type) {
        case 'mono':
            $Isc = ISC_MONO;
            break;
        case 'poly':
            $Isc = ISC_POLY;
            break;
        case 'amorphe':
            $Isc = ISC_AMORPHE;
            break;
        default:
            $Isc = ISC_MONO;
    }

    // Calcul de la consommation énergétique
    $E_p = $consumption / $k;
    
    // Calcul de la puissance crête nécessaire
    $P_C = $E_p / $Ir;
    
    // Calcul du nombre de panneaux nécessaires avec ajustement pour les panneaux amorphes
    if ($panel_type === 'amorphe') {
        // Les panneaux amorphes nécessitent plus de surface pour la même puissance
        $N_P = ceil($P_C / ($P_ref * $panel_efficiency) * 1.2); // Facteur de correction de 1.2
    } else {
        $N_P = ceil($P_C / ($P_ref * $panel_efficiency));
    }
    
    // Calcul de la tension de sortie d'un panneau
    $panel_voltage = 36; // Tension nominale d'un panneau en V
    
    // Calcul du nombre de panneaux en série pour atteindre la tension système
    $N_PS = ceil($system_voltage / $panel_voltage);
    
    // Calcul du nombre de panneaux en parallèle pour atteindre la puissance nécessaire
    $N_PP = ceil($N_P / $N_PS);
    
    // Vérification et ajustement de la configuration
    $total_panels = $N_PS * $N_PP;
    if ($total_panels < $N_P) {
        // Si le nombre total de panneaux est insuffisant, augmenter le nombre en parallèle
        $N_PP = ceil($N_P / $N_PS);
    }
    
    // Calcul de la puissance réelle du système
    $actual_power = $N_PS * $N_PP * $P_ref * $panel_efficiency;
    
    // Calcul de la surface totale nécessaire avec ajustement pour les panneaux amorphes
    if ($panel_type === 'amorphe') {
        $S_t = $N_PS * $N_PP * PANEL_LENGTH * PANEL_WIDTH * SURFACE_FACTOR * 1.3; // Facteur de correction de 1.3
    } else {
        $S_t = $N_PS * $N_PP * PANEL_LENGTH * PANEL_WIDTH * SURFACE_FACTOR;
    }
    
    // Calcul de la capacité de batterie nécessaire
    $battery_capacity = $system_type !== 'connecte' ? 
        ($consumption * $autonomy_days * 1000) / ($DOD * $battery_efficiency * $system_voltage) : 0;
    
    // Calcul du nombre de batteries nécessaires
    $battery_count = $system_type !== 'connecte' ? ceil($battery_capacity / 100) : 0;
    
    // Calcul du courant du contrôleur
    $controller_current = $system_type !== 'connecte' ? 1.25 * $Isc * $N_PP : 0;
    
    // Calcul des coûts
    $total_panel_cost = $panel_cost * $N_PS * $N_PP;
    $total_battery_cost = $system_type !== 'connecte' ? $battery_cost * $battery_count : 0;
    $total_inverter_cost = $system_type === 'hybride' ? $inverter_cost : 0;
    $total_cost = $total_panel_cost + $total_battery_cost + $total_inverter_cost;

    // Résultats
    $results = [
        'success' => true,
        'consumption' => round($consumption, 2),
        'peak_power' => $peak_power,
        'E_p' => round($E_p, 2),
        'P_C' => round($P_C, 2),
        'N_P' => $N_PS * $N_PP,
        'N_PS' => $N_PS,
        'N_PP' => $N_PP,
        'S_t' => round($S_t, 2),
        'battery_capacity' => round($battery_capacity, 2),
        'battery_count' => $battery_count,
        'controller_current' => round($controller_current, 2),
        'panel_cost' => round($total_panel_cost, 2),
        'battery_cost' => round($total_battery_cost, 2),
        'inverter_cost' => round($total_inverter_cost, 2),
        'total_cost' => round($total_cost, 2),
        'system_voltage' => $system_voltage,
        'irradiation' => round($Ir, 2),
        'tilt' => $tilt,
        'azimuth' => $azimuth,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'panel_type' => $panel_type,
        'panel_efficiency' => $panel_efficiency,
        'actual_power' => round($actual_power, 2)
    ];

    // Journalisation des calculs
    logCalculation(sprintf(
        "Calcul effectué pour lat: %.2f, lng: %.2f, irr: %.2f, tilt: %d, az: %.2f",
        $latitude, $longitude, $Ir, $tilt, $azimuth
    ));

    echo json_encode($results);

} catch (Exception $e) {
    logError('Erreur: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>