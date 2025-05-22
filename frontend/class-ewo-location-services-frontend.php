<?php

class EWO_Location_Services_Frontend {

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
} 