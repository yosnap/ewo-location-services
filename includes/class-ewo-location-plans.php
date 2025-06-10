<?php
/**
 * Lógica de planes y addons para el plugin EWO Location Services
 *
 * @since 1.0.0
 * @package Ewo_Location_Services
 */

class Ewo_Location_Plans {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        // Registrar handler AJAX para obtener paquetes/planes
        add_action('wp_ajax_ewo_get_packages', array($this, 'handle_get_packages'));
        add_action('wp_ajax_nopriv_ewo_get_packages', array($this, 'handle_get_packages'));
    }

    /**
     * Maneja la solicitud AJAX para obtener los planes y addons según coverage_code.
     */
    public function handle_get_packages() {
        check_ajax_referer('ewo_location_services_nonce', 'nonce');
        $coverage_code = isset($_POST['coverage_code']) ? sanitize_text_field($_POST['coverage_code']) : '';
        $latitude = isset($_POST['latitude']) ? sanitize_text_field($_POST['latitude']) : '';
        $longitude = isset($_POST['longitude']) ? sanitize_text_field($_POST['longitude']) : '';
        if (empty($coverage_code)) {
            wp_send_json_error(['message' => 'Missing coverage_code']);
        }
        // Obtener configuración del plugin y entorno
        $env = get_option('ewo_env', 'dev');
        $api_key = get_option('ewo_api_key', '');
        $api_url = '';
        if ($env === 'prod') {
            $api_url = get_option('ewo_endpoint_getPackages_prod', '');
        } else {
            $api_url = get_option('ewo_endpoint_getPackages_dev', '');
        }
        if (empty($api_key) || empty($api_url)) {
            wp_send_json_error(['message' => 'API configuration missing']);
        }
        $payload = [
            'api-key' => $api_key,
            'requesting_system' => 'website',
            'coverage_code' => $coverage_code,
        ];
        if ($latitude) $payload['latitude'] = $latitude;
        if ($longitude) $payload['longitude'] = $longitude;
        $response = wp_remote_post($api_url, [
            'body'    => json_encode($payload),
            'headers' => [
                'Content-Type' => 'application/json',
                'api-key' => $api_key
            ],
            'timeout' => 15,
        ]);
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'API connection error']);
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (empty($data['success']) || empty($data['return-data']['package-data'])) {
            wp_send_json_error(['message' => 'No plans found']);
        }
        $all_plans = $data['return-data']['package-data'];
        wp_send_json_success([
            'packages' => $all_plans,
        ]);
    }
} 