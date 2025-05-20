<?php
/*
Plugin Name: EWO Location Services
Description: Plugin para búsqueda y contratación de servicios según ubicación, con integración a APIs externas y frontend en vanilla JS.
Version: 2.0.0
Author: sn4p.dev
Author URI: https://sn4p.dev
*/

// Evitar acceso directo
if (!defined('ABSPATH')) exit;

// Definir constantes de rutas
if (!defined('EWO_LOCATION_PLUGIN_DIR')) {
    define('EWO_LOCATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('EWO_LOCATION_PLUGIN_URL')) {
    define('EWO_LOCATION_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Cargar archivos de admin y frontend
require_once EWO_LOCATION_PLUGIN_DIR . 'admin/ewo-location-services.php';
require_once plugin_dir_path(__FILE__) . 'frontend/class-ewo-location.php';

// Hooks de inicialización
register_activation_hook(__FILE__, ['Ewo_Location_Services_Admin', 'activate']);
register_deactivation_hook(__FILE__, ['Ewo_Location_Services_Admin', 'deactivate']);

// ... Aquí se pueden añadir más hooks y lógica global ... 