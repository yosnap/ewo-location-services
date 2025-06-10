<?php
if (!defined('ABSPATH')) exit;

class Ewo_Location_Step_Location {
    public function __construct() {
        add_shortcode('ewo_location_services', [$this, 'render_shortcode']);
        add_shortcode('ewo_location_form_only', [$this, 'render_form_only_shortcode']);
        add_shortcode('ewo_location_coverage', [$this, 'render_coverage_shortcode']);
        add_shortcode('ewo_location_plans_slider', [$this, 'render_plans_slider_shortcode']);
        add_shortcode('ewo_cart', [$this, 'render_cart_shortcode']);
        add_shortcode('ewo_checkout', [$this, 'render_checkout_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        // Handler AJAX para cobertura
        add_action('wp_ajax_ewo_check_coverage', [$this, 'ajax_check_coverage']);
        add_action('wp_ajax_nopriv_ewo_check_coverage', [$this, 'ajax_check_coverage']);
        // Handler AJAX para obtener planes
        add_action('wp_ajax_ewo_get_plans', [$this, 'ajax_get_plans']);
        add_action('wp_ajax_nopriv_ewo_get_plans', [$this, 'ajax_get_plans']);
        // Al final del archivo o en el constructor global:
        if (!has_filter('script_loader_tag', 'ewo_location_module_loader')) {
            add_filter('script_loader_tag', function($tag, $handle) {
                if (in_array($handle, ['ewo-user-addons-modal', 'ewo-plans-slider'])) {
                    return str_replace('text/javascript', 'module', $tag);
                }
                return $tag;
            }, 10, 2);
        }
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
        $cart_page = get_permalink(get_option('ewo_page_cart'));
        $checkout_page = get_permalink(get_option('ewo_page_checkout'));
        $installation_page = get_permalink(get_option('ewo_page_installation'));
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
            'pageCoverageNo' => $page_coverage_no,
            'cartPage' => $cart_page,
            'checkoutPage' => $checkout_page,
            'installationPage' => $installation_page
        ]);
        $output .= ';</script>';
        $output .= '<div id="ewo-location-root" data-featured-image="' . esc_url($featured_image_url) . '"></div>';
        return $output;
    }

    public function enqueue_assets() {
        // Imprimir las variables CSS globales en el head
        add_action('wp_head', function() {
            $primary = get_option('ewo_color_primary', '#203F9A');
            $secondary = get_option('ewo_color_secondary', '#58DCFC');
            $alert = get_option('ewo_color_alert', '#FF4B4B');
            echo '<style id="ewo-global-css-vars">:root {';
            echo '--ewo-primary: ' . esc_attr($primary) . ';';
            echo '--ewo-secondary: ' . esc_attr($secondary) . ';';
            echo '--ewo-alert: ' . esc_attr($alert) . ';';
            echo '}</style>';

            // Imprimir la variable global window.ewoLocationConfig en todo el sitio
            $map_provider = get_option('ewo_map_provider', 'osm');
            $autocomplete_provider = get_option('ewo_autocomplete_provider', 'osm');
            $google_maps_api_key = get_option('ewo_google_maps_api_key', '');
            $mapbox_api_key = get_option('ewo_mapbox_api_key', '');
            $google_autocomplete_api_key = get_option('ewo_autocomplete_google_api_key', '');
            $mapbox_autocomplete_api_key = get_option('ewo_autocomplete_mapbox_api_key', '');
            $map_zoom = get_option('ewo_map_zoom', 15);
            $env = get_option('ewo_env', 'dev');
            $endpoint_key = $env === 'prod' ? 'ewo_endpoint_getServiceabilityDetails_prod' : 'ewo_endpoint_getServiceabilityDetails_dev';
            $get_serviceability_url = get_option($endpoint_key, '');
            $api_key = get_option('ewo_api_key', '');
            $page_coverage_yes = get_permalink(get_option('ewo_page_coverage_yes'));
            $page_coverage_maybe = get_permalink(get_option('ewo_page_coverage_maybe'));
            $page_coverage_not_yet = get_permalink(get_option('ewo_page_coverage_not_yet'));
            $page_coverage_no = get_permalink(get_option('ewo_page_coverage_no'));
            $cart_page = get_permalink(get_option('ewo_page_cart'));
            $checkout_page = get_permalink(get_option('ewo_page_checkout'));
            $installation_page = get_permalink(get_option('ewo_page_installation'));
            echo '<script>window.ewoLocationConfig = ';
            echo json_encode([
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
                'pageCoverageNo' => $page_coverage_no,
                'cartPage' => $cart_page,
                'checkoutPage' => $checkout_page,
                'installationPage' => $installation_page
            ]);
            echo ';</script>';
        });
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
        $env = get_option('ewo_env', 'dev');
        $api_key = get_option('ewo_api_key', '');
        $url = '';
        if ($env === 'prod') {
            $url = get_option('ewo_endpoint_getServiceabilityDetails_prod', '');
        } else {
            $url = get_option('ewo_endpoint_getServiceabilityDetails_dev', '');
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

    public function render_plans_slider_shortcode($atts = []) {
        // Encolar CSS del slider
        wp_enqueue_style('ewo-plans-slider', plugin_dir_url(__FILE__) . '../frontend/css/ewo-plans-slider.css', [], null);
        // Encolar JS del modal y slider como módulos
        wp_enqueue_script(
            'ewo-user-addons-modal',
            plugin_dir_url(__FILE__) . '../frontend/js/ewo-user-addons-modal.js',
            [],
            null,
            true
        );
        wp_enqueue_script(
            'ewo-plans-slider',
            plugin_dir_url(__FILE__) . '../frontend/js/ewo-plans-slider.js',
            ['ewo-user-addons-modal'],
            null,
            true
        );
        // Imprimir el nonce antes del contenedor
        $output = '';
        $output .= '<script>window.ewoLocationServicesNonce = "' . wp_create_nonce('ewo_location_services_nonce') . '";</script>';
        $output .= '<div id="ewo-plans-slider-root"><div class="ewo-plans-slider-loading">Loading plans...</div></div>';
        return $output;
    }

    public function ajax_get_plans() {
        $coverage_code = isset($_GET['coverage_code']) ? sanitize_text_field($_GET['coverage_code']) : '';
        if (!$coverage_code) {
            wp_send_json_error(['message' => 'Missing coverage_code']);
        }
        // Obtener config
        $env = get_option('ewo_env', 'dev');
        $api_key = get_option('ewo_api_key', '');
        $url = '';
        if ($env === 'prod') {
            $url = get_option('ewo_endpoint_getPackages_prod', '');
        } else {
            $url = get_option('ewo_endpoint_getPackages_dev', '');
        }
        if (!$url) {
            wp_send_json_error(['message' => 'API endpoint not configured']);
        }
        // Construir body para la API externa
        $body = [
            'api-key' => $api_key,
            'coverage_code' => $coverage_code,
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
        // Extraer solo los planes (no addons)
        $plans = [];
        if (isset($payload['return-data']['package-data']) && is_array($payload['return-data']['package-data'])) {
            foreach ($payload['return-data']['package-data'] as $plan) {
                if (empty($plan['display_as_addon'])) {
                    $plans[] = [
                        'plan_name' => $plan['plan_name'],
                        'price' => $plan['price'],
                        'speed_download_mbs' => $plan['speed_download_mbs'] ?? '',
                        'plan_description' => $plan['plan_description'] ?? ''
                    ];
                }
            }
        }
        wp_send_json_success($plans);
    }

    public function render_cart_shortcode($atts = []) {
        // Encolar CSS y JS del carrito
        wp_enqueue_style('ewo-cart', plugin_dir_url(__FILE__) . '../frontend/css/ewo-cart.css', [], null);
        wp_enqueue_script('ewo-cart', plugin_dir_url(__FILE__) . '../frontend/js/ewo-cart.js', [], null, true);
        $output = '';
        $output .= '<div id="ewo-cart-root"></div>';
        return $output;
    }

    public function render_checkout_shortcode($atts = []) {
        // Encolar solo el CSS del billing details form
        wp_enqueue_style('ewo-billing-details-form', plugin_dir_url(__FILE__) . '../frontend/css/ewo-billing-details-form.css', [], null);
        // Encolar el JS del billing details form
        wp_enqueue_script('ewo-billing-details-form', plugin_dir_url(__FILE__) . '../frontend/js/ewo-billing-details-form.js', [], null, true);
        // Incluir solo el formulario Billing Details
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/ewo-billing-details-form.php';
        return ob_get_clean();
    }
}

// Instanciar la clase
new Ewo_Location_Step_Location(); 