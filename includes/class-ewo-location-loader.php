<?php
if (!defined('ABSPATH')) exit;

// Incluir todas las clases de lógica asíncrona
require_once __DIR__ . '/class-ewo-location-plans.php';
require_once __DIR__ . '/class-ewo-location-coverage.php';
// Puedes añadir aquí otras clases como usuario, oportunidad, etc.

// Instanciar las clases que requieren hooks o inicialización
new Ewo_Location_Plans('ewo-location-services', '2.0.0');
// Si tienes otras clases que requieren instancia, instáncialas aquí. 