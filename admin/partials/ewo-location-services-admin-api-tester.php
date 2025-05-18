<?php
/**
 * Proporciona una vista de administración para el probador de API
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

// No permitir el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener las opciones guardadas
$options = get_option('ewo_location_services_options');

// Determinar el entorno actual
$current_environment = isset($options['api_environment']) ? $options['api_environment'] : 'development';

// Obtener las URLs de los endpoints disponibles
$available_endpoints = array();

$endpoint_types = array(
    'get_serviceability_details' => __('Get Serviceability Details', 'ewo-location-services'),
    'get_serviceable_locations_by_latlng' => __('Get Serviceable Locations By Lat/Lng', 'ewo-location-services'),
    'get_packages' => __('Get Packages', 'ewo-location-services'),
    'get_discount_templates' => __('Get Discount Templates', 'ewo-location-services'),
    'add_package_to_parent' => __('Add Package To Parent', 'ewo-location-services'),
    'create_customer' => __('Create Customer', 'ewo-location-services'),
    'update_customer' => __('Update Customer', 'ewo-location-services'),
    'create_customer_comment' => __('Create Customer Comment', 'ewo-location-services'),
    'create_opportunity' => __('Create Opportunity', 'ewo-location-services'),
    'update_opportunity' => __('Update Opportunity', 'ewo-location-services'),
    'get_opportunity' => __('Get Opportunity', 'ewo-location-services'),
);

foreach ($endpoint_types as $key => $label) {
    $dev_key = 'dev_' . $key . '_url';
    $prod_key = 'prod_' . $key . '_url';
    if (!empty($options[$dev_key]) || !empty($options[$prod_key])) {
        $available_endpoints[$key] = $label;
    }
}

// API Key
$api_key = isset($options['api_key']) ? $options['api_key'] : '';
$api_key_masked = !empty($api_key) ? substr($api_key, 0, 4) . '...' . substr($api_key, -4) : '';
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="ewo-admin-container">
        <div class="ewo-admin-main ewo-api-tester">
            <h2><?php _e('API Endpoint Tester', 'ewo-location-services'); ?></h2>
            
            <?php if (empty($api_key)): ?>
                <div class="notice notice-error">
                    <p><?php _e('API Key is not configured. Please set an API Key in the plugin settings before using this tool.', 'ewo-location-services'); ?></p>
                </div>
            <?php elseif (empty($available_endpoints)): ?>
                <div class="notice notice-error">
                    <p><?php _e('No API endpoints are configured. Please configure at least one endpoint in the plugin settings.', 'ewo-location-services'); ?></p>
                </div>
            <?php else: ?>
                <div class="ewo-api-tester-form">
                    <form id="ewo-api-test-form">
                        <div class="ewo-form-row">
                            <label for="endpoint_type"><?php _e('Endpoint:', 'ewo-location-services'); ?></label>
                            <select id="endpoint_type" name="endpoint_type">
                                <?php foreach ($available_endpoints as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="ewo-form-row">
                            <label for="environment"><?php _e('Environment:', 'ewo-location-services'); ?></label>
                            <select id="environment" name="environment">
                                <option value="development" <?php selected($current_environment, 'development'); ?>><?php _e('Development', 'ewo-location-services'); ?></option>
                                <option value="production" <?php selected($current_environment, 'production'); ?>><?php _e('Production', 'ewo-location-services'); ?></option>
                            </select>
                        </div>
                        
                        <div class="ewo-form-row">
                            <label><?php _e('Coordinates:', 'ewo-location-services'); ?></label>
                            <div class="ewo-coordinates-inputs">
                                <input type="text" id="latitude" name="latitude" placeholder="<?php _e('Latitude', 'ewo-location-services'); ?>" value="40.7128">
                                <input type="text" id="longitude" name="longitude" placeholder="<?php _e('Longitude', 'ewo-location-services'); ?>" value="-74.0060">
                                <button type="button" id="ewo-use-map" class="button button-secondary">
                                    <?php _e('Select on Map', 'ewo-location-services'); ?>
                                </button>
                            </div>
                        </div>

                        <div id="ewo-map-container" style="display: none; height: 400px; margin: 20px 0;"></div>
                        
                        <div class="ewo-form-row">
                            <label><?php _e('API Key:', 'ewo-location-services'); ?></label>
                            <span><?php echo esc_html($api_key_masked); ?></span>
                            <p class="description"><?php _e('The API Key is configured in the plugin settings.', 'ewo-location-services'); ?></p>
                        </div>
                        
                        <div class="ewo-form-actions">
                            <button type="submit" id="ewo-test-api" class="button button-primary">
                                <?php _e('Test API', 'ewo-location-services'); ?>
                            </button>
                            <span class="spinner" id="ewo-test-spinner"></span>
                        </div>
                    </form>
                </div>
                
                <div id="ewo-api-response-container" style="display: none;">
                    <h3><?php _e('API Response', 'ewo-location-services'); ?></h3>
                    <div class="ewo-response-status">
                        <span id="ewo-response-status-icon"></span>
                        <span id="ewo-response-status-text"></span>
                    </div>
                    <div class="ewo-response-tabs">
                        <button type="button" class="ewo-tab-button active" data-tab="formatted"><?php _e('Formatted', 'ewo-location-services'); ?></button>
                        <button type="button" class="ewo-tab-button" data-tab="raw"><?php _e('Raw JSON', 'ewo-location-services'); ?></button>
                        <button type="button" class="ewo-tab-button" data-tab="debug"><?php _e('Debug Info', 'ewo-location-services'); ?></button>
                    </div>
                    <div class="ewo-response-content">
                        <div id="ewo-response-formatted" class="ewo-tab-content active"></div>
                        <div id="ewo-response-raw" class="ewo-tab-content">
                            <pre id="ewo-response-json"></pre>
                        </div>
                        <div id="ewo-response-debug" class="ewo-tab-content">
                            <pre id="ewo-debug-json"></pre>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="ewo-admin-sidebar">
            <div class="ewo-admin-box">
                <h3><?php _e('API Tester Instructions', 'ewo-location-services'); ?></h3>
                <ol>
                    <li><?php _e('Select the API endpoint you want to test.', 'ewo-location-services'); ?></li>
                    <li><?php _e('Choose the environment (development or production).', 'ewo-location-services'); ?></li>
                    <li><?php _e('Enter the coordinates or select them on the map.', 'ewo-location-services'); ?></li>
                    <li><?php _e('Click "Test API" to send the request.', 'ewo-location-services'); ?></li>
                </ol>
                <p><?php _e('The API response will be displayed below the form. You can view it in a formatted view or as raw JSON.', 'ewo-location-services'); ?></p>
            </div>
            
            <div class="ewo-admin-box">
                <h3><?php _e('Troubleshooting', 'ewo-location-services'); ?></h3>
                <p><?php _e('If you encounter errors, check the following:', 'ewo-location-services'); ?></p>
                <ul>
                    <li><?php _e('Verify the API Key is correct.', 'ewo-location-services'); ?></li>
                    <li><?php _e('Ensure the endpoint URLs are properly configured.', 'ewo-location-services'); ?></li>
                    <li><?php _e('Check if the coordinates are within a valid service area.', 'ewo-location-services'); ?></li>
                    <li><?php _e('Review the plugin logs for detailed error messages.', 'ewo-location-services'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
