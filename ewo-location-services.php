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
require_once EWO_LOCATION_PLUGIN_DIR . 'includes/ewo-request-callback-loader.php';

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

// Enqueue JS y CSS para el formulario de instalación
function ewo_enqueue_installation_form_assets() {
    if (is_singular()) {
        global $post;
        if (has_shortcode($post->post_content, 'ewo_installation_form')) {
            wp_enqueue_script(
                'ewo-installation-form',
                plugins_url('frontend/js/ewo-installation-form.js', __FILE__),
                array(),
                filemtime(plugin_dir_path(__FILE__) . 'frontend/js/ewo-installation-form.js'),
                true
            );
            wp_enqueue_style(
                'ewo-form',
                plugins_url('frontend/css/ewo-form.css', __FILE__),
                array(),
                filemtime(plugin_dir_path(__FILE__) . 'frontend/css/ewo-form.css')
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'ewo_enqueue_installation_form_assets');

// Registrar el shortcode [ewo_installation_form] directamente desde el archivo principal
add_shortcode('ewo_installation_form', function() {
    ob_start();
    include EWO_LOCATION_PLUGIN_DIR . 'templates/ewo-installation-form.php';
    return ob_get_clean();
});

// ... Aquí se pueden añadir más hooks y lógica global ... 