<?php

class EWO_Location {
    public function render_shortcode($atts = []) {
        global $post;
        $featured_image_url = '';
        if (isset($post) && has_post_thumbnail($post->ID)) {
            $featured_image_url = get_the_post_thumbnail_url($post->ID, 'full');
        }
        // Obtener configuración desde opciones
        $map_provider = get_option('ewo_map_provider', 'osm');
        $autocomplete_provider = get_option('ewo_autocomplete_provider', 'osm');
        $google_maps_api_key = get_option('ewo_google_maps_api_key', '');
        $mapbox_api_key = get_option('ewo_mapbox_api_key', '');
        $google_autocomplete_api_key = get_option('ewo_google_autocomplete_api_key', '');
        $mapbox_autocomplete_api_key = get_option('ewo_mapbox_autocomplete_api_key', '');
        $map_zoom = get_option('ewo_map_zoom', 15);
        // Obtener entorno actual y endpoint + API key
        $env = get_option('ewo_env', 'dev');
        $endpoint_key = $env === 'prod' ? 'ewo_endpoint_getServiceabilityDetails_prod' : 'ewo_endpoint_getServiceabilityDetails_dev';
        $get_serviceability_url = get_option($endpoint_key, '');
        $api_key = get_option('ewo_api_key', '');
        $output = '';
        $output .= '<script>window.ewoLocationConfig = ';
        $output .= json_encode([
            'mapProvider' => $map_provider,
            'autocompleteProvider' => $autocomplete_provider,
            'googleApiKey' => $google_maps_api_key,
            'mapboxApiKey' => $mapbox_api_key,
            'googleAutocompleteApiKey' => $google_autocomplete_api_key,
            'mapboxAutocompleteApiKey' => $mapbox_autocomplete_api_key,
            'mapZoom' => intval($map_zoom),
            'getServiceabilityDetailsUrl' => $get_serviceability_url,
            'apiKey' => $api_key
        ]);
        $output .= ';</script>';
        $output .= '<div id="ewo-location-root" data-featured-image="' . esc_url($featured_image_url) . '"></div>';
        return $output;
    }
} 