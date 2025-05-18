<?php
/**
 * La funcionalidad específica de la parte pública del plugin.
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

class Ewo_Location_Services_Frontend {

    /**
     * El ID de este plugin.
     *
     * @sinc        // Añadir datos de depuración para administradores
        $debug_data = array(
            'request_url' => $api_url,
            'request_params' => array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'requesting_system' => 'website',
                'api-key' => substr($api_key, 0, 4) . '****' // Mostrar solo primeros 4 caracteres por seguridad
            ),
            'response_status' => wp_remote_retrieve_response_code($response),
            'response_headers' => wp_remote_retrieve_headers($response),
            'timestamp' => current_time('mysql'),
            'response_structure' => $this->analyze_response_structure($data) // Análisis de estructura
        );   * @access   private
     * @var      string    $plugin_name    El ID de este plugin.
     */
    private $plugin_name;

    /**
     * La versión de este plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    La versión actual de este plugin.
     */
    private $version;

    /**
     * El sistema de registro (logger) del plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      Ewo_Location_Services_Logger    $logger    El sistema de registro del plugin.
     */
    private $logger;

    /**
     * Inicializa la clase y establece sus propiedades.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       El nombre del plugin.
     * @param    string    $version           La versión del plugin.
     * @param    object    $logger            El objeto logger del plugin.
     */
    public function __construct($plugin_name, $version, $logger) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->logger = $logger;
    }

    /**
     * Registra los estilos para la parte pública del sitio.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Estilos principales
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ewo-location-services-frontend.css', array(), $this->version, 'all');

        // Estilos para Leaflet (si usamos OpenStreetMap)
        wp_enqueue_style($this->plugin_name . '-leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), '1.7.1', 'all');
        
        // Estilos específicos para la depuración (cargados para administradores)
        if (current_user_can('administrator')) {
            wp_enqueue_style($this->plugin_name . '-debug', plugin_dir_url(__FILE__) . 'css/ewo-location-services-debug.css', array(), $this->version, 'all');
        }
        
        // Enqueue WordPress Dashicons
        wp_enqueue_style('dashicons');
    }

    /**
     * Registra los scripts para la parte pública del sitio.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Scripts principales
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ewo-location-services-frontend.js', array('jquery'), $this->version, false);

        // Scripts para Leaflet (si usamos OpenStreetMap)
        wp_enqueue_script($this->plugin_name . '-leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', false);

        // Forzar carga de jQuery UI Autocomplete y CSS
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');

        // Forzar carga de Algolia Places solo si el proveedor es algolia
        $listing_options = get_option('ewo_service_listing_options', array());
        $autocomplete_provider = isset($listing_options['autocomplete_provider']) ? $listing_options['autocomplete_provider'] : 'nominatim';
        if ($autocomplete_provider === 'algolia') {
            wp_enqueue_script('algolia-places', 'https://cdn.jsdelivr.net/npm/places.js@1.19.0', array(), null, true);
        }

        // Configuración para AJAX
        wp_localize_script($this->plugin_name, 'ewoLocationServices', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ewo_location_services_nonce')
        ));
        // Pasar opciones del admin al JS (transformadas)
        wp_localize_script($this->plugin_name, 'ewoServiceListingOptions', array(
            'columns' => isset($listing_options['grid_columns']) ? intval($listing_options['grid_columns']) : 3,
            'per_page' => isset($listing_options['items_per_page']) ? intval($listing_options['items_per_page']) : 8,
            'show_pagination' => isset($listing_options['show_pagination']) ? $listing_options['show_pagination'] : 'yes',
            'load_more' => isset($listing_options['load_more']) ? $listing_options['load_more'] : 'no',
            'listing_mode' => isset($listing_options['listing_mode']) ? $listing_options['listing_mode'] : 'grid',
            'show_filters' => isset($listing_options['show_filters']) ? $listing_options['show_filters'] : 'yes',
            'card_color_usage' => isset($listing_options['card_color_usage']) ? $listing_options['card_color_usage'] : 'none',
            'form_steps_style' => isset($listing_options['form_steps_style']) ? $listing_options['form_steps_style'] : 'progress_bar',
            'step_active_color' => isset($listing_options['step_active_color']) ? $listing_options['step_active_color'] : '#c2185b',
            'step_inactive_color' => isset($listing_options['step_inactive_color']) ? $listing_options['step_inactive_color'] : '#bbb',
            'autocomplete_provider' => $autocomplete_provider,
            'autocomplete_api_key' => isset($listing_options['autocomplete_api_key']) ? $listing_options['autocomplete_api_key'] : '',
        ));
    }

    /**
     * Registra todos los shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('ewo_location_form', array($this, 'location_form_shortcode'));
    }

    /**
     * Callback para el shortcode del formulario de ubicación.
     *
     * @since    1.0.0
     * @param    array    $atts    Atributos del shortcode.
     * @return   string            Contenido HTML del formulario de ubicación.
     */
    public function location_form_shortcode($atts) {
        // Procesar atributos
        $atts = shortcode_atts(array(
            'title' => __('Find Services in Your Area', 'ewo-location-services'),
            'button_text' => __('Search', 'ewo-location-services'),
        ), $atts, 'ewo_location_form');

        // Iniciar buffer de salida
        ob_start();

        // Incluir plantilla
        include plugin_dir_path(dirname(__FILE__)) . 'templates/location-form.php';

        // Retornar contenido del buffer
        return ob_get_clean();
    }

    /**
     * Maneja la solicitud AJAX para la búsqueda de ubicación.
     *
     * @since    1.0.0
     */
    public function handle_location_search() {
        // Verificar nonce para seguridad
        check_ajax_referer('ewo_location_services_nonce', 'nonce');

        // Obtener parámetros
        $latitude = isset($_POST['latitude']) ? sanitize_text_field($_POST['latitude']) : '';
        $longitude = isset($_POST['longitude']) ? sanitize_text_field($_POST['longitude']) : '';

        // Validar parámetros
        if (empty($latitude) || empty($longitude)) {
            wp_send_json_error(array('message' => __('Latitude and longitude are required.', 'ewo-location-services')));
        }

        // Registrar la solicitud
        $this->logger->info(sprintf('Location search request: lat=%s, lng=%s', $latitude, $longitude));

        // Obtener opciones y configuración del API
        $options = get_option('ewo_location_services_options');
        $environment = isset($options['api_environment']) ? $options['api_environment'] : 'development';
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';

        // Determinar la URL del endpoint basada en el entorno
        $api_url = '';
        if ($environment === 'development') {
            $api_url = isset($options['dev_get_serviceable_locations_by_latlng_url']) ? $options['dev_get_serviceable_locations_by_latlng_url'] : '';
        } else {
            $api_url = isset($options['prod_get_serviceable_locations_by_latlng_url']) ? $options['prod_get_serviceable_locations_by_latlng_url'] : '';
        }

        // Verificar que tenemos una URL de API
        if (empty($api_url)) {
            $this->logger->error('API URL not configured for environment: ' . $environment);
            wp_send_json_error(array('message' => __('API not properly configured. Please contact the administrator.', 'ewo-location-services')));
        }

        // Preparar los argumentos para la solicitud a la API
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json'
                // Quitar la API key del encabezado
            ),
            'body' => json_encode(array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'requesting_system' => 'website',
                'api-key' => $api_key // Añadir la API key al cuerpo de la solicitud
            )),
            'method' => 'POST',
            'timeout' => 45,
            'data_format' => 'body'
        );

        // Realizar la solicitud a la API
        $response = wp_remote_post($api_url, $args);
        
        // Bandera para indicar si intentamos con GET
        $tried_get_fallback = false;

        // Si falla con método POST o devuelve 404, intentar con GET
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) == 404) {
            $this->logger->info('POST request failed or returned 404, trying GET method as fallback');
            
            // Crear URL con query params para GET
            $get_url = add_query_arg(array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'requesting_system' => 'website',
                'api-key' => $api_key  // Añadir API key en la URL para GET
            ), $api_url);
            
            // Preparar argumentos para GET
            $get_args = array(
                'headers' => array(
                    // Quitar API key del encabezado
                ),
                'method' => 'GET',
                'timeout' => 45
            );
            
            // Realizar solicitud GET
            $backup_response = $response; // Guardar respuesta original para debugging
            $response = wp_remote_get($get_url, $get_args);
            $tried_get_fallback = true;
            
            // Registrar el intento de fallback
            $this->logger->info('Attempted GET fallback to URL: ' . $get_url);
        }

        // Registrar la respuesta
        $this->logger->info('API Response received for location search');

        // Verificar si hubo un error en la solicitud
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $this->logger->error('API Request failed: ' . $error_message);
            wp_send_json_error(array('message' => __('Could not connect to service API. Please try again later.', 'ewo-location-services')));
        }

        // Obtener el cuerpo de la respuesta y decodificar JSON
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Verificar si la respuesta es válida
        if (empty($data) || !is_array($data)) {
            $this->logger->error('Invalid API response format');
            wp_send_json_error(array('message' => __('Received invalid response from API. Please try again later.', 'ewo-location-services')));
        }

        // Procesar la respuesta del endpoint getServiceableLocationsByLatLng
        $processed_data = array();
        
        // Verificar si la estructura esperada está presente
        if (isset($data['serviceableLocations']) && is_array($data['serviceableLocations'])) {
            $processed_data = $data['serviceableLocations'];
            $this->logger->info('Successfully processed serviceableLocations from API response');
        } elseif (isset($data['return-data']['serviceability-info']) && is_array($data['return-data']['serviceability-info'])) {
            $processed_data = $data['return-data']['serviceability-info'];
            $this->logger->info('Found serviceability-info array in return-data');
        } else {
            // Loguear detalles de la estructura recibida para debugging
            $this->logger->warning('Expected serviceableLocations or serviceability-info key not found in API response. Response structure: ' . json_encode(array_keys($data)));
            
            // Verificar si hay mensaje de error en la respuesta
            if (isset($data['errorMessage']) || isset($data['message'])) {
                $error_message = isset($data['errorMessage']) ? $data['errorMessage'] : $data['message'];
                $this->logger->error('API error: ' . $error_message);
                wp_send_json_error(array(
                    'message' => $error_message, 
                    'api_response' => $data,
                    'debug' => $debug_data
                ));
                return;
            }
            
            // Intentar procesar la respuesta con diferentes formatos conocidos
            if (isset($data['services']) && is_array($data['services'])) {
                $processed_data = $data['services'];
                $this->logger->info('Found services array in API response');
            } elseif (isset($data['locations']) && is_array($data['locations'])) {
                $processed_data = $data['locations'];
                $this->logger->info('Found locations array in API response');
            } else {
                // Como último recurso, usar los datos tal como vienen
                $this->logger->warning('Using raw data as fallback for services');
                $processed_data = $data;
            }
        }

        // Añadir información de depuración para administradores
        $debug_data = array(
            'request_url' => $api_url,
            'request_params' => array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'requesting_system' => 'website',
                'api-key' => substr($api_key, 0, 4) . '****' // Mostrar solo primeros 4 caracteres por seguridad
            ),
            'request_headers' => array(
                'Content-Type' => 'application/json'
                // API key ahora va en el cuerpo
            ),
            'response_status' => wp_remote_retrieve_response_code($response),
            'response_headers' => wp_remote_retrieve_headers($response),
            'timestamp' => current_time('mysql'),
            'tried_get_fallback' => $tried_get_fallback
        );
        
        // Si intentamos con GET, incluir esos detalles
        if ($tried_get_fallback) {
            $debug_data['get_url'] = $get_url;
            if (isset($backup_response) && is_wp_error($backup_response)) {
                $debug_data['original_post_error'] = $backup_response->get_error_message();
            } else if (isset($backup_response)) {
                $debug_data['original_post_status'] = wp_remote_retrieve_response_code($backup_response);
            }
        }

        // Añadir datos de depuración a la respuesta
        $response_data = array(
            'services' => $processed_data,
            'raw_response' => $data, // Incluir la respuesta completa para depuración
            'debug' => $debug_data
        );

        // Registrar la respuesta completa para depuración adicional
        $this->logger->debug('Processed API response: ' . json_encode(array_keys($data)));

        // Enviar la respuesta procesada
        wp_send_json_success($response_data);
    }

    /**
     * Maneja la solicitud AJAX para el envío de datos del usuario.
     *
     * @since    1.0.0
     */
    public function handle_user_submission() {
        // Verificar nonce para seguridad
        check_ajax_referer('ewo_location_services_nonce', 'nonce');

        // Obtener parámetros
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
        $this->logger->info(sprintf('User submission request for: %s (%s)', $username, $email));

        // Verificar si el usuario ya existe
        if (username_exists($username)) {
            $this->logger->warning('Username already exists: ' . $username);
            wp_send_json_error(array('message' => __('Username already exists. Please choose another one.', 'ewo-location-services')));
        }

        if (email_exists($email)) {
            $this->logger->warning('Email already exists: ' . $email);
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
            $this->logger->error('Failed to create user: ' . $user_id->get_error_message());
            wp_send_json_error(array('message' => __('Could not create user. Please try again.', 'ewo-location-services')));
        }

        // Registrar la creación exitosa
        $this->logger->info('User created successfully: ' . $user_id);

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
            $this->logger->error('Opportunities API URL not configured for environment: ' . $environment);
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
        $this->logger->info('API Response received for opportunity creation');

        // Verificar si hubo un error en la solicitud
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $this->logger->error('Opportunities API Request failed: ' . $error_message);
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
            $this->logger->error('Invalid Opportunities API response format');
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

    /**
     * Analiza la estructura de una respuesta API y devuelve un resumen
     * Útil para depuración y detección automática de estructuras de datos
     *
     * @param array $data Datos a analizar
     * @param int $depth Profundidad de recursión actual
     * @param int $max_depth Profundidad máxima para buscar
     * @return array Información sobre la estructura
     */
    private function analyze_response_structure($data, $depth = 0, $max_depth = 3) {
        $result = array(
            'type' => gettype($data),
            'keys' => array(),
            'has_array' => false,
            'array_length' => 0,
            'potential_services' => false
        );
        
        // Si ya alcanzamos la profundidad máxima, detenerse
        if ($depth >= $max_depth) {
            return $result;
        }
        
        if (is_array($data) || is_object($data)) {
            // Si es un array, analizar sus elementos
            if (is_array($data)) {
                $result['array_length'] = count($data);
                $result['has_array'] = true;
                
                // Verificar si podría contener servicios (si al menos el primer elemento tiene propiedades típicas)
                if (count($data) > 0 && isset($data[0])) {
                    $first_item = $data[0];
                    if (is_array($first_item) || is_object($first_item)) {
                        $first_item_arr = (array) $first_item;
                        // Verificar si tiene propiedades típicas de un servicio
                        $service_props = array('id', 'name', 'price', 'description', 'type', 'status');
                        $matches = 0;
                        foreach ($service_props as $prop) {
                            if (isset($first_item_arr[$prop])) {
                                $matches++;
                            }
                        }
                        // Si tiene al menos 2 propiedades típicas, probablemente es un servicio
                        $result['potential_services'] = ($matches >= 2);
                    }
                }
            }
            
            // Convertir a array si es objeto
            $data_array = (array) $data;
            
            // Almacenar las claves
            $result['keys'] = array_keys($data_array);
            
            // Analizar estructura de las subpropiedades (hasta 5 para no sobrecargar)
            $children = array();
            $i = 0;
            foreach ($data_array as $key => $value) {
                if ($i++ >= 5) break; // Limitar a 5 propiedades para no hacer el análisis demasiado pesado
                $children[$key] = $this->analyze_response_structure($value, $depth + 1, $max_depth);
            }
            $result['children'] = $children;
        }
        
        return $result;
    }
}
