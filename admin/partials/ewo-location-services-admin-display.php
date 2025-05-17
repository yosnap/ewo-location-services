<?php
/**
 * Proporciona una vista de administración para el plugin
 *
 * Esta archivo es usado para marcar el aspecto de la administración del plugin.
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

// No permitir el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="ewo-admin-container">
        <div class="ewo-admin-main">
            <form method="post" action="options.php">
                <?php
                // Output security fields
                settings_fields('ewo_location_services_options');
                // Output setting sections
                do_settings_sections('ewo_location_services_options');
                // Output save settings button
                submit_button(__('Save Settings', 'ewo-location-services'));
                ?>
            </form>
        </div>
        
        <div class="ewo-admin-sidebar">
            <div class="ewo-admin-box">
                <h3><?php _e('Plugin Documentation', 'ewo-location-services'); ?></h3>
                <p><?php _e('Use the shortcode below to display the location services form on any page:', 'ewo-location-services'); ?></p>
                <code>[ewo_location_form]</code>
                
                <p><?php _e('Optional parameters:', 'ewo-location-services'); ?></p>
                <ul>
                    <li><code>title</code> - <?php _e('Change the form title', 'ewo-location-services'); ?></li>
                    <li><code>button_text</code> - <?php _e('Change the search button text', 'ewo-location-services'); ?></li>
                </ul>
                
                <h4><?php _e('Example:', 'ewo-location-services'); ?></h4>
                <code>[ewo_location_form title="Find Services Near You" button_text="Find Now"]</code>
            </div>
            
            <div class="ewo-admin-box">
                <h3><?php _e('Need Help?', 'ewo-location-services'); ?></h3>
                <p><?php _e('For plugin support or feature requests, please contact us:', 'ewo-location-services'); ?></p>
                <a href="mailto:support@example.com" class="button button-secondary">
                    <?php _e('Contact Support', 'ewo-location-services'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
