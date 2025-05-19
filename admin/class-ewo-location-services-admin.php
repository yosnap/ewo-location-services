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

        // Add Service Listing Settings submenu
        add_submenu_page(
            $this->plugin_name,
            __('Service Listing Settings', 'ewo-location-services'),
            __('Service Listing Settings', 'ewo-location-services'),
            'manage_options',
            $this->plugin_name . '-service-listing-settings',
            array($this, 'display_service_listing_settings_page')
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

    public function display_service_listing_settings_page() {
        ?>
        <div class="wrap">
            <h1>Service Listing Settings</h1>
            <div class="ewo-tabs" style="margin-bottom: 2rem;">
                <button class="ewo-tab-btn active" data-tab="display">Service Listing Display</button>
                <button class="ewo-tab-btn" data-tab="steps">Form Steps Customization</button>
                <button class="ewo-tab-btn" data-tab="autocomplete">Map & Autocomplete Settings</button>
            </div>
            <form method="post" action="options.php">
                <?php
                settings_fields('ewo_service_listing_options_group');
                echo '<div id="ewo-tab-content-display" class="ewo-tab-content active">';
                do_settings_sections('ewo_service_listing_options');
                echo '</div>';
                echo '<div id="ewo-tab-content-steps" class="ewo-tab-content">';
                do_settings_sections('ewo_form_steps_customization');
                echo '</div>';
                echo '<div id="ewo-tab-content-autocomplete" class="ewo-tab-content">';
                do_settings_sections('ewo_autocomplete_settings');
                echo '</div>';
                submit_button();
                ?>
            </form>
        </div>
        <style>
        .ewo-tabs { display: flex; gap: 1rem; }
        .ewo-tab-btn { background: #f5f5f5; border: none; border-radius: 6px 6px 0 0; padding: 0.7em 2em; font-size: 1.1rem; cursor: pointer; color: #555; transition: background 0.2s, color 0.2s; }
        .ewo-tab-btn.active { background: #fff; color: #c2185b; border-bottom: 2px solid #c2185b; font-weight: bold; }
        .ewo-tab-content { display: none; background: #fff; border-radius: 0 0 8px 8px; padding: 2rem 2rem 1rem 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .ewo-tab-content.active { display: block; }
        </style>
        <script>
        jQuery(function($){
            // --- PERSISTENCIA DE PESTAÑA ACTIVA ---
            function setActiveTab(tab) {
                $('.ewo-tab-btn').removeClass('active');
                $('.ewo-tab-content').removeClass('active');
                if(tab === 'steps') {
                    $('.ewo-tab-btn[data-tab="steps"]').addClass('active');
                    $('#ewo-tab-content-steps').addClass('active');
                } else if(tab === 'autocomplete') {
                    $('.ewo-tab-btn[data-tab="autocomplete"]').addClass('active');
                    $('#ewo-tab-content-autocomplete').addClass('active');
                } else {
                    $('.ewo-tab-btn[data-tab="display"]').addClass('active');
                    $('#ewo-tab-content-display').addClass('active');
                }
                localStorage.setItem('ewo_service_listing_tab', tab);
                // Al cambiar de pestaña, volver a evaluar los campos condicionales y validación
                toggleApiKeyFields();
                setTimeout(validateApiKeys, 100);
            }
            $('.ewo-tab-btn').on('click', function(){
                setActiveTab($(this).data('tab'));
            });
            var lastTab = localStorage.getItem('ewo_service_listing_tab') || 'display';
            setActiveTab(lastTab);

            // --- CAMPOS CONDICIONALES Y VALIDACIÓN ---
            function toggleApiKeyFields() {
                var mapProvider = $('#ewo_map_provider').val();
                var autocompleteProvider = $('#ewo_autocomplete_provider').val();
                // Google Maps API Key
                var googleMapsRow = $('#ewo_map_google_api_key_row').closest('tr');
                if (mapProvider === 'google') {
                    googleMapsRow.show();
                } else {
                    googleMapsRow.hide();
                }
                // Google Autocomplete API Key
                var googleAutoRow = $('#ewo_google_autocomplete_api_key_row').closest('tr');
                if (autocompleteProvider === 'google') {
                    googleAutoRow.show();
                } else {
                    googleAutoRow.hide();
                }
                // Mapbox Autocomplete API Key
                var mapboxAutoRow = $('#ewo_mapbox_autocomplete_api_key_row').closest('tr');
                if (autocompleteProvider === 'mapbox') {
                    mapboxAutoRow.show();
                } else {
                    mapboxAutoRow.hide();
                }
            }
            function validateApiKeys() {
                var mapProvider = $('#ewo_map_provider').val();
                var autocompleteProvider = $('#ewo_autocomplete_provider').val();
                var mapGoogleKey = $('input[name="ewo_service_listing_options[map_google_api_key]"]').val();
                var googleAutoKey = $('input[name="ewo_service_listing_options[google_autocomplete_api_key]"]').val();
                var mapboxAutoKey = $('input[name="ewo_service_listing_options[mapbox_autocomplete_api_key]"]').val();
                var errorMsg = '';
                if ($('#ewo_map_google_api_key_row').is(':visible') && !mapGoogleKey) {
                    errorMsg += '<div class="notice notice-error"><p>Google Maps API Key is required for Google Maps provider.</p></div>';
                }
                if ($('#ewo_google_autocomplete_api_key_row').is(':visible') && !googleAutoKey) {
                    errorMsg += '<div class="notice notice-error"><p>Google Autocomplete API Key is required for Google Places Autocomplete.</p></div>';
                }
                if ($('#ewo_mapbox_autocomplete_api_key_row').is(':visible') && !mapboxAutoKey) {
                    errorMsg += '<div class="notice notice-error"><p>Mapbox Autocomplete API Key is required for Mapbox Autocomplete.</p></div>';
                }
                $('#ewo-api-key-errors').remove();
                if (errorMsg) {
                    $('.ewo-tab-content.active form, form.ewo-settings-form').before('<div id="ewo-api-key-errors">'+errorMsg+'</div>');
                    $('input[type="submit"], button[type="submit"]').prop('disabled', true);
                } else {
                    $('#ewo-api-key-errors').remove();
                    $('input[type="submit"], button[type="submit"]').prop('disabled', false);
                }
            }
            $('#ewo_map_provider, #ewo_autocomplete_provider').on('change', function(){
                toggleApiKeyFields();
                setTimeout(validateApiKeys, 100);
            });
            $('input[name^="ewo_service_listing_options["]').on('input', function(){
                setTimeout(validateApiKeys, 100);
            });
            // Inicialización al cargar
            toggleApiKeyFields();
            setTimeout(validateApiKeys, 100);
        });
        </script>
        <?php
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

        // Registrar la configuración para Service Listing Settings
        register_setting('ewo_service_listing_options_group', 'ewo_service_listing_options');

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

        // Sección principal de opciones de listado
        add_settings_section(
            'ewo_service_listing_main',
            __('Service Listing Display Options', 'ewo-location-services'),
            function() {
                echo '<p>Configure how the available services are displayed on the frontend.</p>';
            },
            'ewo_service_listing_options'
        );
        // Default mode
        add_settings_field(
            'listing_mode',
            __('Default Listing Mode', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['listing_mode']) ? $opts['listing_mode'] : 'grid';
                echo '<select name="ewo_service_listing_options[listing_mode]">';
                echo '<option value="grid"' . selected($val, 'grid', false) . '>Grid</option>';
                echo '<option value="list"' . selected($val, 'list', false) . '>List</option>';
                echo '</select>';
            },
            'ewo_service_listing_options',
            'ewo_service_listing_main'
        );
        // Grid columns
        add_settings_field(
            'grid_columns',
            __('Grid Columns', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['grid_columns']) ? intval($opts['grid_columns']) : 3;
                echo '<select name="ewo_service_listing_options[grid_columns]">';
                for ($i = 2; $i <= 4; $i++) {
                    echo '<option value="' . $i . '"' . selected($val, $i, false) . '>' . $i . '</option>';
                }
                echo '</select>';
            },
            'ewo_service_listing_options',
            'ewo_service_listing_main'
        );
        // Show filters
        add_settings_field(
            'show_filters',
            __('Show Filters', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['show_filters']) ? $opts['show_filters'] : 'yes';
                echo '<select name="ewo_service_listing_options[show_filters]">';
                echo '<option value="yes"' . selected($val, 'yes', false) . '>Yes</option>';
                echo '<option value="no"' . selected($val, 'no', false) . '>No</option>';
                echo '</select>';
            },
            'ewo_service_listing_options',
            'ewo_service_listing_main'
        );
        // Show pagination
        add_settings_field(
            'show_pagination',
            __('Show Pagination', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['show_pagination']) ? $opts['show_pagination'] : 'yes';
                echo '<select name="ewo_service_listing_options[show_pagination]">';
                echo '<option value="yes"' . selected($val, 'yes', false) . '>Yes</option>';
                echo '<option value="no"' . selected($val, 'no', false) . '>No</option>';
                echo '</select>';
            },
            'ewo_service_listing_options',
            'ewo_service_listing_main'
        );
        // Items per page
        add_settings_field(
            'items_per_page',
            __('Items Per Page', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['items_per_page']) ? intval($opts['items_per_page']) : 8;
                echo '<input type="number" min="1" max="50" name="ewo_service_listing_options[items_per_page]" value="' . esc_attr($val) . '" style="width:60px;">';
            },
            'ewo_service_listing_options',
            'ewo_service_listing_main'
        );
        // Load More button
        add_settings_field(
            'load_more',
            __('Use "Load More" Button', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['load_more']) ? $opts['load_more'] : 'no';
                echo '<select name="ewo_service_listing_options[load_more]">';
                echo '<option value="yes"' . selected($val, 'yes', false) . '>Yes</option>';
                echo '<option value="no"' . selected($val, 'no', false) . '>No</option>';
                echo '</select>';
            },
            'ewo_service_listing_options',
            'ewo_service_listing_main'
        );
        // Card color usage
        add_settings_field(
            'card_color_usage',
            __('Use status_color_hex for', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['card_color_usage']) ? $opts['card_color_usage'] : 'none';
                echo '<select name="ewo_service_listing_options[card_color_usage]">';
                echo '<option value="none"' . selected($val, 'none', false) . '>None</option>';
                echo '<option value="border"' . selected($val, 'border', false) . '>Card Border</option>';
                echo '<option value="background"' . selected($val, 'background', false) . '>Card Background</option>';
                echo '</select>';
            },
            'ewo_service_listing_options',
            'ewo_service_listing_main'
        );
        // Form Steps Style (mover aquí, antes de los demás campos de personalización)
        $opts = get_option('ewo_service_listing_options');
        add_settings_field(
            'form_steps_style',
            __('Form Steps Style', 'ewo-location-services'),
            function() use ($opts) {
                $value = isset($opts['form_steps_style']) ? $opts['form_steps_style'] : 'progress_bar';
                echo '<select id="ewo-form-steps-style" name="ewo_service_listing_options[form_steps_style]">';
                echo '<option value="progress_bar"' . selected($value, 'progress_bar', false) . '>Progress Bar</option>';
                echo '<option value="circles"' . selected($value, 'circles', false) . '>Circles</option>';
                echo '<option value="none"' . selected($value, 'none', false) . '>None</option>';
                echo '</select>';
            },
            'ewo_form_steps_customization',
            'ewo_form_steps_customization'
        );
        // Active Step Color
        add_settings_field(
            'step_active_color',
            __('Active Step Color', 'ewo-location-services'),
            function() use ($opts) {
                $val = isset($opts['step_active_color']) ? $opts['step_active_color'] : '#c2185b';
                echo '<input type="color" name="ewo_service_listing_options[step_active_color]" value="' . esc_attr($val) . '">';
            },
            'ewo_form_steps_customization',
            'ewo_form_steps_customization'
        );
        // Inactive Step Color
        add_settings_field(
            'step_inactive_color',
            __('Inactive Step Color', 'ewo-location-services'),
            function() use ($opts) {
                $val = isset($opts['step_inactive_color']) ? $opts['step_inactive_color'] : '#bbb';
                echo '<input type="color" name="ewo_service_listing_options[step_inactive_color]" value="' . esc_attr($val) . '">';
            },
            'ewo_form_steps_customization',
            'ewo_form_steps_customization'
        );
        // Active Step Background
        add_settings_field(
            'step_active_bg',
            __('Active Step Background', 'ewo-location-services'),
            function() use ($opts) {
                $val = isset($opts['step_active_bg']) ? $opts['step_active_bg'] : '#ffe3ef';
                echo '<input type="color" name="ewo_service_listing_options[step_active_bg]" value="' . esc_attr($val) . '">';
            },
            'ewo_form_steps_customization',
            'ewo_form_steps_customization'
        );
        // Inactive Step Background
        add_settings_field(
            'step_inactive_bg',
            __('Inactive Step Background', 'ewo-location-services'),
            function() use ($opts) {
                $val = isset($opts['step_inactive_bg']) ? $opts['step_inactive_bg'] : '#eee';
                echo '<input type="color" name="ewo_service_listing_options[step_inactive_bg]" value="' . esc_attr($val) . '">';
            },
            'ewo_form_steps_customization',
            'ewo_form_steps_customization'
        );
        // Step Size
        add_settings_field(
            'step_size',
            __('Step Size (px)', 'ewo-location-services'),
            function() use ($opts) {
                $val = isset($opts['step_size']) ? intval($opts['step_size']) : 32;
                echo '<input type="number" min="20" max="80" name="ewo_service_listing_options[step_size]" value="' . esc_attr($val) . '" style="width:60px;">';
            },
            'ewo_form_steps_customization',
            'ewo_form_steps_customization'
        );
        // Show Step Labels
        add_settings_field(
            'show_step_labels',
            __('Show Step Labels', 'ewo-location-services'),
            function() use ($opts) {
                $val = isset($opts['show_step_labels']) ? $opts['show_step_labels'] : 'yes';
                echo '<select name="ewo_service_listing_options[show_step_labels]">';
                echo '<option value="yes"' . selected($val, 'yes', false) . '>Yes</option>';
                echo '<option value="no"' . selected($val, 'no', false) . '>No</option>';
                echo '</select>';
            },
            'ewo_form_steps_customization',
            'ewo_form_steps_customization'
        );
        // Custom Step Labels
        $default_labels = ['Location','Service','Plan','Addons','Your Information','Confirmation'];
        for ($i=1; $i<=6; $i++) {
            add_settings_field(
                'step_label_' . $i,
                sprintf(__('Step %d Label', 'ewo-location-services'), $i),
                function() use ($opts, $i, $default_labels) {
                    $val = isset($opts['step_label_'.$i]) ? $opts['step_label_'.$i] : $default_labels[$i-1];
                    echo '<input type="text" name="ewo_service_listing_options[step_label_'.$i.']" value="' . esc_attr($val) . '" style="width:220px;">';
                },
                'ewo_form_steps_customization',
                'ewo_form_steps_customization'
            );
        }
        // Icon Type selector
        add_settings_field(
            'step_icon_type',
            __('Icon Type', 'ewo-location-services'),
            function() use ($opts) {
                $val = isset($opts['step_icon_type']) ? $opts['step_icon_type'] : 'dashicons';
                echo '<select id="ewo-step-icon-type" name="ewo_service_listing_options[step_icon_type]">';
                echo '<option value="none"' . selected($val, 'none', false) . '>None</option>';
                echo '<option value="dashicons"' . selected($val, 'dashicons', false) . '>Dashicons</option>';
                echo '<option value="svg"' . selected($val, 'svg', false) . '>SVG</option>';
                echo '</select>';
            },
            'ewo_form_steps_customization',
            'ewo_form_steps_customization'
        );
        // Dashicon for each step (only if dashicons selected)
        for ($i=1; $i<=6; $i++) {
            add_settings_field(
                'step_icon_' . $i,
                sprintf(__('Step %d Dashicon', 'ewo-location-services'), $i),
                function() use ($opts, $i) {
                    $val = isset($opts['step_icon_'.$i]) ? $opts['step_icon_'.$i] : 'location';
                    echo '<div class="ewo-step-icon-dashicon-row" data-icon-type="dashicons">';
                    echo '<input type="text" name="ewo_service_listing_options[step_icon_'.$i.']" value="' . esc_attr($val) . '" style="width:120px;">';
                    echo ' <span class="dashicons dashicons-' . esc_attr($val) . '"></span>';
                    echo '</div>';
                },
                'ewo_form_steps_customization',
                'ewo_form_steps_customization'
            );
        }
        // SVG upload for each step (only if svg selected)
        for ($i=1; $i<=6; $i++) {
            add_settings_field(
                'step_svg_' . $i,
                sprintf(__('Step %d SVG Icon', 'ewo-location-services'), $i),
                function() use ($opts, $i) {
                    $val = isset($opts['step_svg_'.$i]) ? $opts['step_svg_'.$i] : '';
                    echo '<div class="ewo-step-icon-svg-row" data-icon-type="svg">';
                    echo '<input type="text" id="ewo-step-svg-'.$i.'" name="ewo_service_listing_options[step_svg_'.$i.']" value="' . esc_attr($val) . '" style="width:60%" placeholder="Paste SVG URL or upload"> ';
                    echo '<button class="button ewo-upload-svg" data-target="#ewo-step-svg-'.$i.'">Upload SVG</button>';
                    if ($val) {
                        echo '<div style="margin-top:8px;"><img src="' . esc_url($val) . '" alt="SVG preview" style="height:32px;width:32px;vertical-align:middle;"></div>';
                    }
                    echo '<p class="description">Upload an SVG file. Recommended size: 32x32px. If no SVG is uploaded, the default Dashicon will be used.</p>';
                    echo '</div>';
                },
                'ewo_form_steps_customization',
                'ewo_form_steps_customization'
            );
        }
        // JS para alternar campos según el tipo de icono y el estilo de pasos
        add_action('admin_footer', function() {
            $screen = get_current_screen();
            if ($screen && strpos($screen->id, 'ewo-location-services') !== false) {
                ?>
                <script>
                jQuery(function($){
                    function toggleStepCustomizationFields() {
                        var style = $('#ewo-form-steps-style').val();
                        if (style === 'none') {
                            // Oculta todos los campos excepto el select de estilo
                            $('#ewo-form-steps-style').closest('tr').siblings().hide();
                            return;
                        } else {
                            $('#ewo-form-steps-style').closest('tr').siblings().show();
                        }
                        // Ahora controlar los iconos
                        var type = $('#ewo-step-icon-type').val();
                        if (type === 'none') {
                            $('.ewo-step-icon-dashicon-row').closest('tr').hide();
                            $('.ewo-step-icon-svg-row').closest('tr').hide();
                        } else {
                            $('.ewo-step-icon-dashicon-row').closest('tr').toggle(type === 'dashicons');
                            $('.ewo-step-icon-svg-row').closest('tr').toggle(type === 'svg');
                        }
                    }
                    $('#ewo-form-steps-style, #ewo-step-icon-type').on('change', toggleStepCustomizationFields);
                    toggleStepCustomizationFields();
                    // Media uploader para SVG
                    $('.ewo-upload-svg').on('click', function(e){
                        e.preventDefault();
                        var target = $(this).data('target');
                        var frame = wp.media({
                            title: 'Select or Upload SVG',
                            button: { text: 'Use this SVG' },
                            library: { type: 'image/svg+xml' },
                            multiple: false
                        });
                        frame.on('select', function(){
                            var url = frame.state().get('selection').first().toJSON().url;
                            $(target).val(url);
                        });
                        frame.open();
                    });
                });
                </script>
                <?php
            }
        });
        // Sección de personalización de pasos
        add_settings_section(
            'ewo_form_steps_customization',
            __('Form Steps Customization', 'ewo-location-services'),
            function() {
                echo '<p>Customize the appearance and content of the multi-step form progress bar.</p>';
            },
            'ewo_form_steps_customization'
        );
        // Sección y campos para Autocomplete
        add_settings_section(
            'ewo_autocomplete_settings_section',
            __('Provider Configuration', 'ewo-location-services'),
            function() {
                echo '<p>' . __('Configure the provider for address autocomplete and map display.', 'ewo-location-services') . '</p>';
            },
            'ewo_autocomplete_settings'
        );
        add_settings_field(
            'map_provider',
            __('Map Provider', 'ewo-location-services'),
            array($this, 'settings_field_map_provider_cb'),
            'ewo_autocomplete_settings',
            'ewo_autocomplete_settings_section'
        );
        add_settings_field(
            'autocomplete_provider',
            __('Autocomplete Provider', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['autocomplete_provider']) ? $opts['autocomplete_provider'] : 'nominatim';
                echo '<select id="ewo_autocomplete_provider" name="ewo_service_listing_options[autocomplete_provider]">';
                echo '<option value="nominatim"' . selected($val, 'nominatim', false) . '>Nominatim (OpenStreetMap)</option>';
                echo '<option value="google"' . selected($val, 'google', false) . '>Google Places</option>';
                echo '<option value="mapbox"' . selected($val, 'mapbox', false) . '>Mapbox</option>';
                echo '<option value="algolia"' . selected($val, 'algolia', false) . '>Algolia Places</option>';
                echo '</select>';
            },
            'ewo_autocomplete_settings',
            'ewo_autocomplete_settings_section'
        );
        add_settings_field(
            'map_google_api_key',
            __('Google Maps API Key', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['map_google_api_key']) ? esc_attr($opts['map_google_api_key']) : '';
                echo '<div id="ewo_map_google_api_key_row">';
                echo '<input type="text" name="ewo_service_listing_options[map_google_api_key]" value="' . $val . '" style="width: 350px;">';
                echo '<p class="description">Required for Google Maps display. Must have Maps JavaScript API enabled.</p>';
                echo '</div>';
            },
            'ewo_autocomplete_settings',
            'ewo_autocomplete_settings_section'
        );
        add_settings_field(
            'google_autocomplete_api_key',
            __('Google Autocomplete API Key', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['google_autocomplete_api_key']) ? esc_attr($opts['google_autocomplete_api_key']) : '';
                echo '<div id="ewo_google_autocomplete_api_key_row">';
                echo '<input type="text" name="ewo_service_listing_options[google_autocomplete_api_key]" value="' . $val . '" style="width: 350px;">';
                echo '<p class="description">Required for Google Places Autocomplete. Must have Places API enabled.</p>';
                echo '</div>';
            },
            'ewo_autocomplete_settings',
            'ewo_autocomplete_settings_section'
        );
        add_settings_field(
            'mapbox_autocomplete_api_key',
            __('Mapbox Autocomplete API Key', 'ewo-location-services'),
            function() {
                $opts = get_option('ewo_service_listing_options');
                $val = isset($opts['mapbox_autocomplete_api_key']) ? esc_attr($opts['mapbox_autocomplete_api_key']) : '';
                echo '<div id="ewo_mapbox_autocomplete_api_key_row">';
                echo '<input type="text" name="ewo_service_listing_options[mapbox_autocomplete_api_key]" value="' . $val . '" style="width: 350px;">';
                echo '<p class="description">Required for Mapbox Autocomplete. Must have Geocoding API enabled.</p>';
                echo '</div>';
            },
            'ewo_autocomplete_settings',
            'ewo_autocomplete_settings_section'
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
                $unlink_result = unlink($file_path);
                clearstatcache(); // Limpiar caché de estado de archivos
                if (!file_exists($file_path)) {
                    wp_redirect(admin_url('admin.php?page=' . $this->plugin_name . '-logs&cleared=1'));
                    exit;
                } else {
                    // Fallback: vaciar el archivo si no se puede eliminar
                    $empty_result = file_put_contents($file_path, '');
                    if ($empty_result !== false) {
                        wp_redirect(admin_url('admin.php?page=' . $this->plugin_name . '-logs&cleared=2'));
                        exit;
                    } else {
                        wp_redirect(admin_url('admin.php?page=' . $this->plugin_name . '-logs&cleared=0&error=1'));
                        exit;
                    }
                }
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
        // Construir la key final igual que en el frontend
        $prefix = $environment === 'development' ? 'dev_' : 'prod_';
        $key = $prefix . $endpoint_type . '_url';
        $api_url = isset($options[$key]) ? $options[$key] : '';
        if (empty($api_url)) {
            $this->logger->error('API URL not configured for endpoint: ' . $key);
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
                'api-key' => $api_key
            )),
            'method' => 'POST',
            'timeout' => 45,
            'data_format' => 'body'
        );
        // Realizar la solicitud a la API
        $response = wp_remote_post($api_url, $args);
        // Registrar la respuesta
        $this->logger->info('API Test Response received for endpoint: ' . $key);
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
                'api-key' => substr($api_key, 0, 4) . '****'
            ),
            'response_status' => $response_code,
            'response_headers' => wp_remote_retrieve_headers($response),
            'timestamp' => current_time('mysql')
        );
        // Verificar si la respuesta es válida
        if (empty($data) && $response_code !== 204) {
            $this->logger->error('Invalid API response format for endpoint: ' . $key);
            wp_send_json_error(array(
                'message' => __('Received invalid response from API.', 'ewo-location-services'),
                'raw_response' => $body,
                'debug' => $debug_data
            ));
        }
        // Registrar la respuesta completa para depuración adicional
        $this->logger->debug('API Test successful for endpoint: ' . $key);
        // Enviar la respuesta procesada
        wp_send_json_success(array(
            'message' => sprintf(__('API test successful. Response code: %d', 'ewo-location-services'), $response_code),
            'raw_response' => $data,
            'debug' => $debug_data
        ));
    }

    /**
     * Renderiza el campo de selección para el proveedor de mapas.
     *
     * @since 1.0.1
     */
    public function settings_field_map_provider_cb() {
        $options = get_option('ewo_service_listing_options');
        $map_provider = isset($options['map_provider']) ? $options['map_provider'] : 'osm'; // 'osm' como valor por defecto

        ?>
        <select id="ewo_map_provider" name="ewo_service_listing_options[map_provider]">
            <option value="osm" <?php selected($map_provider, 'osm'); ?>>
                <?php esc_html_e('OpenStreetMap (Leaflet)', 'ewo-location-services'); ?>
            </option>
            <option value="google" <?php selected($map_provider, 'google'); ?>>
                <?php esc_html_e('Google Maps', 'ewo-location-services'); ?>
            </option>
        </select>
        <p class="description">
            <?php esc_html_e('Select the map provider for the frontend map display. If Google Maps is chosen, ensure the necessary API key is configured and has the Maps JavaScript API enabled.', 'ewo-location-services'); ?>
        </p>
        <?php
    }
}

if (is_admin()) {
    add_action('admin_post_clear_log', function() {
        require_once dirname(__DIR__) . '/includes/class-ewo-location-services-logger.php';
        $logger = new Ewo_Location_Services_Logger();
        $admin = new Ewo_Location_Services_Admin('ewo-location-services', defined('EWO_LOCATION_SERVICES_VERSION') ? EWO_LOCATION_SERVICES_VERSION : '1.0.0', $logger);
        $admin->handle_log_clear();
    });
    // Handler para guardar logging_enabled desde la página de logs
    add_action('admin_post_ewo_save_logging_settings', function() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('ewo_save_logging_settings', 'ewo_save_logging_settings_nonce');
        $opts = get_option('ewo_location_services_options', array());
        $opts['logging_enabled'] = isset($_POST['logging_enabled']) && $_POST['logging_enabled'] === 'yes' ? 'yes' : 'no';
        update_option('ewo_location_services_options', $opts);
        wp_redirect(admin_url('admin.php?page=ewo-location-services-logs&logging_saved=1'));
        exit;
    });
}
