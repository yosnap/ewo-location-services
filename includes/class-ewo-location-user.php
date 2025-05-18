<?php
/**
 * Lógica de usuarios para el plugin EWO Location Services
 *
 * @since 1.0.0
 * @package Ewo_Location_Services
 */

class Ewo_Location_User {
    private $plugin_name;
    private $version;
    private $logger;

    public function __construct($plugin_name, $version, $logger = null) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->logger = $logger;
        // Registrar handler AJAX para registro de usuario
        add_action('wp_ajax_ewo_submit_user', array($this, 'handle_user_submission'));
        add_action('wp_ajax_nopriv_ewo_submit_user', array($this, 'handle_user_submission'));
    }

    /**
     * Maneja la solicitud AJAX para el registro de usuario.
     */
    public function handle_user_submission() {
        check_ajax_referer('ewo_location_services_nonce', 'nonce');
        $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $service_id = isset($_POST['service_id']) ? sanitize_text_field($_POST['service_id']) : '';
        $addons = isset($_POST['addons']) ? array_map('sanitize_text_field', $_POST['addons']) : array();

        // Validar parámetros requeridos
        if (empty($username) || empty($email) || empty($password)) {
            wp_send_json_error(array('message' => __('Username, email and password are required.', 'ewo-location-services')));
        }

        // Registrar la solicitud
        if ($this->logger && method_exists($this->logger, 'info')) {
            $this->logger->info(sprintf('User submission request for: %s (%s)', $username, $email));
        }

        // Verificar si el usuario ya existe
        if (username_exists($username)) {
            if ($this->logger && method_exists($this->logger, 'warning')) {
                $this->logger->warning('Username already exists: ' . $username);
            }
            wp_send_json_error(array('message' => __('Username already exists. Please choose another one.', 'ewo-location-services')));
        }

        if (email_exists($email)) {
            if ($this->logger && method_exists($this->logger, 'warning')) {
                $this->logger->warning('Email already exists: ' . $email);
            }
            wp_send_json_error(array('message' => __('Email already exists. Please use another one or log in.', 'ewo-location-services')));
        }

        // Crear el usuario en WordPress
        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => 'subscriber'
        );

        $user_id = wp_insert_user($user_data);

        if (is_wp_error($user_id)) {
            if ($this->logger && method_exists($this->logger, 'error')) {
                $this->logger->error('Failed to create user: ' . $user_id->get_error_message());
            }
            wp_send_json_error(array('message' => __('Could not create user. Please try again.', 'ewo-location-services')));
        }

        // Registrar la creación exitosa
        if ($this->logger && method_exists($this->logger, 'info')) {
            $this->logger->info('User created successfully: ' . $user_id);
        }

        // Obtener opciones y configuración del API
        $options = get_option('ewo_location_services_options');
        $environment = isset($options['api_environment']) ? $options['api_environment'] : 'development';
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';

        // Determinar la URL del endpoint basada en el entorno
        $api_url = '';
        if ($environment === 'development') {
            $api_url = isset($options['dev_opportunities_url']) ? $options['dev_opportunities_url'] : '';
        } else {
            $api_url = isset($options['prod_opportunities_url']) ? $options['prod_opportunities_url'] : '';
        }

        // Verificar que tenemos una URL de API
        if (empty($api_url)) {
            if ($this->logger && method_exists($this->logger, 'error')) {
                $this->logger->error('Opportunities API URL not configured for environment: ' . $environment);
            }
            wp_send_json_error(array('message' => __('API not properly configured. User was created but service registration failed.', 'ewo-location-services')));
        }

        // Preparar los datos para enviar a la API de Opportunities
        $opportunity_data = array(
            'user_id' => $user_id,
            'username' => $username,
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'service_id' => $service_id,
            'addons' => $addons
        );

        // Preparar los argumentos para la solicitud a la API
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'api-key' => $api_key // API key en el encabezado
            ),
            'body' => json_encode($opportunity_data), // Datos sin la API key
            'method' => 'POST',
            'timeout' => 45,
            'data_format' => 'body'
        );

        // Realizar la solicitud a la API
        $response = wp_remote_post($api_url, $args);

        // Registrar la respuesta
        if ($this->logger && method_exists($this->logger, 'info')) {
            $this->logger->info('API Response received for opportunity creation');
        }

        // Verificar si hubo un error en la solicitud
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            if ($this->logger && method_exists($this->logger, 'error')) {
                $this->logger->error('Opportunities API Request failed: ' . $error_message);
            }
            wp_send_json_error(array(
                'message' => __('User was created but service registration failed. Please contact support.', 'ewo-location-services'),
                'user_id' => $user_id
            ));
        }

        // Obtener el cuerpo de la respuesta y decodificar JSON
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Verificar si la respuesta es válida
        if (empty($data) || !is_array($data)) {
            if ($this->logger && method_exists($this->logger, 'error')) {
                $this->logger->error('Invalid Opportunities API response format');
            }
            wp_send_json_error(array(
                'message' => __('User was created but received invalid response from service API.', 'ewo-location-services'),
                'user_id' => $user_id
            ));
        }

        // Iniciar sesión automáticamente para el nuevo usuario
        wp_set_auth_cookie($user_id, false);

        // Enviar respuesta de éxito
        wp_send_json_success(array(
            'message' => __('Registration successful! Welcome aboard.', 'ewo-location-services'),
            'user_id' => $user_id,
            'redirect' => home_url()
        ));
    }
} 