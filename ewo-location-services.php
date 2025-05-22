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
require_once EWO_LOCATION_PLUGIN_DIR . 'includes/class-ewo-location-loader.php';

// Hooks de inicialización
register_activation_hook(__FILE__, ['Ewo_Location_Services_Admin', 'activate']);
register_deactivation_hook(__FILE__, ['Ewo_Location_Services_Admin', 'deactivate']);

// Añadir filtro global para cargar scripts ES6 como módulos
add_filter('script_loader_tag', 'ewo_location_module_loader', 10, 3);
function ewo_location_module_loader($tag, $handle, $src) {
    $module_handles = [
        'ewo-user-addons-modal',
        'ewo-plans-slider',
        // Agrega aquí otros handles de módulos ES6 si los necesitas
    ];
    if (in_array($handle, $module_handles, true)) {
        // Forzar type="module" y mantener id y src
        return '<script type="module" src="' . esc_url($src) . '" id="' . esc_attr($handle) . '"></script>';
    }
    return $tag;
}

// ... Aquí se pueden añadir más hooks y lógica global ... 