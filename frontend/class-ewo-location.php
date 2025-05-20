<?php
if (!defined('ABSPATH')) exit;

class Ewo_Location_Step_Location {
    public function __construct() {
        add_shortcode('ewo_location_services', [$this, 'render_shortcode']);
        add_shortcode('ewo_location_form_only', [$this, 'render_form_only_shortcode']);
        add_shortcode('ewo_location_coverage', [$this, 'render_coverage_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        // Handler AJAX para cobertura
        add_action('wp_ajax_ewo_check_coverage', [$this, 'ajax_check_coverage']);
        add_action('wp_ajax_nopriv_ewo_check_coverage', [$this, 'ajax_check_coverage']);
    }
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
        $google_autocomplete_api_key = get_option('ewo_autocomplete_google_api_key', '');
        $mapbox_autocomplete_api_key = get_option('ewo_autocomplete_mapbox_api_key', '');
        $map_zoom = get_option('ewo_map_zoom', 15);
        // Obtener entorno actual y endpoint + API key
        $env = get_option('ewo_env', 'dev');
        $endpoint_key = $env === 'prod' ? 'ewo_endpoint_getServiceabilityDetails_prod' : 'ewo_endpoint_getServiceabilityDetails_dev';
        $get_serviceability_url = get_option($endpoint_key, '');
        $api_key = get_option('ewo_api_key', '');
        // Obtener páginas mapeadas
        $page_coverage_yes = get_permalink(get_option('ewo_page_coverage_yes'));
        $page_coverage_maybe = get_permalink(get_option('ewo_page_coverage_maybe'));
        $page_coverage_not_yet = get_permalink(get_option('ewo_page_coverage_not_yet'));
        $page_coverage_no = get_permalink(get_option('ewo_page_coverage_no'));
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
            'apiKey' => $api_key,
            'pageCoverageYes' => $page_coverage_yes,
            'pageCoverageMaybe' => $page_coverage_maybe,
            'pageCoverageNotYet' => $page_coverage_not_yet,
            'pageCoverageNo' => $page_coverage_no
        ]);
        $output .= ';</script>';
        $output .= '<div id="ewo-location-root" data-featured-image="' . esc_url($featured_image_url) . '"></div>';
        return $output;
    }

    public function enqueue_assets() {
        wp_enqueue_script('ewo-location-form', plugin_dir_url(__FILE__) . '../frontend/js/ewo-location-form.js', [], null, true);
        wp_enqueue_style('ewo-location-form', plugin_dir_url(__FILE__) . '../frontend/css/ewo-location-form.css', [], null);
    }

    /**
     * Shortcode para mostrar contenido solo si coverage_status es Yes
     * Uso: [ewo_location_coverage status="Yes"]Contenido aquí[/ewo_location_coverage]
     */
    public function render_coverage_shortcode($atts = [], $content = null) {
        $atts = shortcode_atts([
            'status' => ''
        ], $atts);
        $allowed = ['yes', 'maybe', 'not yet', 'no'];
        $status = strtolower(trim($atts['status']));
        $status = preg_replace('/\s+/', ' ', $status); // normaliza espacios
        if (in_array($status, $allowed, true)) {
            return do_shortcode($content);
        }
        return '';
    }

    public function ajax_check_coverage() {
        $lat = isset($_POST['lat']) ? sanitize_text_field($_POST['lat']) : '';
        $lng = isset($_POST['lng']) ? sanitize_text_field($_POST['lng']) : '';
        if (!$lat || !$lng) {
            wp_send_json_error(['message' => 'Missing coordinates']);
        }
        // Obtener config
        $options = get_option('ewo_location_services_options');
        $env = isset($options['api_environment']) ? $options['api_environment'] : 'development';
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        $url = '';
        if ($env === 'production') {
            $url = isset($options['prod_get_serviceability_details_url']) ? $options['prod_get_serviceability_details_url'] : '';
        } else {
            $url = isset($options['dev_get_serviceability_details_url']) ? $options['dev_get_serviceability_details_url'] : '';
        }
        if (!$url) {
            wp_send_json_error(['message' => 'API endpoint not configured']);
        }
        $body = [
            'api-key' => $api_key,
            'latitude' => $lat,
            'longitude' => $lng,
            'requesting_system' => 'website'
        ];
        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'api-key' => $api_key
            ],
            'body' => json_encode($body),
            'timeout' => 30
        ];
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'API request failed']);
        }
        $payload = json_decode(wp_remote_retrieve_body($response), true);
        // Extraer coverage_status
        $status = null;
        if (isset($payload['return-data']['serviceability-info']['coverage_confidence']['coverage_status'])) {
            $raw_status = strtolower($payload['return-data']['serviceability-info']['coverage_confidence']['coverage_status']);
            // Normalizar
            if ($raw_status === 'yes') $status = 'yes';
            elseif ($raw_status === 'maybe') $status = 'maybe';
            elseif ($raw_status === 'no') $status = 'no';
            elseif ($raw_status === 'not yet') $status = 'not yet';
            elseif ($raw_status === 'notyet') $status = 'not yet';
            else $status = $raw_status;
        }
        wp_send_json_success(['status' => $status, 'raw' => $payload]);
    }
}

// Instanciar la clase
new Ewo_Location_Step_Location(); 