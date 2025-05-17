<?php
/**
 * La funcionalidad específica de administración del plugin.
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

class Ewo_Location_Services_Admin {

    /**
     * El ID de este plugin.
     *
     * @since    1.0.0
     * @access   private
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
     * Registra los estilos para el área de administración.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ewo-location-services-admin.css', array(), $this->version, 'all');
    }

    /**
     * Registra los scripts para el área de administración.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ewo-location-services-admin.js', array('jquery'), $this->version, false);
        
        // Registrar script específico para la página de prueba API
        $screen = get_current_screen();
        if ($screen && $screen->id === 'location-services_page_ewo-location-services-api-tester') {
            // Incluir la biblioteca Leaflet para el mapa
            wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), '1.7.1');
            wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', false);
            
            // Incluir nuestro script del API Tester
            wp_enqueue_script($this->plugin_name . '-api-tester', plugin_dir_url(__FILE__) . 'js/ewo-location-services-api-tester.js', array('jquery', 'leaflet-js'), $this->version, false);
            
            // Pasar variables al script
            wp_localize_script($this->plugin_name . '-api-tester', 'ewoApiTester', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ewo_api_tester_nonce')
            ));
        }
    }

    /**
     * Agrega opciones de menú del plugin en el área de administración.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Menú principal
        add_menu_page(
            __('Ewo Location Services', 'ewo-location-services'),
            __('Location Services', 'ewo-location-services'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page'),
            'dashicons-location',
            56
        );

        // Submenú para configuración
        add_submenu_page(
            $this->plugin_name,
            __('Settings', 'ewo-location-services'),
            __('Settings', 'ewo-location-services'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page')
        );

        // Submenú para logs
        add_submenu_page(
            $this->plugin_name,
            __('Logs', 'ewo-location-services'),
            __('Logs', 'ewo-location-services'),
            'manage_options',
            $this->plugin_name . '-logs',
            array($this, 'display_plugin_logs_page')
        );
        
        // Submenú para el modo de prueba API
        add_submenu_page(
            $this->plugin_name,
            __('API Tester', 'ewo-location-services'),
            __('API Tester', 'ewo-location-services'),
            'manage_options',
            $this->plugin_name . '-api-tester',
            array($this, 'display_plugin_api_tester_page')
        );
        
        // Submenú para el modo de prueba de Autenticación API
        add_submenu_page(
            $this->plugin_name,
            __('API Auth Tester', 'ewo-location-services'),
            __('API Auth Tester', 'ewo-location-services'),
            'manage_options',
            $this->plugin_name . '-api-auth-tester',
            array($this, 'display_plugin_api_auth_tester_page')
        );
    }

    /**
     * Renderiza la página de configuración.
     *
     * @since    1.0.0
     */
    public function display_plugin_setup_page() {
        include_once 'partials/ewo-location-services-admin-display.php';
    }

    /**
     * Renderiza la página de logs.
     *
     * @since    1.0.0
     */
    public function display_plugin_logs_page() {
        include_once 'partials/ewo-location-services-admin-logs.php';
    }
    
    /**
     * Renderiza la página del modo de prueba API.
     *
     * @since    1.0.0
     */
    public function display_plugin_api_tester_page() {
        include_once 'partials/ewo-location-services-admin-api-tester.php';
    }
    
    /**
     * Renderiza la página de prueba de autenticación API.
     *
     * @since    1.0.0
     */
    public function display_plugin_api_auth_tester_page() {
        include_once 'partials/ewo-location-services-admin-api-auth-tester.php';
    }

    /**
     * Registra todas las opciones/configuraciones para el plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Registrar la configuración
        register_setting(
            'ewo_location_services_options', 
            'ewo_location_services_options'
        );

        // Sección API General
        add_settings_section(
            'ewo_location_services_general',
            __('API Settings', 'ewo-location-services'),
            array($this, 'settings_section_general_cb'),
            'ewo_location_services_options'
        );

        // Selección de entorno
        add_settings_field(
            'api_environment',
            __('API Environment', 'ewo-location-services'),
            array($this, 'settings_field_api_environment_cb'),
            'ewo_location_services_options',
            'ewo_location_services_general'
        );

        // API Key
        add_settings_field(
            'api_key',
            __('API Key', 'ewo-location-services'),
            array($this, 'settings_field_api_key_cb'),
            'ewo_location_services_options',
            'ewo_location_services_general'
        );

        // Sección Wisper Serviceability Endpoints
        add_settings_section(
            'ewo_location_services_serviceability',
            __('Serviceability Endpoints', 'ewo-location-services'),
            array($this, 'settings_section_serviceability_cb'),
            'ewo_location_services_options'
        );

        // URL getServiceabilityDetails (Desarrollo)
        add_settings_field(
            'dev_get_serviceability_details_url',
            __('Development getServiceabilityDetails URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_get_serviceability_details_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_serviceability'
        );

        // URL getServiceabilityDetails (Producción)
        add_settings_field(
            'prod_get_serviceability_details_url',
            __('Production getServiceabilityDetails URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_get_serviceability_details_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_serviceability'
        );

        // URL getServiceableLocationsByLatLng (Desarrollo)
        add_settings_field(
            'dev_get_serviceable_locations_by_latlng_url',
            __('Development getServiceableLocationsByLatLng URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_get_serviceable_locations_by_latlng_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_serviceability'
        );

        // URL getServiceableLocationsByLatLng (Producción)
        add_settings_field(
            'prod_get_serviceable_locations_by_latlng_url',
            __('Production getServiceableLocationsByLatLng URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_get_serviceable_locations_by_latlng_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_serviceability'
        );

        // Sección Package Endpoints
        add_settings_section(
            'ewo_location_services_packages',
            __('Package Endpoints', 'ewo-location-services'),
            array($this, 'settings_section_packages_cb'),
            'ewo_location_services_options'
        );

        // URL getPackages (Desarrollo)
        add_settings_field(
            'dev_get_packages_url',
            __('Development getPackages URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_get_packages_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_packages'
        );

        // URL getPackages (Producción)
        add_settings_field(
            'prod_get_packages_url',
            __('Production getPackages URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_get_packages_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_packages'
        );

        // URL getDiscountTemplates (Desarrollo)
        add_settings_field(
            'dev_get_discount_templates_url',
            __('Development getDiscountTemplates URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_get_discount_templates_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_packages'
        );

        // URL getDiscountTemplates (Producción)
        add_settings_field(
            'prod_get_discount_templates_url',
            __('Production getDiscountTemplates URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_get_discount_templates_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_packages'
        );

        // URL addPackageToParent (Desarrollo)
        add_settings_field(
            'dev_add_package_to_parent_url',
            __('Development addPackageToParent URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_add_package_to_parent_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_packages'
        );

        // URL addPackageToParent (Producción)
        add_settings_field(
            'prod_add_package_to_parent_url',
            __('Production addPackageToParent URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_add_package_to_parent_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_packages'
        );

        // Sección Customer Endpoints
        add_settings_section(
            'ewo_location_services_customers',
            __('Customer Endpoints', 'ewo-location-services'),
            array($this, 'settings_section_customers_cb'),
            'ewo_location_services_options'
        );

        // URL createCustomer (Desarrollo)
        add_settings_field(
            'dev_create_customer_url',
            __('Development createCustomer URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_create_customer_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_customers'
        );

        // URL createCustomer (Producción)
        add_settings_field(
            'prod_create_customer_url',
            __('Production createCustomer URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_create_customer_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_customers'
        );

        // URL updateCustomer (Desarrollo)
        add_settings_field(
            'dev_update_customer_url',
            __('Development updateCustomer URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_update_customer_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_customers'
        );

        // URL updateCustomer (Producción)
        add_settings_field(
            'prod_update_customer_url',
            __('Production updateCustomer URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_update_customer_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_customers'
        );

        // URL createCustomerComment (Desarrollo)
        add_settings_field(
            'dev_create_customer_comment_url',
            __('Development createCustomerComment URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_create_customer_comment_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_customers'
        );

        // URL createCustomerComment (Producción)
        add_settings_field(
            'prod_create_customer_comment_url',
            __('Production createCustomerComment URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_create_customer_comment_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_customers'
        );

        // Sección Opportunity Endpoints
        add_settings_section(
            'ewo_location_services_opportunity',
            __('Opportunity Endpoints', 'ewo-location-services'),
            array($this, 'settings_section_opportunity_cb'),
            'ewo_location_services_options'
        );

        // URL createOpportunity (Desarrollo)
        add_settings_field(
            'dev_create_opportunity_url',
            __('Development createOpportunity URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_create_opportunity_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_opportunity'
        );

        // URL createOpportunity (Producción)
        add_settings_field(
            'prod_create_opportunity_url',
            __('Production createOpportunity URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_create_opportunity_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_opportunity'
        );

        // URL updateOpportunity (Desarrollo)
        add_settings_field(
            'dev_update_opportunity_url',
            __('Development updateOpportunity URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_update_opportunity_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_opportunity'
        );

        // URL updateOpportunity (Producción)
        add_settings_field(
            'prod_update_opportunity_url',
            __('Production updateOpportunity URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_update_opportunity_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_opportunity'
        );

        // URL getOpportunity (Desarrollo)
        add_settings_field(
            'dev_get_opportunity_url',
            __('Development getOpportunity URL', 'ewo-location-services'),
            array($this, 'settings_field_dev_get_opportunity_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_opportunity'
        );

        // URL getOpportunity (Producción)
        add_settings_field(
            'prod_get_opportunity_url',
            __('Production getOpportunity URL', 'ewo-location-services'),
            array($this, 'settings_field_prod_get_opportunity_url_cb'),
            'ewo_location_services_options',
            'ewo_location_services_opportunity'
        );
    }

    /**
     * Callback para la sección de configuración general.
     *
     * @since    1.0.0
     */
    public function settings_section_general_cb() {
        echo '<p>' . __('Configure the API settings for service lookup and user registration.', 'ewo-location-services') . '</p>';
    }

    /**
     * Callback para el campo de entorno de API.
     *
     * @since    1.0.0
     */
    public function settings_field_api_environment_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['api_environment']) ? $options['api_environment'] : 'development';
        ?>
        <select name="ewo_location_services_options[api_environment]" id="api_environment">
            <option value="development" <?php selected($value, 'development'); ?>><?php _e('Development', 'ewo-location-services'); ?></option>
            <option value="production" <?php selected($value, 'production'); ?>><?php _e('Production', 'ewo-location-services'); ?></option>
        </select>
        <p class="description"><?php _e('Select which environment to use for API calls.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo de API Key.
     *
     * @since    1.0.0
     */
    public function settings_field_api_key_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['api_key']) ? $options['api_key'] : '';
        ?>
        <input type="text" class="regular-text" name="ewo_location_services_options[api_key]" id="api_key" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('Enter your API key for authentication.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Este comentario reemplaza los métodos eliminados para los campos redundantes.
     * Los campos de URLs generales (Service Lookup y Opportunities) fueron eliminados
     * ya que son reemplazados por los campos específicos en las secciones de Serviceability,
     * Packages, Customers y Opportunity.
     */

    /**
     * Callback para la sección de configuración de Serviceability.
     *
     * @since    1.0.0
     */
    public function settings_section_serviceability_cb() {
        echo '<p>' . __('Configure the Serviceability endpoints for the Wisper Serviceability Tool.', 'ewo-location-services') . '</p>';
    }

    /**
     * Callback para el campo getServiceabilityDetails (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_get_serviceability_details_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_get_serviceability_details_url']) ? $options['dev_get_serviceability_details_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_get_serviceability_details_url]" id="dev_get_serviceability_details_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getServiceabilityDetails endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo getServiceabilityDetails (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_get_serviceability_details_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_get_serviceability_details_url']) ? $options['prod_get_serviceability_details_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_get_serviceability_details_url]" id="prod_get_serviceability_details_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getServiceabilityDetails endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo getServiceableLocationsByLatLng (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_get_serviceable_locations_by_latlng_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_get_serviceable_locations_by_latlng_url']) ? $options['dev_get_serviceable_locations_by_latlng_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_get_serviceable_locations_by_latlng_url]" id="dev_get_serviceable_locations_by_latlng_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getServiceableLocationsByLatLng endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo getServiceableLocationsByLatLng (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_get_serviceable_locations_by_latlng_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_get_serviceable_locations_by_latlng_url']) ? $options['prod_get_serviceable_locations_by_latlng_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_get_serviceable_locations_by_latlng_url]" id="prod_get_serviceable_locations_by_latlng_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getServiceableLocationsByLatLng endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para la sección de configuración de paquetes.
     *
     * @since    1.0.0
     */
    public function settings_section_packages_cb() {
        echo '<p>' . __('Configure the Package endpoints for the Wisper Serviceability Tool.', 'ewo-location-services') . '</p>';
    }

    /**
     * Callback para el campo getPackages (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_get_packages_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_get_packages_url']) ? $options['dev_get_packages_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_get_packages_url]" id="dev_get_packages_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getPackages endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo getPackages (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_get_packages_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_get_packages_url']) ? $options['prod_get_packages_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_get_packages_url]" id="prod_get_packages_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getPackages endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo getDiscountTemplates (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_get_discount_templates_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_get_discount_templates_url']) ? $options['dev_get_discount_templates_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_get_discount_templates_url]" id="dev_get_discount_templates_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getDiscountTemplates endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo getDiscountTemplates (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_get_discount_templates_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_get_discount_templates_url']) ? $options['prod_get_discount_templates_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_get_discount_templates_url]" id="prod_get_discount_templates_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getDiscountTemplates endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo addPackageToParent (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_add_package_to_parent_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_add_package_to_parent_url']) ? $options['dev_add_package_to_parent_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_add_package_to_parent_url]" id="dev_add_package_to_parent_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for addPackageToParent endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo addPackageToParent (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_add_package_to_parent_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_add_package_to_parent_url']) ? $options['prod_add_package_to_parent_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_add_package_to_parent_url]" id="prod_add_package_to_parent_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for addPackageToParent endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para la sección de Customers.
     *
     * @since    1.0.0
     */
    public function settings_section_customers_cb() {
        echo '<p>' . __('Configure the Customer endpoints for the Wisper Serviceability Tool.', 'ewo-location-services') . '</p>';
    }

    /**
     * Callback para el campo createCustomer (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_create_customer_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_create_customer_url']) ? $options['dev_create_customer_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_create_customer_url]" id="dev_create_customer_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for createCustomer endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo createCustomer (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_create_customer_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_create_customer_url']) ? $options['prod_create_customer_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_create_customer_url]" id="prod_create_customer_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for createCustomer endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo updateCustomer (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_update_customer_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_update_customer_url']) ? $options['dev_update_customer_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_update_customer_url]" id="dev_update_customer_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for updateCustomer endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo updateCustomer (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_update_customer_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_update_customer_url']) ? $options['prod_update_customer_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_update_customer_url]" id="prod_update_customer_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for updateCustomer endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo createCustomerComment (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_create_customer_comment_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_create_customer_comment_url']) ? $options['dev_create_customer_comment_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_create_customer_comment_url]" id="dev_create_customer_comment_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for createCustomerComment endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo createCustomerComment (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_create_customer_comment_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_create_customer_comment_url']) ? $options['prod_create_customer_comment_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_create_customer_comment_url]" id="prod_create_customer_comment_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for createCustomerComment endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para la sección de Opportunity.
     *
     * @since    1.0.0
     */
    public function settings_section_opportunity_cb() {
        echo '<p>' . __('Configure the Opportunity endpoints for the Wisper Serviceability Tool.', 'ewo-location-services') . '</p>';
    }

    /**
     * Callback para el campo createOpportunity (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_create_opportunity_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_create_opportunity_url']) ? $options['dev_create_opportunity_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_create_opportunity_url]" id="dev_create_opportunity_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for createOpportunity endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo createOpportunity (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_create_opportunity_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_create_opportunity_url']) ? $options['prod_create_opportunity_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_create_opportunity_url]" id="prod_create_opportunity_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for createOpportunity endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo updateOpportunity (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_update_opportunity_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_update_opportunity_url']) ? $options['dev_update_opportunity_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_update_opportunity_url]" id="dev_update_opportunity_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for updateOpportunity endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo updateOpportunity (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_update_opportunity_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_update_opportunity_url']) ? $options['prod_update_opportunity_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_update_opportunity_url]" id="prod_update_opportunity_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for updateOpportunity endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo getOpportunity (Desarrollo).
     *
     * @since    1.0.0
     */
    public function settings_field_dev_get_opportunity_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['dev_get_opportunity_url']) ? $options['dev_get_opportunity_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[dev_get_opportunity_url]" id="dev_get_opportunity_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getOpportunity endpoint in development environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Callback para el campo getOpportunity (Producción).
     *
     * @since    1.0.0
     */
    public function settings_field_prod_get_opportunity_url_cb() {
        $options = get_option('ewo_location_services_options');
        $value = isset($options['prod_get_opportunity_url']) ? $options['prod_get_opportunity_url'] : '';
        ?>
        <input type="url" class="regular-text" name="ewo_location_services_options[prod_get_opportunity_url]" id="prod_get_opportunity_url" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php _e('URL for getOpportunity endpoint in production environment.', 'ewo-location-services'); ?></p>
        <?php
    }

    /**
     * Procesa la acción de descarga de archivo de log.
     *
     * @since    1.0.0
     */
    public function handle_log_download() {
        if (isset($_GET['action']) && $_GET['action'] === 'download_log' && isset($_GET['file']) && current_user_can('manage_options')) {
            $file = sanitize_text_field($_GET['file']);
            $log_dir = EWO_LOCATION_SERVICES_LOGS_DIR;
            $file_path = $log_dir . $file;
            
            if (file_exists($file_path) && is_file($file_path)) {
                header('Content-Description: File Transfer');
                header('Content-Type: text/plain');
                header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                exit;
            }
        }
    }

    /**
     * Procesa la acción de limpieza de archivo de log.
     *
     * @since    1.0.0
     */
    public function handle_log_clear() {
        if (isset($_POST['action']) && $_POST['action'] === 'clear_log' && isset($_POST['log_file']) && current_user_can('manage_options')) {
            check_admin_referer('ewo_clear_log', 'ewo_clear_log_nonce');
            
            $file = sanitize_text_field($_POST['log_file']);
            $log_dir = EWO_LOCATION_SERVICES_LOGS_DIR;
            $file_path = $log_dir . $file;
            
            if (file_exists($file_path) && is_file($file_path)) {
                file_put_contents($file_path, '');
                wp_redirect(admin_url('admin.php?page=' . $this->plugin_name . '-logs&cleared=1'));
                exit;
            }
        }
    }
    
    /**
     * Maneja la solicitud AJAX para la prueba de API.
     *
     * @since    1.0.0
     */
    public function handle_api_test() {
        // Verificar nonce para seguridad
        check_ajax_referer('ewo_api_tester_nonce', 'nonce');
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have sufficient permissions.', 'ewo-location-services')));
        }
        
        // Obtener parámetros
        $latitude = isset($_POST['latitude']) ? sanitize_text_field($_POST['latitude']) : '';
        $longitude = isset($_POST['longitude']) ? sanitize_text_field($_POST['longitude']) : '';
        $endpoint_type = isset($_POST['endpoint_type']) ? sanitize_text_field($_POST['endpoint_type']) : '';
        $environment = isset($_POST['environment']) ? sanitize_text_field($_POST['environment']) : 'development';

        // Validar parámetros
        if (empty($latitude) || empty($longitude) || empty($endpoint_type)) {
            wp_send_json_error(array('message' => __('All fields are required.', 'ewo-location-services')));
        }

        // Registrar la solicitud
        $this->logger->info(sprintf('API Test request: endpoint=%s, environment=%s, lat=%s, lng=%s', 
            $endpoint_type, $environment, $latitude, $longitude));

        // Obtener opciones y configuración del API
        $options = get_option('ewo_location_services_options');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        
        // Determinar la URL del endpoint basada en el entorno y el tipo
        $api_url = '';
        $endpoint_option = '';
        
        if ($endpoint_type === 'getServiceableLocationsByLatLng') {
            $endpoint_option = $environment === 'development' ? 'dev_get_serviceable_locations_by_latlng_url' : 'prod_get_serviceable_locations_by_latlng_url';
        } elseif ($endpoint_type === 'getServiceabilityDetails') {
            $endpoint_option = $environment === 'development' ? 'dev_get_serviceability_details_url' : 'prod_get_serviceability_details_url';
        } else {
            wp_send_json_error(array('message' => __('Invalid endpoint selected.', 'ewo-location-services')));
        }
        
        $api_url = isset($options[$endpoint_option]) ? $options[$endpoint_option] : '';
        
        // Verificar que tenemos una URL de API
        if (empty($api_url)) {
            $this->logger->error('API URL not configured for endpoint: ' . $endpoint_type . ' in environment: ' . $environment);
            wp_send_json_error(array('message' => __('API endpoint URL not configured.', 'ewo-location-services')));
        }

        // Preparar los argumentos para la solicitud a la API
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'requesting_system' => 'website',
                'api-key' => $api_key  // Formato correcto con guion
            )),
            'method' => 'POST',
            'timeout' => 45,
            'data_format' => 'body'
        );

        // Realizar la solicitud a la API
        $response = wp_remote_post($api_url, $args);

        // Registrar la respuesta
        $this->logger->info('API Test Response received for endpoint: ' . $endpoint_type);

        // Verificar si hubo un error en la solicitud
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $this->logger->error('API Test Request failed: ' . $error_message);
            wp_send_json_error(array('message' => __('Could not connect to service API: ', 'ewo-location-services') . $error_message));
        }

        // Obtener el código de respuesta HTTP
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Obtener el cuerpo de la respuesta y decodificar JSON
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Añadir información de depuración para administradores
        $debug_data = array(
            'request_url' => $api_url,
            'request_params' => array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'requesting_system' => 'website',
                'api-key' => substr($api_key, 0, 4) . '****' // Mostrar solo primeros 4 caracteres por seguridad
            ),
            'response_status' => $response_code,
            'response_headers' => wp_remote_retrieve_headers($response),
            'timestamp' => current_time('mysql')
        );

        // Verificar si la respuesta es válida
        if (empty($data) && $response_code !== 204) { // 204 = No Content, podría ser válido en algunos casos
            $this->logger->error('Invalid API response format for endpoint: ' . $endpoint_type);
            wp_send_json_error(array(
                'message' => __('Received invalid response from API.', 'ewo-location-services'),
                'raw_response' => $body,
                'debug' => $debug_data
            ));
        }

        // Registrar la respuesta completa para depuración adicional
        $this->logger->debug('API Test successful for endpoint: ' . $endpoint_type);

        // Enviar la respuesta procesada
        wp_send_json_success(array(
            'message' => sprintf(__('API test successful. Response code: %d', 'ewo-location-services'), $response_code),
            'raw_response' => $data,
            'debug' => $debug_data
        ));
    }
}
