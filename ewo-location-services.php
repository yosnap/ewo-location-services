<?php
/**
 * Plugin Name: Ewo Location Services
 * Plugin URI: https://example.com/ewo-location-services
 * Description: Un plugin que permite a los usuarios encontrar y reservar servicios basados en su ubicación, integra con una API externa, maneja el registro de usuarios y gestiona opciones de venta cruzada.
 * Version: 1.0.0
 * Author: sn4p.dev
 * Author URI: https://sn4p.dev
 * Text Domain: ewo-location-services
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

// Definir constantes para el plugin
define('EWO_LOCATION_SERVICES_VERSION', '1.0.0');
define('EWO_LOCATION_SERVICES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EWO_LOCATION_SERVICES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EWO_LOCATION_SERVICES_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('EWO_LOCATION_SERVICES_LOGS_DIR', EWO_LOCATION_SERVICES_PLUGIN_DIR . 'logs/');

// Incluir los archivos necesarios
require_once EWO_LOCATION_SERVICES_PLUGIN_DIR . 'includes/class-ewo-location-services.php';
require_once EWO_LOCATION_SERVICES_PLUGIN_DIR . 'includes/class-ewo-location-services-logger.php';

/**
 * Comienza la ejecución del plugin.
 */
function run_ewo_location_services() {
    $plugin = new Ewo_Location_Services();
    $plugin->run();
}

// Crear directorio de logs si no existe
if (!file_exists(EWO_LOCATION_SERVICES_LOGS_DIR)) {
    wp_mkdir_p(EWO_LOCATION_SERVICES_LOGS_DIR);
    // Crear archivo index.php para proteger el directorio
    file_put_contents(EWO_LOCATION_SERVICES_LOGS_DIR . 'index.php', '<?php // Silence is golden');
    
    // Intentar crear archivo .htaccess para mayor seguridad
    file_put_contents(EWO_LOCATION_SERVICES_LOGS_DIR . '.htaccess', 'Deny from all');
}

// Ejecutar el plugin
run_ewo_location_services();

// Activación, desactivación y desinstalación del plugin
register_activation_hook(__FILE__, 'activate_ewo_location_services');
register_deactivation_hook(__FILE__, 'deactivate_ewo_location_services');

/**
 * Código ejecutado durante la activación del plugin.
 */
function activate_ewo_location_services() {
    // Inicializar opciones por defecto
    $default_options = array(
        'api_environment' => 'development',
        'api_key' => '',
        'dev_service_lookup_url' => '',
        'prod_service_lookup_url' => '',
        'dev_opportunities_url' => '',
        'prod_opportunities_url' => ''
    );
    
    add_option('ewo_location_services_options', $default_options);
    
    // Crear directorio de logs si no existe
    if (!file_exists(EWO_LOCATION_SERVICES_LOGS_DIR)) {
        wp_mkdir_p(EWO_LOCATION_SERVICES_LOGS_DIR);
    }
    
    // Limpiar rewrite rules
    flush_rewrite_rules();
}

/**
 * Código ejecutado durante la desactivación del plugin.
 */
function deactivate_ewo_location_services() {
    // Limpiar rewrite rules
    flush_rewrite_rules();
}
