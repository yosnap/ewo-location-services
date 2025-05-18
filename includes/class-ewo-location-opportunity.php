<?php
/**
 * Lógica de oportunidades para el plugin EWO Location Services
 *
 * @since 1.0.0
 * @package Ewo_Location_Services
 */

class Ewo_Location_Opportunity {
    private $plugin_name;
    private $version;
    private $logger;

    public function __construct($plugin_name, $version, $logger = null) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->logger = $logger;
        // Registrar handler AJAX para crear oportunidad
        add_action('wp_ajax_ewo_create_opportunity', array($this, 'handle_create_opportunity'));
        add_action('wp_ajax_nopriv_ewo_create_opportunity', array($this, 'handle_create_opportunity'));
    }

    /**
     * Maneja la solicitud AJAX para crear una oportunidad.
     */
    public function handle_create_opportunity() {
        check_ajax_referer('ewo_location_services_nonce', 'nonce');
        $data = $_POST;
        // Obtener opciones y configuración del API
        $options = get_option('ewo_location_services_options');
        $environment = isset($options['api_environment']) ? $options['api_environment'] : 'development';
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        // Determinar la URL del endpoint basada en el entorno
        $api_url = '';
        if ($environment === 'development') {
            $api_url = isset($options['dev_create_opportunity_url']) ? $options['dev_create_opportunity_url'] : '';
        } else {
            $api_url = isset($options['prod_create_opportunity_url']) ? $options['prod_create_opportunity_url'] : '';
        }
        if (empty($api_url)) {
            wp_send_json_error(array('message' => 'API URL not configured.'));
        }
        // Armar el array de datos para la API externa
        $payload = array(
            'api-key' => $api_key,
            'first_name' => $data['user']['first_name'] ?? '',
            'last_name' => $data['user']['last_name'] ?? '',
            'email' => $data['user']['email'] ?? '',
            'mobile_number' => $data['user']['mobile_number'] ?? ($data['mobile_number'] ?? ''),
            'latitude' => $data['latitude'] ?? '',
            'longitude' => $data['longitude'] ?? '',
            'service_id' => $data['service_id'] ?? '',
            'addons' => $data['addons'] ?? array(),
            'address_line_one' => $data['address_line_one'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'zip' => $data['zip'] ?? '',
        );
        // Incluir serviceability_results_json si está presente
        if (!empty($data['serviceability_results_json'])) {
            $payload['serviceability_results_json'] = is_string($data['serviceability_results_json']) ? json_decode($data['serviceability_results_json'], true) : $data['serviceability_results_json'];
        }
        // Logging para depuración
        if ($this->logger && method_exists($this->logger, 'info')) {
            $this->logger->info('Sending createOpportunity payload: ' . json_encode($payload));
        }
        // Preparar los argumentos para la solicitud a la API
        $args = array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($payload),
            'method' => 'POST',
            'timeout' => 45,
            'data_format' => 'body'
        );
        $response = wp_remote_post($api_url, $args);
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            if ($this->logger && method_exists($this->logger, 'error')) {
                $this->logger->error('createOpportunity API error: ' . $error_message);
            }
            wp_send_json_error(array('message' => 'API request failed', 'error' => $error_message));
        }
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        if ($this->logger && method_exists($this->logger, 'info')) {
            $this->logger->info('createOpportunity API response: ' . $body);
        }
        wp_send_json_success(array('api_response' => $result));
    }
} 