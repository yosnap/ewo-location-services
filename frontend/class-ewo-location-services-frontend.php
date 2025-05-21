<?php

class EWO_Location_Services_Frontend {
    public function __construct() {
        // Registrar el shortcode del formulario Billing Details
        add_shortcode('ewo_billing_details_form', [$this, 'render_billing_details_form']);
        // Encolar scripts y estilos
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        // ... existing enqueues ...
        // Enqueue Billing Details Form CSS
        wp_enqueue_style(
            'ewo-billing-details-form',
            plugins_url('frontend/css/ewo-billing-details-form.css', dirname(__FILE__)),
            [],
            filemtime(plugin_dir_path(__FILE__) . '../frontend/css/ewo-billing-details-form.css')
        );
        // Inyectar variables CSS desde opciones del plugin
        $primary = get_option('ewo_primary_color', '#00b6f0');
        $secondary = get_option('ewo_secondary_color', '#0099c8');
        $alert = get_option('ewo_alert_color', '#e74c3c');
        $custom_css = ":root { --primary: {$primary}; --secondary: {$secondary}; --alert: {$alert}; }";
        wp_add_inline_style('ewo-billing-details-form', $custom_css);
        // Enqueue Billing Details Form JS
        wp_enqueue_script(
            'ewo-billing-details-form',
            plugins_url('frontend/js/ewo-billing-details-form.js', dirname(__FILE__)),
            array(),
            filemtime(plugin_dir_path(__FILE__) . '../frontend/js/ewo-billing-details-form.js'),
            true
        );
        // Localize ajax_url
        wp_localize_script('ewo-billing-details-form', 'ewoLocationConfig', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
        // ... existing code ...
    }

    public function render_billing_details_form($atts = []) {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/ewo-billing-details-form.php';
        return ob_get_clean();
    }
}

// Instanciar la clase para que el shortcode esté disponible
global $ewo_location_services_frontend;
$ewo_location_services_frontend = new EWO_Location_Services_Frontend(); 