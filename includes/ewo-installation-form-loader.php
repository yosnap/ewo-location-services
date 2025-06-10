<?php
// Loader para el formulario de Installation

// Registrar el shortcode [ewo_installation_form] directamente
add_shortcode('ewo_installation_form', function() {
    ob_start();
    include EWO_LOCATION_PLUGIN_DIR . 'templates/ewo-installation-form.php';
    return ob_get_clean();
});

// Encolar CSS de formulario si el shortcode está presente
add_action('wp_enqueue_scripts', function() {
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ewo_installation_form')) {
        wp_enqueue_style(
            'ewo-form',
            plugins_url('frontend/css/ewo-form.css', EWO_LOCATION_PLUGIN_DIR . 'ewo-location-services.php')
        );
    }
}); 