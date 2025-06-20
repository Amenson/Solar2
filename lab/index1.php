<?php
            session_start();
            $csrf_token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrf_token;
            ?>
 <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <title>Dimensionnement Solaire Togo</title>
    <link rel="icon" href="Amen1.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        /* Base Styles */
        body{font-family:Arial,sans-serif;background:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" opacity="0.1"><rect width="100" height="100" fill="none" stroke="#15803d" stroke-width="1"/><path d="M10,90 L90,10" stroke="#15803d" stroke-width="1"/><circle cx="50" cy="50" r="5" fill="#d4af37"/></svg>') repeat #f4f4f9;transition:.3s}

        /* Form Layout */
        #sizing-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.1);transition:.3s}

        /* Form Sections */
        .form-section{grid-column:1/-1;border-bottom:2px solid #15803d;padding-bottom:.75rem;margin-bottom:1rem}
        .form-section h3{color:#15803d;font-size:1.1rem;font-weight:600;margin-bottom:.75rem}

        /* Form Groups */
        .form-group{margin-bottom:1.5rem;position:relative}
        .form-group label{display:block;font-weight:600;color:#374151;margin-bottom:.5rem}
        .form-group input,.form-group select{width:100%;padding:.75rem;border:2px solid #d1d5db;border-radius:8px;transition:.3s;font-size:.95rem}
        .form-group input:focus,.form-group select:focus{outline:none;border-color:#15803d;box-shadow:0 0 0 3px rgba(21,128,61,.2)}

        /* Appliances Section */
        .appliances-container{position:relative;max-width:600px;margin:0 auto 4rem auto}
        #appliances{display:flex;flex-direction:column;gap:.75rem}
        .appliance{background:#f8fafc;padding:1rem;border-radius:8px;border:1px solid #e2e8f0;transition:.3s;display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;position:relative;align-items:center}
        .appliance:hover{box-shadow:0 2px 8px rgba(0,0,0,.1);transform:translateY(-1px)}
        .appliance .form-group{margin:0}
        .appliance .form-group.full-width{grid-column:1/-1}
        .appliance .form-group label{font-size:.8rem;color:#4b5563;margin-bottom:.25rem}
        .appliance input[type="number"],.appliance select{background:#fff;padding:.4rem;font-size:.85rem;height:32px;border:1px solid #d1d5db;border-radius:4px}

        /* Buttons */
        .btn{padding:.75rem 1.5rem;border-radius:8px;font-weight:600;transition:.3s;cursor:pointer;border:none;display:inline-flex;align-items:center;justify-content:center;gap:.5rem}
        .btn-action{background:linear-gradient(to right,#3b82f6,#2563eb);color:#fff}
        .btn-action:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(37,99,235,.2)}
        .btn-add-appliance{background:linear-gradient(to right,#15803d,#16a34a);color:#fff;padding:.5rem 1.5rem;border-radius:25px;font-size:.9rem;display:inline-flex;align-items:center;gap:.5rem;transition:.3s;border:none;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.2);margin:1rem 0}
        .btn-add-appliance:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(21,128,61,.3)}
        .btn-add-appliance i{font-size:1rem}
        .btn-remove{position:absolute;top:.25rem;right:.25rem;background:#fee2e2;color:#ef4444;border:none;border-radius:3px;padding:.2rem .4rem;font-size:.75rem;cursor:pointer;transition:.2s}
        .btn-remove:hover{background:#fecaca}

        /* Tooltips */
        .tooltip{position:absolute;background:#1f2937;color:#fff;padding:.5rem .75rem;border-radius:4px;font-size:.8rem;bottom:100%;left:50%;transform:translateX(-50%);white-space:nowrap;opacity:0;visibility:hidden;transition:.3s;z-index:10}
        .tooltip::after{content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);border:5px solid transparent;border-top-color:#1f2937}
        .form-group:hover .tooltip{opacity:1;visibility:visible;transform:translateX(-50%) translateY(-5px)}

        /* Dark Mode */
        .dark{background-color:#1f2937}
        .dark #sizing-form{background-color:#374151;color:#e5e7eb}
        .dark .form-group label{color:#e5e7eb}
        .dark .form-group input,.dark .form-group select{background-color:#4b5563;color:#e5e7eb;border-color:#6b7280}
        .dark .appliance{background:#4b5563;border-color:#6b7280}
        .dark .tooltip{background:#1f2937;color:#e5e7eb}
        .dark .btn-add-appliance{background:linear-gradient(to right,#16a34a,#15803d);box-shadow:0 2px 8px rgba(0,0,0,.4)}
        .dark .btn-remove{background:#7f1d1d;color:#fecaca}
        .dark .btn-remove:hover{background:#991b1b}

        /* Animations */
        @keyframes highlight{0%{background-color:rgba(21,128,61,.2)}100%{background-color:transparent}}
        .value-changed{animation:highlight 1s ease}

        /* Messages & Results */
        .error-message{color:#ef4444;font-size:.875rem;margin-top:.25rem}
        .results-table{background:#fff;border-radius:12px}

        /* Responsive Design */
        @media (max-width:768px){
            .appliance{flex-direction:column;gap:1rem}
            .appliance .form-group{width:100%}
            .btn{width:100%;margin-bottom:.5rem}
        }
        @media (max-width:640px){
            .btn-add-appliance{width:100%;justify-content:center}
            .appliance{grid-template-columns:1fr;padding:.75rem;gap:.5rem}
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        /* Styles de base */
        body {
            font-family: 'Arial', sans-serif;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" opacity="0.1"><rect width="100" height="100" fill="none" stroke="#15803d" stroke-width="1"/><path d="M10,90 L90,10" stroke="#15803d" stroke-width="1"/><circle cx="50" cy="50" r="5" fill="#d4af37"/></svg>') repeat;
            background-color: #f4f4f9;
            transition: background-color 0.3s;
        }

        /* Styles du formulaire */
        #sizing-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        /* Styles des sections */
        .form-section {
            grid-column: 1 / -1;
            border-bottom: 3px solid #15803d;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-section h3 {
            color: #15803d;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* Styles des groupes de formulaire */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #15803d;
            box-shadow: 0 0 0 3px rgba(21,128,61,0.2);
        }

        /* Styles spécifiques pour la section des appareils */
        .appliances-container {
            position: relative;
            max-width: 600px;
            margin: 0 auto 4rem auto;
        }

        #appliances {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .appliance {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            position: relative;
            align-items: center;
        }

        .appliance:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }

        .appliance .form-group {
            margin-bottom: 0;
        }

        .appliance .form-group.full-width {
            grid-column: 1 / -1;
        }

        .appliance .form-group label {
            font-size: 0.8rem;
            color: #4b5563;
            margin-bottom: 0.25rem;
            display: block;
        }

        .appliance input[type="number"],
        .appliance select {
            background-color: white;
            padding: 0.4rem;
            font-size: 0.85rem;
            height: 32px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }

        /* Style du bouton d'ajout */
        .btn-add-appliance {
            background: linear-gradient(to right, #15803d, #16a34a);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            margin: 1rem 0;
        }

        .btn-add-appliance:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(21,128,61,0.3);
        }

        .btn-add-appliance i {
            font-size: 1rem;
        }

        /* Ajustements pour le mode sombre */
        .dark .btn-add-appliance {
            background: linear-gradient(to right, #16a34a, #15803d);
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        /* Ajustements responsifs */
        @media (max-width: 640px) {
            .btn-add-appliance {
                width: 100%;
                justify-content: center;
            }
        }

        /* Style du bouton de suppression */
        .btn-remove {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: #fee2e2;
            color: #ef4444;
            border: none;
            border-radius: 3px;
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-remove:hover {
            background: #fecaca;
        }

        /* Ajustements pour le mode sombre */
        .dark .appliance {
            background: #374151;
            border-color: #4b5563;
        }

        .dark .appliance input[type="number"],
        .dark .appliance select {
            background-color: #4b5563;
            color: #e5e7eb;
            border-color: #6b7280;
        }

        .dark .btn-remove {
            background: #7f1d1d;
            color: #fecaca;
        }

        .dark .btn-remove:hover {
            background: #991b1b;
        }

        /* Ajustements responsifs */
        @media (max-width: 640px) {
            .appliance {
                grid-template-columns: 1fr;
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .btn-add-appliance {
                width: 90%;
                justify-content: center;
                bottom: 1rem;
            }
        }

        /* Styles des boutons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-action {
            background: linear-gradient(to right, #3b82f6, #2563eb);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.2);
        }

        /* Styles des tooltips */
        .tooltip {
            position: absolute;
            background: #1f2937;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: #1f2937 transparent transparent transparent;
        }

        .form-group:hover .tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-5px);
        }

        /* Styles pour le mode sombre */
        .dark {
            background-color: #1f2937;
        }

        .dark #sizing-form {
            background-color: #374151;
            color: #e5e7eb;
        }

        .dark .form-group label {
            color: #e5e7eb;
        }

        .dark .form-group input,
        .dark .form-group select {
            background-color: #4b5563;
            color: #e5e7eb;
            border-color: #6b7280;
        }

        .dark .appliance {
            background: #4b5563;
            border-color: #6b7280;
        }

        .dark .tooltip {
            background: #1f2937;
            color: #e5e7eb;
        }

        /* Styles responsifs */
        @media (max-width: 768px) {
            .appliance {
                flex-direction: column;
                gap: 1rem;
            }

            .appliance .form-group {
                width: 100%;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        /* Animation pour les changements de valeur */
        @keyframes highlight {
            0% { background-color: rgba(21,128,61,0.2); }
            100% { background-color: transparent; }
        }

        .value-changed {
            animation: highlight 1s ease;
        }

        /* Styles pour les messages d'erreur */
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Styles pour les résultats */
        .results-table {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .results-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th,
        .results-table td {
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            text-align: left;
        }

        .results-table th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        /* Styles pour le mode sombre des résultats */
        .dark .results-table {
            background-color: #374151;
            color: #e5e7eb;
        }

        .dark .results-table th {
            background-color: #4b5563;
        }

        .dark .results-table td {
            border-color: #6b7280;
        }
        header {
            background: #15803d;
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        footer {
            background: linear-gradient(45deg, #15803d, #d4af37);
            color: white;
            padding: 2rem 1rem;
            transition: background 0.3s;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: auto;
        }
        .logo img {
            border-radius: 50%;
            width: 60px;
            height: 60px;
        }
        .nav-menu {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        .dropdown {
            position: relative;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            color: black;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1;
            animation: slideDown 0.3s ease;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .dropdown-content a {
            padding: 12px 16px;
            display: block;
            text-decoration: none;
            color: black;
            transition: background-color 0.2s;
        }
        .dropdown-content a:hover {
            background-color: #e5e7eb;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hamburger {
            display: none;
        }
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
                flex-direction: column;
                width: 100%;
                background: #15803d;
                padding: 1rem;
            }
            .nav-menu.active {
                display: flex;
            }
            .hamburger {
                display: block;
                cursor: pointer;
                font-size: 1.5rem;
            }
        }
        #sizing-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .form-group input:valid,
        .form-group select:valid {
            border-color: #10b981;
        }
        .form-group input:invalid:not(:placeholder-shown),
        .form-group select:invalid {
            border-color: #ef4444;
        }
        .tooltip {
            font-size: 0.8rem;
            color: #6b7280;
            display: none;
            position: absolute;
            background: #f3f4f6;
            padding: 0.5rem;
            border-radius: 4px;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-group:hover .tooltip {
            display: block;
        }
        .slider-container {
            margin: 1.5rem 0;
            text-align: center;
        }
        .panel-svg {
            width: 200px;
            height: 100px;
            margin-top: 1rem;
            display: inline-block;
        }
        .sun-arc {
            fill: none;
            stroke: #f59e0b;
            stroke-width: 2;
        }
        .solar-panel {
            fill: #4b5563;
            transition: transform 0.3s;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .btn-action {
            background: linear-gradient(to right, #3b82f6, #2563eb);
            color: white;
        }
        .btn-primary {
            background: linear-gradient(to right, #15803d, #10b981);
            color: white;
        }
        .btn-secondary {
            background: linear-gradient(to right, #ef4444, #dc2626);
            color: white;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }
        .toast.error {
            background: #ef4444;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #15803d, #10b981);
            border-radius: 6px;
            transition: width 0.5s ease;
        }
        .cost-breakdown {
            margin-top: 1.5rem;
            
        }
        .system-type-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .loading-spinner {
            display: none;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #15803d;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            margin-left: 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .chart-container {
            flex: 1;
            min-width: 300px;
            max-width: 600px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 1rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .chart-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .social-icons .social-icon {
            font-size: 1.5rem;
            transition: transform 0.3s, color 0.3s;
        }
        .social-icons .social-icon:hover {
            transform: scale(1.2);
            color: #d4af37;
        }
        .dark {
            background-color: #1f2937;
        }
        .dark #sizing-form,
        .dark .chart-container {
            background-color: #374151;
            color: white;
        }
        .dark .bg-gray-100 {
            background-color: #4b5563;
        }
        .dark .form-group label {
            color: #e5e7eb;
        }
        .card-img:hover {
    transform: scale(1.1);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.bg-white:hover {
    backdrop-filter: blur(5px);
}

@media (min-width: 1024px) {
    .max-w-5xl {
        max-width: 90%;
    }
}
.dark .bg-gray-100 {
    background-color: #4b5563;
}
.dark .form-group input,
.dark .form-group select {
    background-color: #4b5563;
    color: #e5e7eb;
    border-color: #6b7280;
}
.dark .tooltip {
    background: #4b5563;
    color: #e5e7eb;
}
.dark table, .dark th, .dark td {
    border-color: #6b7280;
}
/* Positionnement et style du bloc irradiation */
.form-group.irradiation-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.3rem;
    margin-bottom: 1.5rem;
}

.irradiation-group label {
    margin-bottom: 0.2rem;
}

.irradiation-group input[type="text"] {
    margin-bottom: 0.2rem;
}

.irradiation-group .tooltip {
    margin-bottom: 0.2rem;
}

.irradiation-group .text-yellow-700 {
    margin-bottom: 0.2rem;
}

.irradiation-group a {
    align-items: center;
    display: inline-flex;
    font-weight: 500;
    font-size: 0.98rem;
    color: #2563eb;
    text-decoration: underline;
    margin-top: 0.2rem;
    transition: color 0.2s;
}
.irradiation-group a:hover {
    color: #15803d;
    text-decoration: underline;
}

@media (max-width: 900px) {
    .irradiation-row {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }
    .irradiation-side {
        min-width: 0;
        align-items: flex-start;
    }
}
        /* Style spécifique pour l'input de consommation totale */
        #total-consumption {
            width: 180px;
            padding: 0.5rem;
            font-size: 1rem;
            height: 38px;
            background-color: #f0fdf4;
            border: 2px solid #15803d;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            color: #15803d;
            box-shadow: 0 2px 4px rgba(21, 128, 61, 0.1);
            transition: all 0.3s ease;
            position: relative;
            margin: 0.5rem 0;
        }

        #total-consumption:focus {
            outline: none;
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.2);
        }

        #total-consumption::before {
            content: '⚡';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
            color: #15803d;
        }

        #total-consumption::after {
            content: 'kWh/jour';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.75rem;
            color: #15803d;
            opacity: 0.7;
        }

        .dark #total-consumption {
            background-color: #064e3b;
            border-color: #059669;
            color: #4ade80;
            box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
        }

        .dark #total-consumption::before,
        .dark #total-consumption::after {
            color: #4ade80;
        }

        .dark #total-consumption:focus {
            border-color: #4ade80;
            box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.2);
        }

        /* Style du label de consommation */
        .consumption-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #15803d;
            margin-bottom: 0.5rem;
        }

        .consumption-label i {
            font-size: 1.1rem;
        }

        .dark .consumption-label {
            color: #4ade80;
}

/* --- Compression des sections et groupes --- */
.form-section {
    padding-bottom: 0.4rem;
    margin-bottom: 0.7rem;
    border-bottom-width: 2px;
}
.form-section h3 {
    font-size: 1rem ;
    margin-bottom: 0.3rem;
    padding: 0 ;
}
.form-group {
    margin-bottom: 0.5rem;
    padding: 0.1rem 0;
}
.form-group label {
    font-size: 0.9rem;
    margin-bottom: 0.15rem;
}
.form-group input,
.form-group select {
    padding: 0.35rem 0.6rem;
    font-size: 0.9rem ;
    border-radius: 5px;
}
.btn, .btn-action, .btn-primary, .btn-secondary {
    padding: 0.35rem 0.9rem;
    font-size: 0.92rem;
    border-radius: 6px ;
}
.appliance {
    padding: 0.3rem;
    gap: 0.3rem;
}
.tooltip {
    font-size: 0.72rem;
    padding: 0.2rem 0.5rem ;
}
.results-table th,
.results-table td {
    padding: 0.3rem 0.4rem;
    font-size: 0.93rem ;
}
@media (max-width: 640px) {
    .form-section {
        padding-bottom: 0.2rem ;
        margin-bottom: 0.3rem ;
    }
    .form-group {
        margin-bottom: 0.2rem ;
    }
    .btn, .btn-action, .btn-primary, .btn-secondary {
        padding: 0.25rem 0.6rem t;
        font-size: 0.9rem;
    }
}
    </style>
</head>
<body>
    <header>
        <div class="header-content max-w-7xl mx-auto flex justify-between items-center">
            <div class="logo">
                <img src="../image/Amen1.png" alt="SolarCalc Logo" width="50" height="50" onerror="this.src='https://via.placeholder.com/50'">
                <span class="text-2xl font-bold">SolarCalc</span>
            </div>
            <div class="hamburger" aria-label="Toggle menu"><i class="fas fa-bars"></i></div>
            <nav class="nav-menu">
              
                <div class="dropdown">
                <a href="#" class="text-white hover:underline" ><i class="fas fa-home"></i></a> 
                    <a href="#" class="text-white hover:underline">Solutions Solaires</a>
                    <div class="dropdown-content">
                        <a href="index1.php">Dimensionnement</a>
                        <a href="installation.html">Installation</a>
                        <a href="maintenance.html">Maintenance</a>
                    </div>
                </div>
                <div class="dropdown">
                    <a href="#" class="text-white hover:underline"><i class="fas fa-info-circle"></i> À Propos</a>
                    <div class="dropdown-content">
                        <a href="mission.html">Mission</a>                   
                        <a href="contact.html">Contact</a>
                    </div>
                </div>
                <a href="https://weather.com/fr-TG/temps-aujour/l/Lomé+Togo?canonicalCityId=dfdaba8cbe3b4a1c3b4a1c3b4a1c3b4a1c3b4a1c3b4a1c3b4a1c3b4a1c3b4a1c" target="_blank" class="text-white hover:underline">
                    <i class="fas fa-cloud-sun"></i> Météo
                </a>
                <a href="#" class="theme-toggle text-white hover:underline" aria-label="Basculer le thème"><i class="fas fa-moon"></i> Mode Nuit</a>
            </nav>
        </div>
    </header>

    <div class="container max-w-4xl mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4"><i class="fas fa-solar-panel mr-2"></i> Dimensionnement Solaire Photovoltaïque</h2>
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill" style="width: 0%;"></div>
        </div>
        <form id="sizing-form">
       
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-section">
                <h3 class="text-xl font-semibold">Informations Générales</h3>
                <div class="form-group">
                    <label class="block text-sm font-medium"><i class="fas fa-user mr-1"></i>Nom</label>
                    <input type="text" name="name" required placeholder="Ex. Amenson Toulass" class="w-full">
                    <span class="tooltip">Nom de l'utilisateur</span>
                </div>

                <h3 class="text-xl font-semibold">Actions</h3>
        
                <div class="flex flex-wrap gap-4">
                    <button type="button" onclick="saveForm()" class="btn btn-action"><i class="fas fa-save mr-2"></i>Sauvegarder</button>
                    <button type="button" onclick="loadForm()" class="btn btn-action"><i class="fas fa-upload mr-2"></i>Charger</button>
                    <button type="button" onclick="quickSetup()" class="btn btn-action"><i class="fas fa-bolt mr-2"></i>Configuration Rapide</button>
                </div>
            </div>
            <div class="form-section">
                <div class="section-header">
                <h3 class="text-xl font-semibold">Estimation de la Charge</h3>
            </div>
                <div class="appliances-container">
            <div id="appliances">
                        <div class="appliance">
                            <div class="form-group">
                        <label class="block text-sm font-medium">Appareil</label>
                                <select name="appliance[0][name]" required class="w-full" onchange="updateAppliancePower(this)">
                                    <option value="refrigerateur">Réfrigérateur</option>
                                    <option value="ventilateur">Ventilateur</option>
                                    <option value="lampe">Lampe LED</option>
                                    <option value="television">Télévision</option>
                                    <option value="ordinateur">Ordinateur</option>
                                    <option value="portable">Portable</option>
                                    <option value="autre">Autre</option>
                        </select>
                        <span class="tooltip">Sélectionnez un appareil électrique à alimenter</span>
                    </div>
                            <div class="form-group">
                                <label class="block text-sm font-medium">Puissance (W)</label>
                                <input type="number" name="appliance[0][power]" min="1" step="1" required placeholder="Ex. 150" class="w-full" onchange="calculateConsumption()">
                                <span class="tooltip">Puissance électrique de l'appareil en watts</span>
                            </div>
                            <div class="form-group">
                                <label class="block text-sm font-medium">Quantité</label>
                                <input type="number" name="appliance[0][quantity]" min="1" step="1" value="1" required placeholder="Ex. 1" class="w-full" onchange="calculateConsumption()">
                                <span class="tooltip">Nombre d'appareils identiques</span>
                            </div>
                            <div class="form-group">
                        <label class="block text-sm font-medium">Heures/jour</label>
                                <input type="number" name="appliance[0][hours]" min="0" step="0.1" required placeholder="Ex. 5" class="w-full" onchange="calculateConsumption()">
                        <span class="tooltip">Durée d'utilisation quotidienne en heures</span>
                    </div>
                            <button type="button" onclick="removeAppliance(this)" class="btn-remove">
                                <i class="fas fa-trash"></i>
                            </button>
                </div>
            </div>
                    <button type="button" onclick="addAppliance()" class="btn-add-appliance">
                        <i class="fas fa-plus"></i> Ajouter un appareil
                    </button>
                </div>
            <div class="form-group">
                    <label class="consumption-label">
                        <i class="fas fa-bolt"></i>
                        Consommation Totale
                    </label>
                    <input type="number" id="total-consumption" name="total_consumption" step="0.01" required readonly>
                    <span class="tooltip">Consommation totale calculée en kilowattheures par jour</span>
                </div>
            </div>
            <div class="form-section">
                <h3 class="text-xl font-semibold">Localisation</h3>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium"><i class="fas fa-globe mr-1"></i>Longitude (°)</label>
                <input type="number" id="longitude" name="longitude" step="0.01" min="-180" max="180" required placeholder="Ex. 1.2" class="w-full">
                <span class="tooltip">Coordonnée est-ouest, 1.2°E pour Lomé</span>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium"><i class="fas fa-globe mr-1"></i>Latitude (°)</label>
                <input type="number" id="latitude" name="latitude" step="0.01" min="-90" max="90" required placeholder="Ex. 6.1" class="w-full">
                <span class="tooltip">Coordonnée nord-sud, 6.1°N pour Lomé</span>
            </div>

            <div class="form-group">
                <label for="irradiation">Irradiation Solaire (kWh/m²/jour)</label>
                <input type="text" id="irradiation" name="irradiation" readonly 
                       value="<?php echo number_format($irradiation ?? 5.5, 1); ?>" 
                       class="form-control">
                <small class="form-text text-muted">
                   <i class="fas fa-info-circle "></i> Calculé automatiquement en fonction de votre position <br>
                    <i class="fas fa-info-circle "></i>   calculer avec la configuration automatique du togo-lomé
                     </small>
                </div>
            <div id="user-position-container" class="mt-4 p-4 bg-white rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-2"></i>Votre Position
                        </h3>
                        <div id="user-position-loading" class="text-green-600">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Chargement de votre position...
            </div>
                        <div id="user-position" class="text-gray-600" style="display:none;"></div>
                        <div id="user-position-error" class="text-red-600" style="display:none;"></div>
                    </div>
                    <button onclick="getUserLocation()" class="btn btn-action">
                        <i class="fas fa-sync-alt mr-2"></i>Actualiser
                    </button>
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="text-xl font-semibold">Configuration du Système</h3>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium"><i class="fas fa-plug mr-1"></i>Type de Système</label>
                <select id="system-type" name="system-type" required class="w-full">
                    <option value="connecte" class="system-type-option"><i class="fas fa-plug"></i> Connecté au réseau</option>
                    <option value="autonome" class="system-type-option"><i class="fas fa-battery-full"></i> Autonome</option>
                    <option value="hybride" class="system-type-option"><i class="fas fa-sync"></i> Hybride</option>
                </select>
                <span class="tooltip">Connecté: relié au réseau; Autonome: indépendant; Hybride: combine les deux</span>
            </div>
            <div class="form-group" id="system-voltage-section" style="display: none;">
                <label class="block text-sm font-medium"><i class="fas fa-bolt mr-1"></i>Tension du Système (V)</label>
                <select name="system-voltage" class="w-full">
                    <option value="12">12V</option>
                    <option value="24">24V</option>
                    <option value="48">48V</option>
                </select>
                <span class="tooltip">Tension du système pour les batteries (Autonome/Hybride)</span>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium"><i class="fas fa-solar-panel mr-1"></i>Type de Panneau</label>
                <select id="panel-type" name="panel-type" required class="w-full" onchange="updatePanelDimensions()">
                    <option value="mono">Monocristallin (400 W)</option>
                    <option value="poly">Polycristallin (300 W)</option>
                    <option value="Amorp">Amorphe (200 W)</option>
                </select>
                <span class="tooltip">Monocristallin: plus efficace; Polycristallin: moins cher</span>
            </div>

            <div class="form-group">
                <label class="block text-sm font-medium"><i class="fas fa-ruler mr-1"></i>Dimensions du Panneau</label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600">Longueur (cm)</label>
                        <input type="number" id="panel-length" name="panel-length" min="100" max="300" step="0.1" value="176" required class="w-full">
                        <span class="tooltip">Longueur du panneau en centimètres</span>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Largeur (cm)</label>
                        <input type="number" id="panel-width" name="panel-width" min="50" max="200" step="0.1" value="104" required class="w-full">
                        <span class="tooltip">Largeur du panneau en centimètres</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="block text-sm font-medium"><i class="fas fa-tachometer-alt mr-1"></i>Efficacité Panneau (%)</label>
                <input type="number" id="panel-efficiency" name="panel-efficiency" min="12" max="19" step="0.1" value="15" required placeholder="Ex. 15" class="w-full">
                <span class="tooltip">Efficacité de conversion solaire (12-19%)</span>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium"><i class="fas fa-compass mr-1"></i>Orientation (Azimut, °)</label>
                <input type="number" id="azimuth" name="azimuth" min="-180" max="180" step="1" value="0" required placeholder="Ex. 0" class="w-full">
                <span class="tooltip">0° = Sud, 90° = Ouest, -90° = Est</span>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium"><i class="fas fa-percentage mr-1"></i>Pertes Système (%)</label>
                <input type="number" id="system-losses" name="system-losses" min="0" max="50" step="1" value="15" required placeholder="Ex. 15" class="w-full">
                <span class="tooltip">Pertes dues à l'onduleur, câbles, etc. (0-50%)</span>
            </div>
            <div class="slider-container">
                <label class="block text-sm font-medium"><i class="fas fa-angle-up mr-1"></i>Inclinaison (°)</label>
                <input type="range" id="tilt" name="tilt" min="0" max="90" value="10" class="w-full">
                <span id="tilt-value" class="text-sm">10°</span>
                <svg class="panel-svg" viewBox="0 0 200 100">
                    <path class="sun-arc" d="M50,90 A40,40 0 0,1 150,90" />
                    <rect class="solar-panel" x="80" y="40" width="40" height="20" transform="rotate(10, 100, 50)" />
                    <circle cx="100" cy="90" r="5" fill="#f59e0b" />
                </svg>
                <span class="tooltip">Angle d'inclinaison des panneaux pour maximiser l'ensoleillement</span>
                <div id="tilt-message" class="mt-2 text-sm text-gray-600"></div>
            </div>
            <div id="battery-section" style="display: none; transition: all 0.3s;">
                <div class="form-section">
                    <h3 class="text-xl font-semibold">Batteries (Autonome/Hybride)</h3>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium">Jours d'Autonomie</label>
                    <input type="number" name="autonomy-days" min="1" step="1" value="1" placeholder="Ex. 1" class="w-full">
                    <span class="tooltip">Nombre de jours sans soleil à supporter</span>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium">Type de Batterie</label>
                    <select name="battery-type" class="w-full">
                        <option value="lithium">Lithium-ion (80% DOD)</option>
                        <option value="plomb">Plomb-acide (50% DOD)</option>
                    </select>
                    <span class="tooltip">Lithium: plus durable; Plomb: moins cher</span>
                </div>
            </div>
            <div class="form-section">
                <h3 class="text-xl font-semibold">Coûts</h3>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium">Coût Panneaux (CFA/panneau)</label>
                <input type="number" name="panel-cost" min="0" step="0.01" value="50000" required placeholder="Ex. 50000 CFA" class="w-full">
                <span class="tooltip">Coût unitaire d'un panneau solaire</span>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium">Coût Batterie (CFA/batterie)</label>
                <input type="number" name="battery-cost" min="0" step="0.01" value="28000" placeholder="Ex. 28000 CFA" class="w-full">
                <span class="tooltip">Coût unitaire d'une batterie</span>
            </div>
            <div class="form-group">
                <label class="block text-sm font-medium">Coût Onduleur (CFA)</label>
                <input type="number" name="inverter-cost" min="0" step="0.01" value="120000" placeholder="Ex. 120000 CFA" class="w-full">
                <span class="tooltip">Coût de l'onduleur (requis pour Hybride)</span>
            </div>
            <div class="flex flex-wrap gap-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-calculator mr-2"></i>Calculer</button>
                <span id="loading-spinner" class="loading-spinner"></span>
                <button type="button" onclick="generatePDF()" class="btn btn-secondary"><i class="fas fa-file-pdf mr-2"></i>Exporter PDF</button>
                 
            </div>
        </form>
        <div class="results mt-6" id="results">
            <div class="results-table bg-white p-6 rounded-lg shadow-md" id="results-table" style="display: none;">
                <h3 class="text-xl font-semibold mb-2">Résultats</h3>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">Paramètre</th>
                            <th class="border p-2">Valeur</th>
                        </tr>
                    </thead>
                    <tbody id="results-body"></tbody>
                </table>
                <div class="cost-breakdown">
                    <h4 class="text-lg font-semibold mt-4">Répartition des Coûts</h4>
        
                    <table class="w-full border-collapse mt-2">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2">Composant</th>
                                <th class="border p-2">Coût (€)</th>
                            </tr>
                        </thead>
                        <tbody id="cost-breakdown-body"></tbody>
                    </table>
                </div>
                <button type="button" onclick="confirmReset()" class="mt-4 btn btn-action"><i class="fas fa-undo mr-2"></i>Retour au point initial</button>
            </div>
            <div class="charts flex flex-wrap gap-4 mt-6">
                <div class="chart-container">
                    <canvas id="irradiation-chart" style="max-width: 600px;"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="cost-chart" style="max-width: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <main class="max-w-5xl mx-auto p-6">

    <section class="card p-8 rounded-xl shadow-md">
            <h2 class="text-3xl font-semibold text-green-700 mb-6 border-b-2 border-green-200 pb-2">Pourquoi Choisir l'Énergie Solaire</h2>
            <ul class="list-disc list-inside space-y-4 text-lg text-gray-700">
                <li><strong>Durabilité :</strong> Réduisez votre empreinte carbone avec une énergie propre.</li>
                <li><strong>Économie :</strong> Diminuez vos factures d'électricité à long terme.</li>
                <li><strong>Autonomie :</strong> Produisez votre propre énergie, même dans des zones rurales.</li>
                <li><strong>Fiabilité :</strong> Les panneaux solaires modernes ont une durée de vie de 25+ ans.</li>
            </ul>
        </section>
        </main>
    <footer>
        <div class="max-w-7xl mx-auto flex flex-col items-center">
            <div class="social-icons flex space-x-4 mb-4">
                <a href="#" aria-label="Facebook" class="social-icon"><i class="fab fa-facebook"></i></a>
                <a href="#" aria-label="Instagram" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="WhatsApp" class="social-icon"><i class="fab fa-whatsapp"></i></a>
            </div>
            <div class="copyright">
                © 2025 SolarCalc. Tous droits réservés.
            </div>
        </div>
    </footer>
    <div id="toast" class="toast"></div>

   <script>
        let applianceCount = 1;
let results = null;
let irradiationChart;
let costChart;

const escapeHTML = (str) => {
    return str ? str.replace(/[&<>"']/g, (match) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[match])) : '';
};

const initializeCharts = () => {
    const irradiationCtx = document.getElementById('irradiation-chart')?.getContext('2d');
    const costCtx = document.getElementById('cost-chart')?.getContext('2d');
    if (!irradiationCtx || !costCtx) {
        console.warn('One or more chart canvases are missing.');
        return;
    }

    irradiationChart = new Chart(irradiationCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
            datasets: [{
                label: 'Irradiation Solaire (kWh/m²/jour)',
                            data: [5.8, 5.9, 5.7, 5.6, 5.5, 5.4, 5.3, 5.2, 5.3, 5.4, 5.5, 5.6],
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.2)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#f59e0b',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#f59e0b'
            }]
        },
        options: {
            responsive: true,
            animation: { duration: 1000, easing: 'easeOutQuart' },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'kWh/m²/jour' } },
                x: { title: { display: true, text: 'Mois' } }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: { backgroundColor: '#15803d', titleColor: '#fff', bodyColor: '#fff' }
            }
        }
    });

    costChart = new Chart(costCtx, {
        type: 'pie',
        data: {
            labels: ['Panneaux', 'Batteries', 'Onduleur'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#15803d', '#d4af37', '#ef4444'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            animation: { duration: 1000, easing: 'easeOutQuart' },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { backgroundColor: '#15803d', titleColor: '#fff', bodyColor: '#fff' }
            }
        }
    });
};

const showToast = (message, isError = false) => {
    const toast = document.getElementById('toast');
    if (!toast) {
        console.error('Toast element not found.');
        return;
    }
    toast.textContent = escapeHTML(message);
    toast.className = `toast ${isError ? 'error' : ''}`;
    toast.style.display = 'block';
    setTimeout(() => toast.style.display = 'none', 3000);
};

const calculateConsumption = () => {
    let totalConsumption = 0;
    const appliances = document.querySelectorAll('.appliance');
    
    appliances.forEach(appliance => {
        const power = parseFloat(appliance.querySelector('input[name$="[power]"]').value) || 0;
        const quantity = parseInt(appliance.querySelector('input[name$="[quantity]"]').value) || 0;
        const hours = parseFloat(appliance.querySelector('input[name$="[hours]"]').value) || 0;
        
        const consumption = (power * quantity * hours) / 1000; // Conversion en kWh
        totalConsumption += consumption;
    });
    
    document.getElementById('total-consumption').value = totalConsumption.toFixed(2);
    updateResults();
};

const attachApplianceListeners = () => {
    const appliancesDiv = document.getElementById('appliances');
    if (!appliancesDiv) return;

    appliancesDiv.addEventListener('change', (e) => {
        const target = e.target;
        if (target.matches('select, input[type="number"]')) {
            calculateConsumption();
            updateProgress();
        }
    });
};

const addAppliance = () => {
    const appliancesDiv = document.getElementById('appliances');
    const newAppliance = document.createElement('div');
    newAppliance.className = 'appliance';
    newAppliance.innerHTML = `
        <div class="form-group">
            <label class="block text-sm font-medium">Appareil</label>
            <select name="appliance[${applianceCount}][name]" required class="w-full" onchange="updateAppliancePower(this)">
                <option value="refrigerateur">Réfrigérateur</option>
                <option value="ventilateur">Ventilateur</option>
                <option value="lampe">Lampe LED</option>
                <option value="television">Télévision</option>
                <option value="ordinateur">Ordinateur</option>
                <option value="portable">Portable</option>
                <option value="autre">Autre</option>
            </select>
            <span class="tooltip">Sélectionnez un appareil électrique à alimenter</span>
        </div>
        <div class="form-group">
            <label class="block text-sm font-medium">Puissance (W)</label>
            <input type="number" name="appliance[${applianceCount}][power]" min="1" step="1" required placeholder="Ex. 150" class="w-full" onchange="calculateConsumption()">
            <span class="tooltip">Puissance électrique de l'appareil en watts</span>
        </div>
        <div class="form-group">
            <label class="block text-sm font-medium">Quantité</label>
            <input type="number" name="appliance[${applianceCount}][quantity]" min="1" step="1" value="1" required placeholder="Ex. 1" class="w-full" onchange="calculateConsumption()">
            <span class="tooltip">Nombre d'appareils identiques</span>
        </div>
        <div class="form-group">
            <label class="block text-sm font-medium">Heures/jour</label>
            <input type="number" name="appliance[${applianceCount}][hours]" min="0" step="0.1" required placeholder="Ex. 5" class="w-full" onchange="calculateConsumption()">
            <span class="tooltip">Durée d'utilisation quotidienne en heures</span>
        </div>
        <button type="button" onclick="removeAppliance(this)" class="btn-remove">
            <i class="fas fa-trash"></i>
        </button>
    `;
    appliancesDiv.appendChild(newAppliance);
    applianceCount++;
    calculateConsumption();
};

const removeAppliance = (button) => {
    const appliance = button.closest('.appliance');
    appliance.remove();
        calculateConsumption();
};

const updateProgress = () => {
    const form = document.getElementById('sizing-form');
    if (!form) {
        showToast('Formulaire non trouvé.', true);
        return;
    }
    const inputs = form.querySelectorAll('input:not([type="range"]), select');
    if (!inputs.length) {
        document.getElementById('progress-fill').style.width = '0%';
        return;
    }
    let filled = 0;
    inputs.forEach(input => {
        if (input.value && input.value !== '0' && input.value !== '') filled++;
    });
    const progress = (filled / inputs.length) * 100;
    document.getElementById('progress-fill').style.width = `${Math.min(progress, 100)}%`;
};

const quickSetup = () => {
    const inputs = {
        longitude: '1.2',
        latitude: '6.1',
        'system-type': 'autonome',
        'panel-type': 'mono',
        'panel-efficiency': '15',
        azimuth: '0',
        'system-losses': '15',
        tilt: '10',
        'autonomy-days': '2',
        'battery-type': 'lithium',
        'panel-cost': '50000',
        'battery-cost': '28000',
        'inverter-cost': '120000',
        'system-voltage': '48'
    };
    for (const [id, value] of Object.entries(inputs)) {
        const input = document.querySelector(`#${id}, [name="${id}"]`);
        if (input) input.value = value;
    }
    document.getElementById('system-type')?.dispatchEvent(new Event('change'));
    document.getElementById('tilt')?.dispatchEvent(new Event('input'));
    calculateConsumption();
    updateProgress();
    showToast('Configuration rapide appliquée pour Lomé, Togo.');
};

const saveForm = () => {
    const form = document.getElementById('sizing-form');
    if (!form) {
        showToast('Formulaire non trouvé.', true);
        return;
    }
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    data.appliances = [];
    const appliances = form.querySelectorAll('.appliance');
    appliances.forEach((appliance, index) => {
        const name = appliance.querySelector(`[name="appliance[${index}][name]"]`)?.value;
        const hours = appliance.querySelector(`[name="appliance[${index}][hours]"]`)?.value;
        if (name && hours) data.appliances.push({ name, hours });
    });
    localStorage.setItem('solarCalcForm', JSON.stringify(data));
    showToast('Formulaire sauvegardé.');
};

const loadForm = () => {
    const savedData = localStorage.getItem('solarCalcForm');
    if (!savedData) {
        showToast('Aucune donnée sauvegardée.', true);
        return;
    }
    const data = JSON.parse(savedData);
    resetToInitialState(false);
    const appliancesDiv = document.getElementById('appliances');
    if (!appliancesDiv) {
        showToast('Section des appareils non trouvée.', true);
        return;
    }
    appliancesDiv.innerHTML = '';
    applianceCount = 0;
    (data.appliances || []).forEach((app, index) => {
        const newAppliance = document.createElement('div');
        newAppliance.className = 'appliance flex space-x-4 mb-2';
        newAppliance.innerHTML = `
            <div class="form-group flex-1">
                <label class="block text-sm font-medium">Appareil</label>
                <select name="appliance[${index}][name]" required class="w-full" onchange="updateAppliancePower(this)">
                    <option value="refrigerateur" ${app.name === 'refrigerateur' ? 'selected' : ''}>Réfrigérateur</option>
                    <option value="ventilateur" ${app.name === 'ventilateur' ? 'selected' : ''}>Ventilateur</option>
                    <option value="lampe" ${app.name === 'lampe' ? 'selected' : ''}>Lampe LED</option>
                    <option value="television" ${app.name === 'television' ? 'selected' : ''}>Télévision</option>
                    <option value="ordinateur" ${app.name === 'ordinateur' ? 'selected' : ''}>Ordinateur</option>
                </select>
                <span class="tooltip">Sélectionnez un appareil électrique à alimenter</span>
            </div>
            <div class="form-group flex-1">
                <label class="block text-sm font-medium">Puissance (W)</label>
                <input type="number" name="appliance[${index}][power]" min="1" step="1" value="${escapeHTML(app.power || '0')}" required placeholder="Ex. 150" class="w-full" onchange="calculateConsumption()">
                <span class="tooltip">Puissance électrique de l'appareil en watts</span>
            </div>
            <div class="form-group flex-1">
                <label class="block text-sm font-medium">Quantité</label>
                <input type="number" name="appliance[${index}][quantity]" min="1" step="1" value="${escapeHTML(app.quantity || '1')}" required placeholder="Ex. 1" class="w-full" onchange="calculateConsumption()">
                <span class="tooltip">Nombre d'appareils identiques</span>
            </div>
            <div class="form-group flex-1">
                <label class="block text-sm font-medium">Heures/jour</label>
                <input type="number" name="appliance[${index}][hours]" min="0" step="0.1" value="${escapeHTML(app.hours || '0')}" required placeholder="Ex. 5" class="w-full" onchange="calculateConsumption()">
                <span class="tooltip">Durée d'utilisation quotidienne en heures</span>
            </div>
            <button type="button" onclick="removeAppliance(this)" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
        `;
        appliancesDiv.appendChild(newAppliance);
        applianceCount++;
    });
    for (const [key, value] of Object.entries(data)) {
        if (key !== 'appliances') {
            const input = document.querySelector(`[name="${key}"]`);
            if (input) input.value = escapeHTML(value);
        }
    }
    calculateConsumption();
    document.getElementById('system-type')?.dispatchEvent(new Event('change'));
    document.getElementById('tilt')?.dispatchEvent(new Event('input'));
    updateProgress();
    showToast('Formulaire chargé.');
};

const updateResults = (data) => {
    if (!data || typeof data !== 'object') {
        showToast('Données de résultats invalides.', true);
        return;
    }
    const systemType = document.getElementById('system-type')?.value || 'connecte';
    const resultsBody = document.getElementById('results-body');
    const costBreakdownBody = document.getElementById('cost-breakdown-body');
    if (!resultsBody || !costBreakdownBody) {
        showToast('Section des résultats non trouvée.', true);
        return;
    }

    // Ensure all cost-related fields are numbers or default to 0
    const panelCost = typeof data.panel_cost === 'number' ? data.panel_cost : 0;
    const batteryCost = typeof data.battery_cost === 'number' ? data.battery_cost : 0;
    const inverterCost = typeof data.inverter_cost === 'number' ? data.inverter_cost : 0;
    const totalCost = typeof data.total_cost === 'number' ? data.total_cost : 0;

    resultsBody.innerHTML = `
        <tr><td>Consommation Totale</td><td>${escapeHTML((data.consumption?.toFixed(2)) || '0.00')} kWh/jour</td></tr>
        <tr><td>Type de Système</td><td>${escapeHTML(systemType.charAt(0).toUpperCase() + systemType.slice(1))}</td></tr>
        <tr><td>Puissance Crête</td><td>${escapeHTML((data.peak_power?.toFixed(2)) || '0.00')} W</td></tr>
        <tr><td>Énergie à Produire</td><td>${escapeHTML((data.E_p?.toFixed(2)) || '0.00')} kWh/jour</td></tr>
        <tr><td>Puissance PV</td><td>${escapeHTML((data.P_C?.toFixed(2)) || '0.00')} kWp</td></tr>
        <tr><td>Nombre de Panneaux</td><td>${escapeHTML(data.N_P?.toString() || '0')}</td></tr>
        <tr><td>Nombre de Panneaux en série</td><td>${escapeHTML((data.N_PS?.toString() || results.N_PS?.toString() || '0'))}</td></tr>
        <tr><td>Nombre de Panneaux en parallèle</td><td>${escapeHTML((data.N_PP?.toString() || results.N_PP?.toString() || '0'))}</td></tr>
        <tr><td>Surface Totale</td><td>${escapeHTML((data.S_t?.toFixed(2)) || '0.00')} m²</td></tr>
        <tr><td>Inclinaison Optimale</td><td>${escapeHTML(data.tilt?.toString() || '0')}°</td></tr>
        <tr><td>Orientation (Azimut)</td><td>${escapeHTML(data.azimuth?.toString() || '0')}°</td></tr>
        ${systemType !== 'connecte' ? `
            <tr><td>Capacité Batterie</td><td>${escapeHTML((data.battery_capacity?.toFixed(2)) || '0.00')} Ah (${escapeHTML(data.system_voltage?.toString() || 'N/A')}V)</td></tr>
            <tr><td>Nombre de Batteries</td><td>${escapeHTML(data.battery_count?.toString() || '0')}</td></tr>
            <tr><td>Contrôleur Courant</td><td>${escapeHTML((data.controller_current?.toFixed(2)) || '0.00')} A</td></tr>
        ` : ''}
        <tr><td>Coût Total Système</td><td>${escapeHTML((totalCost.toFixed(2)) || '0.00')} CFA</td></tr>
    `;

    costBreakdownBody.innerHTML = `
        <tr><td>Panneaux Solaires</td><td>${escapeHTML((panelCost.toFixed(2)) || '0.00')} CFA</td></tr>
        ${systemType !== 'connecte' ? `<tr><td>Batteries</td><td>${escapeHTML((batteryCost.toFixed(2)) || '0.00')} CFA</td></tr>` : ''}
        ${systemType === 'hybride' ? `<tr><td>Onduleur</td><td>${escapeHTML((inverterCost.toFixed(2)) || '0.00')} CFA</td></tr>` : ''}
        <tr><td>Total</td><td>${escapeHTML((totalCost.toFixed(2)) || '0.00')} CFA</td></tr>
    `;

    if (costChart) {
        costChart.data.datasets[0].data = [
            panelCost,
            systemType !== 'connecte' ? batteryCost : 0,
            systemType === 'hybride' ? inverterCost : 0
        ].filter(val => val > 0);
        costChart.data.labels = [
            'Panneaux',
            systemType !== 'connecte' ? 'Batteries' : null,
            systemType === 'hybride' ? 'Onduleur' : null

        ].filter(label => label);
        costChart.update();
    }

    const resultsTable = document.getElementById('results-table');
    if (resultsTable) resultsTable.style.display = 'block';

    // Juste après results = data;
    const puissancePanneau = 400; // W, adapte selon le type de panneau sélectionné
    const tensionPanneau = 36;    // V, adapte selon le type de panneau sélectionné
    const tensionSysteme = parseFloat(document.querySelector('[name="system-voltage"]')?.value || 48);
    // P_C est en kW, on convertit en W
    const puissanceTotale = (results.P_C || 0) * 1000;
    const nombreTotalPanneaux = Math.ceil(puissanceTotale / puissancePanneau);
    const nombreSerie = Math.ceil(tensionSysteme / tensionPanneau);
    const nombreParallele = Math.ceil(nombreTotalPanneaux / nombreSerie);
    results.N_PS = nombreSerie;
    results.N_PP = nombreParallele;
};
const { jsPDF } = window.jspdf;

async function generatePDF() {
    try {
        // Vérifiez que jsPDF et autoTable sont chargés
        if (!jsPDF || !jsPDF.API.autoTable) {
            showToast('jsPDF ou autoTable non chargé.', true);
            return;
        }

        // Récupération des résultats (à adapter selon votre code)
        const results = window.results || null;

        // Initialize jsPDF
        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });

        // Header
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(20);
        doc.setTextColor(21, 128, 61);
        doc.text('Dimensionnement Solaire Photovoltaïque', 105, 20, { align: 'center' });
        doc.setLineWidth(0.5);
        doc.setDrawColor(21, 128, 61);
        doc.line(20, 25, 190, 25);

        // General Information
        let y = 35;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(12);
        doc.setTextColor(0);
        doc.text('Informations Générales', 20, y);
        y += 10;

        const form = document.getElementById('sizing-form');
        if (!form) throw new Error('Formulaire non trouvé');

        // Utilitaire pour capitaliser la première lettre
        const capitalize = str => str ? str.charAt(0).toUpperCase() + str.slice(1) : 'Non spécifié';

        const formData = {
            'Nom': form.querySelector('[name="name"]')?.value || 'Non spécifié',
            'Latitude': `${form.querySelector('#latitude')?.value || '6.1'}°`,
            'Longitude': `${form.querySelector('#longitude')?.value || '1.2'}°`,
            'Irradiation': `${form.querySelector('#irradiation')?.value || '5.5'} kWh/m²/jour`,
            'Type de Système': capitalize(form.querySelector('#system-type')?.value) || 'Non spécifié',
            'Tension Système': `${form.querySelector('[name="system-voltage"]')?.value || 'N/A'} V`,
            'Type de Panneau': capitalize(form.querySelector('#panel-type')?.value) || 'Non spécifié',
            'Efficacité Panneau': `${form.querySelector('#panel-efficiency')?.value || '15'} %`,
            'Inclinaison': `${form.querySelector('#tilt')?.value || '10'}°`,
            'Orientation (Azimut)': `${form.querySelector('#azimuth')?.value || '0'}°`,
            'Pertes Système': `${form.querySelector('#system-losses')?.value || '15'} %`,
            'Jours d’Autonomie': `${form.querySelector('[name="autonomy-days"]')?.value || 'N/A'} jours`,
            'Type de Batterie': capitalize(form.querySelector('[name="battery-type"]')?.value) || 'N/A',
            'Coût Panneaux': `${form.querySelector('[name="panel-cost"]')?.value || '50000'} CFA/panneau`,
            'Coût Batterie': `${form.querySelector('[name="battery-cost"]')?.value || '28000'} CFA/batterie`,
            'Coût Onduleur': `${form.querySelector('[name="inverter-cost"]')?.value || '120000'} CFA`
        };

        doc.autoTable({
            startY: y,
            head: [['Paramètre', 'Valeur']],
            body: Object.entries(formData).map(([key, value]) => [key, value]),
            styles: { fontSize: 10, cellPadding: 2 },
            headStyles: { fillColor: [21, 128, 61], textColor: [255, 255, 255] },
            margin: { left: 20, right: 20 }
        });
        y = doc.lastAutoTable.finalY + 10;

        // Appliances
        doc.setFontSize(12);
        doc.text('Liste des Appareils', 20, y);
        y += 10;

        const appliances = document.querySelectorAll('.appliance');
        const applianceData = Array.from(appliances).map((appliance, index) => {
            const name = appliance.querySelector(`[name="appliance[${index}][name]"]`)?.value || '';
            return [
                capitalize(name),
                `${appliance.querySelector(`[name="appliance[${index}][power]"]`)?.value || '0'} W`,
                `${appliance.querySelector(`[name="appliance[${index}][quantity]"]`)?.value || '1'}`,
                `${appliance.querySelector(`[name="appliance[${index}][hours]"]`)?.value || '0'} h/jour`
            ];
        });

        doc.autoTable({
            startY: y,
            head: [['Appareil', 'Puissance', 'Quantité', 'Heures/Jour']],
            body: applianceData,
            styles: { fontSize: 10, cellPadding: 2 },
            headStyles: { fillColor: [21, 128, 61], textColor: [255, 255, 255] },
            margin: { left: 20, right: 20 }
        });
        y = doc.lastAutoTable.finalY + 10;

        // Results
        if (results) {
            doc.setFontSize(12);
            doc.text('Résultats du Dimensionnement', 20, y);
            y += 10;

            const systemType = form.querySelector('#system-type')?.value || 'connecte';
            const resultsData = [
                ['Consommation Totale', `${(results.consumption?.toFixed(2) || '0.00')} kWh/jour`],
                ['Type de Système', capitalize(systemType)],
                ['Puissance Crête', `${(results.peak_power?.toFixed(2) || '0.00')} W`],
                ['Énergie à Produire', `${(results.E_p?.toFixed(2) || '0.00')} kWh/jour`],
                ['Puissance PV', `${(results.P_C?.toFixed(2) || '0.00')} kWp`],
                ['Nombre de Panneaux', `${results.N_P || '0'}`],
                ['Nombre de Panneaux en Série', `${results.N_PS || '0'}`],
                ['Nombre de Panneaux en Parallèle', `${results.N_PP || '0'}`],
                ['Surface Totale', `${(results.S_t?.toFixed(2) || '0.00')} m²`],
                ['Inclinaison Optimale', `${results.tilt || '0'}°`],
                ['Orientation (Azimut)', `${results.azimuth || '0'}°`]
            ];

            if (systemType !== 'connecte') {
                resultsData.push(['Capacité Batterie', `${(results.battery_capacity?.toFixed(2) || '0.00')} Ah (${results.system_voltage || 'N/A'}V)`]);
                resultsData.push(['Nombre de Batteries', `${results.battery_count || '0'}`]);
                resultsData.push(['Contrôleur Courant', `${(results.controller_current?.toFixed(2) || '0.00')} A`]);
            }

            resultsData.push(['Coût Total Système', `${(results.total_cost?.toFixed(2) || '0.00')} CFA`]);

            doc.autoTable({
                startY: y,
                head: [['Paramètre', 'Valeur']],
                body: resultsData,
                styles: { fontSize: 10, cellPadding: 2 },
                headStyles: { fillColor: [21, 128, 61], textColor: [255, 255, 255] },
                margin: { left: 20, right: 20 }
            });
            y = doc.lastAutoTable.finalY + 10;

            // Cost Breakdown
            doc.setFontSize(12);
            doc.text('Répartition des Coûts', 20, y);
            y += 10;

            const costData = [
                ['Panneaux Solaires', `${(results.panel_cost?.toFixed(2) || '0.00')} CFA`]
            ];
            if (systemType !== 'connecte') {
                costData.push(['Batteries', `${(results.battery_cost?.toFixed(2) || '0.00')} CFA`]);
            }
            if (systemType === 'hybride') {
                costData.push(['Onduleur', `${(results.inverter_cost?.toFixed(2) || '0.00')} CFA`]);
            }
            costData.push(['Total', `${(results.total_cost?.toFixed(2) || '0.00')} CFA`]);

            doc.autoTable({
                startY: y,
                head: [['Composant', 'Coût']],
                body: costData,
                styles: { fontSize: 10, cellPadding: 2 },
                headStyles: { fillColor: [21, 128, 61], textColor: [255, 255, 255] },
                margin: { left: 20, right: 20 }
            });
            y = doc.lastAutoTable.finalY + 10;
        } else {
            doc.setFontSize(10);
            doc.setTextColor(239, 68, 68);
            doc.text('Aucun résultat disponible. Veuillez effectuer les calculs.', 20, y);
            y += 10;
        }

        // Charts
        const charts = [
            { id: 'cost-chart', title: 'Répartition des Coûts' },
            { id: 'irradiation-chart', title: 'Irradiation Solaire Mensuelle' }
        ];

        for (const chart of charts) {
            const canvas = document.getElementById(chart.id);
            if (canvas) {
                if (y > 260) {
                    doc.addPage();
                    y = 20;
                }
                doc.setFontSize(12);
                doc.setTextColor(0);
                doc.text(chart.title, 20, y);
                y += 5;

                const imgData = await html2canvas(canvas, { scale: 2 }).then(c => c.toDataURL('image/png', 0.8));
                const imgWidth = 170;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                doc.addImage(imgData, 'PNG', 20, y, imgWidth, imgHeight, undefined, 'FAST');
                y += imgHeight + 10;
            }
        }

        // Footer and Page Numbering
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.setTextColor(128);
            doc.text(`Page ${i} sur ${pageCount}`, 105, 285, { align: 'center' });
            doc.text(`Généré le ${new Date().toLocaleDateString('fr-FR')} par SolarCalc`, 105, 290, { align: 'center' });
        }

        // Save PDF
        doc.save('dimensionnement-solaire.pdf');
        showToast('PDF généré avec succès !');
    } catch (error) {
        console.error('Erreur lors de la génération du PDF:', error);
        showToast('Erreur lors de la génération du PDF. Veuillez réessayer.', true);
    }
}
 
const confirmReset = () => {
    if (confirm('Voulez-vous vraiment réinitialiser le formulaire ? Toutes les données seront perdues.')) {
        resetToInitialState(true);
    }
};

 
const resetToInitialState = (showMessage = true) => {

    const form = document.getElementById('sizing-form');
    const appliancesDiv = document.getElementById('appliances');
    const resultsTable = document.getElementById('results-table');
    const tiltInput = document.getElementById('tilt');
    const tiltValue = document.getElementById('tilt-value');
    const panel = document.querySelector('.panel-svg .solar-panel');

    if (!form || !appliancesDiv || !resultsTable || !tiltInput || !tiltValue || !panel) {
        showToast('Erreur lors de la réinitialisation : éléments manquants.', true);
        return;
    }

    form.reset();
    appliancesDiv.innerHTML = `
        <div class="appliance flex space-x-4 mb-2">
            <div class="form-group flex-1">
                <label class="block text-sm font-medium">Appareil</label>
                <select name="appliance[0][name]" required class="w-full" onchange="updateAppliancePower(this)">
                    <option value="refrigerateur">Réfrigérateur</option>
                    <option value="ventilateur">Ventilateur</option>
                    <option value="lampe">Lampe LED</option>
                    <option value="television">Télévision</option>
                    <option value="ordinateur">Ordinateur</option>
                    <option value="portable">Portable</option>
                    <option value="autre">Autre</option>
                </select>
                <span class="tooltip">Sélectionnez un appareil électrique à alimenter</span>
            </div>
            <div class="form-group flex-1">
                <label class="block text-sm font-medium">Puissance (W)</label>
                <input type="number" name="appliance[0][power]" min="1" step="1" required placeholder="Ex. 150" class="w-full" onchange="calculateConsumption()">
                <span class="tooltip">Puissance électrique de l'appareil en watts</span>
            </div>
            <div class="form-group flex-1">
                <label class="block text-sm font-medium">Quantité</label>
                <input type="number" name="appliance[0][quantity]" min="1" step="1" value="1" required placeholder="Ex. 1" class="w-full" onchange="calculateConsumption()">
                <span class="tooltip">Nombre d'appareils identiques</span>
            </div>
            <div class="form-group flex-1">
                <label class="block text-sm font-medium">Heures/jour</label>
                <input type="number" name="appliance[0][hours]" min="0" step="0.1" required placeholder="Ex. 5" class="w-full" onchange="calculateConsumption()">
                <span class="tooltip">Durée d'utilisation quotidienne en heures</span>
            </div>
            <button type="button" onclick="removeAppliance(this)" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
        </div>
    `;
    document.getElementById('total-consumption').value = '';
    resultsTable.style.display = 'none';
    document.getElementById('battery-section').style.display = 'none';
    document.getElementById('system-voltage-section').style.display = 'none';
    tiltInput.value = 10;
    tiltValue.textContent = '10°';
    panel.setAttribute('transform', `rotate(10, 100, 50)`);
    document.getElementById('panel-efficiency').value = 15;
    document.getElementById('azimuth').value = 0;
    document.getElementById('system-losses').value = 15;
    applianceCount = 1;
    results = null;
    if (costChart) {
        costChart.data.datasets[0].data = [0, 0, 0];
        costChart.update();
    }
    attachApplianceListeners();
    updateProgress();
    if (showMessage) showToast('Formulaire réinitialisé.');
};

document.addEventListener('DOMContentLoaded', () => {
    initializeCharts();

    document.querySelector('.hamburger')?.addEventListener('click', () => {
        const navMenu = document.querySelector('.nav-menu');
        if (navMenu) navMenu.classList.toggle('active');
    });

    document.querySelector('.theme-toggle')?.addEventListener('click', (e) => {
        e.preventDefault();
        const body = document.body;
        if (body) {
            body.classList.toggle('dark');
            
            // Mettre à jour les couleurs de fond
            body.style.backgroundColor = body.classList.contains('dark') ? '#1f2937' : '#f4f4f9';
            
            // Mettre à jour les éléments du formulaire
            document.querySelectorAll('#sizing-form, .chart-container, .results-table, .appliance, .form-group input, .form-group select').forEach(el => {
                if (el) {
                    if (body.classList.contains('dark')) {
                        el.style.backgroundColor = '#374151';
                        el.style.color = '#e5e7eb';
                        if (el.tagName === 'INPUT' || el.tagName === 'SELECT') {
                            el.style.borderColor = '#6b7280';
                        }
                    } else {
                        el.style.backgroundColor = '';
                        el.style.color = '';
                        if (el.tagName === 'INPUT' || el.tagName === 'SELECT') {
                            el.style.borderColor = '';
                        }
                    }
                }
            });
            
            // Mettre à jour les labels
            document.querySelectorAll('.form-group label, .consumption-label').forEach(el => {
                if (el) {
                    el.style.color = body.classList.contains('dark') ? '#e5e7eb' : '';
                }
            });
            
            // Mettre à jour les tooltips
            document.querySelectorAll('.tooltip').forEach(el => {
                if (el) {
                    el.style.backgroundColor = body.classList.contains('dark') ? '#4b5563' : '#1f2937';
                    el.style.color = body.classList.contains('dark') ? '#e5e7eb' : '#fff';
                }
            });
            
            // Mettre à jour l'icône du thème
            const themeIcon = document.querySelector('.theme-toggle i');
            if (themeIcon) {
                themeIcon.className = body.classList.contains('dark') ? 'fas fa-sun' : 'fas fa-moon';
            }
        }
    });

    const systemType = document.getElementById('system-type');
    if (systemType) {
        systemType.addEventListener('change', (e) => {
            const batterySection = document.getElementById('battery-section');
            const voltageSection = document.getElementById('system-voltage-section');
            if (batterySection && voltageSection) {
                const isConnected = e.target.value === 'connecte';
                batterySection.style.display = isConnected ? 'none' : 'block';
                voltageSection.style.display = isConnected ? 'none' : 'block';
                updateProgress();
            }
        });
    }

    const tiltInput = document.getElementById('tilt');
    const tiltValue = document.getElementById('tilt-value');
    const panelSvg = document.querySelector('.panel-svg');
    if (tiltInput && tiltValue && panelSvg) {
        tiltInput.addEventListener('input', () => {
            const angle = tiltInput.value;
            tiltValue.textContent = `${angle}°`;
            const panel = panelSvg.querySelector('.solar-panel');
            if (panel) panel.setAttribute('transform', `rotate(${angle}, 100, 50)`);
        });
    }

    const form = document.getElementById('sizing-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const consumption = document.getElementById('total-consumption')?.value;
            const latitude = document.getElementById('latitude')?.value;
            if (!consumption || parseFloat(consumption) <= 0 || !latitude || parseFloat(latitude) < -90 || parseFloat(latitude) > 90) {
                showToast('Veuillez vérifier la consommation et la latitude.', true);
                return;
            }

            const spinner = document.getElementById('loading-spinner');
            if (spinner) spinner.style.display = 'inline-block';
            try {
                const formData = new FormData(form);
                const response = await fetch('processus.php', {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) throw new Error(`Erreur réseau: ${response.statusText}`);
                const data = await response.json();
                if (data.error) {
                    showToast(`Erreur: ${escapeHTML(data.error)}`, true);
                    return;
                }
                results = data;
                updateResults(data);
                showToast('Calculs effectués avec succès !');
            } catch (error) {
                showToast(`Erreur lors du calcul: ${escapeHTML(error.message)}`, true);
            } finally {
                if (spinner) spinner.style.display = 'none';
            }
        });
    }

    attachApplianceListeners();
    updateProgress();
    document.querySelectorAll('#sizing-form input, #sizing-form select').forEach(el => {
        if (el) el.addEventListener('input', updateProgress);
    });

    // --- Carte de position utilisateur ---
    function showUserMap(lat, lng) {
        const mapUrl = `https://staticmap.openstreetmap.de/staticmap.php?center=${lat},${lng}&zoom=13&size=400x250&markers=${lat},${lng},red-pushpin`;
        const img = document.getElementById('user-map-img');
        const loading = document.getElementById('user-map-loading');
        if (img && loading) {
            img.src = mapUrl;
            img.style.display = 'block';
            loading.style.display = 'none';
        }
    }

    function showMapError(msg) {
        const loading = document.getElementById('user-map-loading');
        const img = document.getElementById('user-map-img');
        if (loading && img) {
            loading.textContent = msg;
            img.style.display = 'none';
        }
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                showUserMap(position.coords.latitude, position.coords.longitude);
            },
            function(error) {
                showMapError("Impossible de récupérer votre position géographique.");
            }
        );
    } else {
        showMapError("La géolocalisation n'est pas supportée par ce navigateur.");
    }
});

// Fonction pour calculer l'inclinaison optimale
function calculateOptimalTilt(latitude) {
    // Calcul de base selon la latitude
    let baseTilt;
    
    if (Math.abs(latitude) <= 25) {
        // Zone tropicale : inclinaison plus faible
        baseTilt = Math.abs(latitude) * 0.87;
    } else if (Math.abs(latitude) <= 50) {
        // Zone tempérée : inclinaison moyenne
        baseTilt = Math.abs(latitude) * 0.9 + 2;
    } else {
        // Zone polaire : inclinaison plus forte
        baseTilt = Math.abs(latitude) * 0.93 + 3;
    }
    
    // Ajustement pour l'hémisphère
    if (latitude < 0) {
        baseTilt = -baseTilt;
    }
    
    // Limiter entre 0 et 90 degrés
    return Math.max(0, Math.min(90, Math.round(baseTilt)));
}

// Fonction pour mettre à jour l'inclinaison
function updateTiltFromCoordinates() {
    const latitude = parseFloat(document.getElementById('latitude').value);
    if (!isNaN(latitude)) {
        const optimalTilt = calculateOptimalTilt(latitude);
        const tiltInput = document.getElementById('tilt');
        const tiltValue = document.getElementById('tilt-value');
        const panel = document.querySelector('.panel-svg .solar-panel');
        const tiltMessage = document.getElementById('tilt-message');
        
        if (tiltInput && tiltValue && panel && tiltMessage) {
            tiltInput.value = optimalTilt;
            tiltValue.textContent = `${optimalTilt}°`;
            panel.setAttribute('transform', `rotate(${optimalTilt}, 100, 50)`);
            
            // Message d'information
            let message = '';
            if (Math.abs(latitude) <= 25) {
                message = `<i class="fas fa-info-circle"></i> Inclinaison optimale pour la zone tropicale (${latitude.toFixed(2)}°) : ${optimalTilt}°`;
            } else if (Math.abs(latitude) <= 50) {
                message = `<i class="fas fa-info-circle"></i> Inclinaison optimale pour la zone tempérée (${latitude.toFixed(2)}°) : ${optimalTilt}°`;
            } else {
                message = `<i class="fas fa-info-circle"></i> Inclinaison optimale pour la zone polaire (${latitude.toFixed(2)}°) : ${optimalTilt}°`;
            }
            
            tiltMessage.innerHTML = message;
        }
    }
}

// Fonction pour afficher les détails de l'inclinaison
function updateTiltDetails(tilt, latitude) {
    const tiltDetails = document.getElementById('tilt-details');
    if (!tiltDetails) return;

    const hemisphere = latitude >= 0 ? 'Nord' : 'Sud';
    const zone = Math.abs(latitude) < 23.5 ? 'Tropicale' :
                Math.abs(latitude) > 60 ? 'Polaire' : 'Tempérée';

    tiltDetails.innerHTML = `
        <div class="mt-2 text-sm text-gray-600">
            <p><strong>Hémisphère:</strong> ${hemisphere}</p>
            <p><strong>Zone climatique:</strong> ${zone}</p>
            <p><strong>Inclinaison optimale:</strong> ${tilt}°</p>
            <p class="text-xs mt-1">Cette inclinaison maximise la production d'énergie solaire pour votre position.</p>
        </div>
    `;
}

// Fonction améliorée pour calculer l'irradiation
function calculateIrradiation(latitude) {
    const baseValues = {
        north: {
            base: 5.5,
            factor: 0.05
        },
        south: {
            base: 5.2,
            factor: 0.04
        }
    };

    let irradiation;
    if (latitude >= 0) {
        irradiation = baseValues.north.base - Math.abs(latitude - 6.1) * baseValues.north.factor;
    } else {
        irradiation = baseValues.south.base - Math.abs(latitude + 6.1) * baseValues.south.factor;
    }

    return Math.max(4.0, Math.min(6.5, irradiation));
}

// Fonction pour mettre à jour l'irradiation
function updateIrradiation(latitude) {
    const irradiationInput = document.getElementById('irradiation');
    if (!irradiationInput) return;

    if (isNaN(latitude) || latitude < -90 || latitude > 90) {
        irradiationInput.value = '';
        return;
    }

    const irradiation = calculateIrradiation(latitude);
    irradiationInput.value = irradiation.toFixed(2);
    updateIrradiationChart(irradiation);
}

// Fonction pour mettre à jour le graphique d'irradiation
function updateIrradiationChart(irradiation) {
    if (!irradiationChart) return;
    
        const monthlyVariation = [0.9, 0.95, 1.0, 1.05, 1.1, 1.05, 1.0, 0.95, 0.9, 0.95, 1.0, 1.05];
    const monthlyData = monthlyVariation.map(v => irradiation * v);
    
    irradiationChart.data.datasets[0].data = monthlyData;
        irradiationChart.update();
    }

// Fonction pour mettre à jour l'affichage
function updateIrradiationFromGSA() {
    const irradiationInput = document.getElementById('irradiation');
    if (!irradiationInput) return;

    const irradiation = calculateIrradiation(parseFloat(document.getElementById('latitude').value));
    irradiationInput.value = irradiation.toFixed(2);
    updateIrradiationChart(irradiation);
}

// Fonction pour afficher les erreurs
function showError(message) {
    const loadingElement = document.getElementById('user-position-loading');
    const errorElement = document.getElementById('user-position-error');
    
    loadingElement.style.display = 'none';
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    
    showToast(message, true);
}

// Fonction améliorée pour la géolocalisation
function getUserLocation() {
    const loadingElement = document.getElementById('user-position-loading');
    const positionElement = document.getElementById('user-position');
    const errorElement = document.getElementById('user-position-error');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');

    if (!navigator.geolocation) {
        showError("La géolocalisation n'est pas supportée par votre navigateur.");
        return;
    }

    loadingElement.style.display = 'block';
    positionElement.style.display = 'none';
    errorElement.style.display = 'none';

    const options = {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 0
    };

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            if (latitudeInput) latitudeInput.value = lat.toFixed(6);
            if (longitudeInput) longitudeInput.value = lng.toFixed(6);
            
            // Mettre à jour l'irradiation et l'inclinaison
            updateIrradiation(lat);
            updateTiltFromCoordinates();
            
            // Mettre à jour l'affichage
            positionElement.innerHTML = `
                <div class="mt-2">
                    <p><strong>Latitude:</strong> ${lat.toFixed(6)}°</p>
                    <p><strong>Longitude:</strong> ${lng.toFixed(6)}°</p>
                    <p><strong>Irradiation:</strong> ${document.getElementById('irradiation').value} kWh/m²/jour</p>
                </div>
            `;
            
            loadingElement.style.display = 'none';
            positionElement.style.display = 'block';
            
            showToast('Position géographique mise à jour avec succès !');
        },
        function(error) {
            let errorMessage = "Erreur lors de la récupération de votre position : ";
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += "Vous devez autoriser l'accès à votre position.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += "Les informations de position ne sont pas disponibles.";
                    break;
                case error.TIMEOUT:
                    errorMessage += "La requête de position a expiré.";
                    break;
                default:
                    errorMessage += "Une erreur inconnue s'est produite.";
            }
            showError(errorMessage);
        },
        options
    );
}

// Ajouter les écouteurs d'événements
document.addEventListener('DOMContentLoaded', () => {
    const latitudeInput = document.getElementById('latitude');
    if (latitudeInput) {
        latitudeInput.addEventListener('input', function() {
            const lat = parseFloat(this.value);
            updateIrradiation(lat);
            updateTiltFromCoordinates();
        });
    }

    // Initialiser la géolocalisation
    getUserLocation();
});

// Fonction pour mettre à jour les dimensions des panneaux
function updatePanelDimensions() {
    const panelType = document.getElementById('panel-type').value;
    const panelLength = document.getElementById('panel-length');
    const panelWidth = document.getElementById('panel-width');
    
    const dimensions = {
        'mono': { length: 176, width: 104 },  // Monocristallin 400W
        'poly': { length: 165, width: 99 },   // Polycristallin 300W
        'Amorp': { length: 158, width: 80 }   // Amorphe 200W
    };

    if (dimensions[panelType]) {
        panelLength.value = dimensions[panelType].length;
        panelWidth.value = dimensions[panelType].width;
    }
}

// Initialiser les dimensions au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    updatePanelDimensions();
});

// Fonction pour mettre à jour la puissance par défaut selon l'appareil
function updateAppliancePower(selectElement) {
    const powerInput = selectElement.closest('.appliance').querySelector('input[name$="[power]"]');
    const defaultPowers = {
        'refrigerateur': 150,
        'ventilateur': 50,
        'lampe': 10,
        'television': 100,
        'ordinateur': 200,
        'portable': 2,
        'autre': 0
    };
    
    const selectedValue = selectElement.value;
    if (powerInput && defaultPowers[selectedValue] !== undefined) {
        powerInput.value = defaultPowers[selectedValue];
        calculateConsumption();
    }
}

// Ajouter le bouton d'export PDF
document.addEventListener('DOMContentLoaded', function() {
    const exportButton = document.createElement('button');
    exportButton.className = 'btn btn-action';
    exportButton.innerHTML = '<i class="fas fa-file-pdf"></i> Exporter en PDF';
    exportButton.onclick = generatePDF;
    
    const resultsContainer = document.querySelector('.results-container');
    if (resultsContainer) {
        resultsContainer.insertBefore(exportButton, resultsContainer.firstChild);
    }
});


</script>
<a class="weatherwidget-io" href="https://forecast7.com/fr/6d13n1d21/lome/" data-label_1="LOMÉ" data-label_2="MÉTÉO" data-theme="original" >LOMÉ MÉTÉO</a>
<script>
!function(d,s,id){
    var js,fjs=d.getElementsByTagName(s)[0];
    if(!d.getElementById(id)){
        js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';
        fjs.parentNode.insertBefore(js,fjs);
    }
}(document,'script','weatherwidget-io-js');
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="assets/js/generatePDF.js"></script>

</body>
</html>