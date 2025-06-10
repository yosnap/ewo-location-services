<?php
// Shortcode para el modal de Request a Callback

// Registrar el shortcode [ewo_request_callback_modal]
add_shortcode('ewo_request_callback_modal', function() {
    ob_start();
    include EWO_LOCATION_PLUGIN_DIR . 'templates/ewo-request-callback-form.php';
    return ob_get_clean();
});

// Encolar JS y CSS del modal solo si el shortcode está presente en la página
add_action('wp_enqueue_scripts', function() {
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ewo_request_callback_modal')) {
        wp_enqueue_script(
            'ewo-request-callback',
            plugins_url('frontend/js/ewo-request-callback.js', EWO_LOCATION_PLUGIN_DIR . 'ewo-location-services.php'),
            array(),
            null,
            true
        );
        // Pasar la API Key desde los ajustes al JS
        $api_key = get_option('ewo_api_key', '');
        wp_localize_script('ewo-request-callback', 'ewoRequestCallbackVars', array(
            'apiKey' => $api_key
        ));
        wp_enqueue_style(
            'ewo-modal',
            plugins_url('frontend/css/ewo-modal.css', EWO_LOCATION_PLUGIN_DIR . 'ewo-location-services.php')
        );
        wp_enqueue_style(
            'ewo-form',
            plugins_url('frontend/css/ewo-form.css', EWO_LOCATION_PLUGIN_DIR . 'ewo-location-services.php')
        );
    }
}); 