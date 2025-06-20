<?php

// Efficacité globale du système (pertes incluses)
define('SYSTEM_EFFICIENCY', 0.85); // Augmenté à 85% pour refléter les systèmes modernes

// Irradiation solaire par défaut (kWh/m²/jour)
define('IRRADIATION', 5.5); // Valeur moyenne pour le Togo

// Dimensions des panneaux solaires (en mètres)
define('PANEL_LENGTH', 1.7); // Longueur d'un panneau
define('PANEL_WIDTH', 1.0);  // Largeur d'un panneau
define('SURFACE_FACTOR', 1.2); // Facteur de surface pour inclure les espaces entre panneaux

// Puissance des panneaux solaires (en W)
define('PANEL_POWER_MONO', 400); // 400W pour les panneaux monocristallins
define('PANEL_POWER_POLY', 380); // 380W pour les panneaux polycristallins
define('PANEL_POWER_AMORPHE', 200); // 200W pour les panneaux amorphes

// Profondeur de décharge (Depth of Discharge) des batteries
define('DOD_LITHIUM', 0.8); // 80% pour les batteries lithium
define('DOD_PLOMB', 0.5);   // 50% pour les batteries plomb-acide

// Efficacité des batteries
define('BATTERY_EFFICIENCY_LITHIUM', 0.95); // 95% pour les batteries lithium
define('BATTERY_EFFICIENCY_PLOMB', 0.85);  // 85% pour les batteries plomb-acide

// Courant de court-circuit (Isc) des panneaux solaires (en ampères)
define('ISC_MONO', 10.2); // Monocristallin
define('ISC_POLY', 9.8);  // Polycristallin
define('ISC_AMORPHE', 3.2); // Amorphe

// Tension nominale des panneaux (en volts)
define('PANEL_VOLTAGE', 36); // Tension nominale standard d'un panneau

// Efficacité des panneaux par type
define('PANEL_EFFICIENCY_MONO', 0.15); // 15% pour les panneaux monocristallins
define('PANEL_EFFICIENCY_POLY', 0.14); // 14% pour les panneaux polycristallins
define('PANEL_EFFICIENCY_AMORPHE', 0.08); // 8% pour les panneaux amorphes

// Ajout de commentaires pour une meilleure compréhension
// Ces valeurs sont basées sur des spécifications techniques standard du marché
?>