<?php
if (!defined('ABSPATH')) exit;

class Ewo_Location_Services_Admin {
    public static function activate() {
        // Lógica de activación (crear opciones, tablas, etc.)
    }
    public static function deactivate() {
        // Lógica de desactivación (limpieza si aplica)
    }
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        // Nueva página de estilos y personalización
        add_action('admin_menu', [$this, 'add_styles_customization_page']);
    }
    public function add_admin_menu() {
        add_menu_page(
            'EWO Location Services',
            'Location Services',
            'manage_options',
            'ewo-location-services',
            [$this, 'settings_page'],
            'dashicons-location-alt'
        );
    }
    public function enqueue_admin_styles() {
        // Estilos tipo WooCommerce para tabs
        echo '<style>
        .ewo-tabs { border-bottom: 1px solid #e5e5e5; margin-bottom: 24px; }
        .ewo-tabs a { display: inline-block; padding: 10px 24px; border: 1px solid #e5e5e5; border-bottom: none; background: #f9f9f9; color: #444; text-decoration: none; margin-right: 4px; border-radius: 4px 4px 0 0; }
        .ewo-tabs a.active { background: #fff; border-bottom: 1px solid #fff; font-weight: bold; }
        .ewo-tab-content { background: #fff; border: 1px solid #e5e5e5; padding: 24px; border-radius: 0 4px 4px 4px; }
        .form-table th { width: 260px; }
        </style>';
    }
    public function add_styles_customization_page() {
        add_submenu_page(
            'ewo-location-services',
            'Styles & Customization',
            'Styles & Customization',
            'manage_options',
            'ewo-styles-customization',
            [$this, 'render_styles_customization_page']
        );
    }
    public function render_styles_customization_page() {
        ?>
        <div class="wrap">
            <h1>Styles & Customization</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('ewo_styles_customization_group');
                do_settings_sections('ewo_styles_customization');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    public function register_settings() {
        // General
        register_setting('ewo_location_services_options', 'ewo_env');
        register_setting('ewo_location_services_options', 'ewo_api_key');
        // Endpoints (dev/prod, todos los relevantes)
        $endpoints = [
            // Serviceability
            'getServiceabilityDetails', 'getServiceableLocationsByLatLng',
            // Package
            'getPackages', 'getDiscountTemplates', 'addPackageToParent',
            // Customer
            'createCustomer', 'updateCustomer', 'createCustomerComment',
            // Opportunity
            'createOpportunity', 'updateOpportunity', 'getOpportunity'
        ];
        foreach ($endpoints as $ep) {
            register_setting('ewo_location_services_options', "ewo_endpoint_{$ep}_dev");
            register_setting('ewo_location_services_options', "ewo_endpoint_{$ep}_prod");
        }
        // Mapas
        register_setting('ewo_location_services_options', 'ewo_map_provider');
        register_setting('ewo_location_services_options', 'ewo_mapbox_api_key');
        register_setting('ewo_location_services_options', 'ewo_google_maps_api_key');
        register_setting('ewo_location_services_options', 'ewo_map_zoom');
        // Autocomplete
        register_setting('ewo_location_services_options', 'ewo_autocomplete_provider');
        register_setting('ewo_location_services_options', 'ewo_autocomplete_mapbox_api_key');
        register_setting('ewo_location_services_options', 'ewo_autocomplete_google_api_key');
        // Page Mapping
        register_setting('ewo_location_services_options', 'ewo_page_coverage_yes');
        register_setting('ewo_location_services_options', 'ewo_page_coverage_maybe');
        register_setting('ewo_location_services_options', 'ewo_page_coverage_not_yet');
        register_setting('ewo_location_services_options', 'ewo_page_coverage_no');
        register_setting('ewo_location_services_options', 'ewo_page_service_selection');
        register_setting('ewo_location_services_options', 'ewo_page_addons');
        register_setting('ewo_location_services_options', 'ewo_page_cart');
        register_setting('ewo_location_services_options', 'ewo_page_checkout');
        // Registrar grupo y campos para estilos globales
        register_setting('ewo_styles_customization_group', 'ewo_color_primary');
        register_setting('ewo_styles_customization_group', 'ewo_color_secondary');
        register_setting('ewo_styles_customization_group', 'ewo_color_alert');

        add_settings_section(
            'ewo_styles_colors_section',
            'Global Color Variables',
            function() {
                echo '<p>Configure the global color variables for the plugin. These will be available as CSS variables in all templates.</p>';
            },
            'ewo_styles_customization'
        );
        add_settings_field(
            'ewo_color_primary',
            'Primary Color',
            function() {
                $val = get_option('ewo_color_primary', '#203F9A');
                echo '<input type="color" name="ewo_color_primary" value="' . esc_attr($val) . '"> <span style="margin-left:8px;">' . esc_html($val) . '</span>';
            },
            'ewo_styles_customization',
            'ewo_styles_colors_section'
        );
        add_settings_field(
            'ewo_color_secondary',
            'Secondary Color',
            function() {
                $val = get_option('ewo_color_secondary', '#58DCFC');
                echo '<input type="color" name="ewo_color_secondary" value="' . esc_attr($val) . '"> <span style="margin-left:8px;">' . esc_html($val) . '</span>';
            },
            'ewo_styles_customization',
            'ewo_styles_colors_section'
        );
        add_settings_field(
            'ewo_color_alert',
            'Alert Color',
            function() {
                $val = get_option('ewo_color_alert', '#FF4B4B');
                echo '<input type="color" name="ewo_color_alert" value="' . esc_attr($val) . '"> <span style="margin-left:8px;">' . esc_html($val) . '</span>';
            },
            'ewo_styles_customization',
            'ewo_styles_colors_section'
        );
    }
    public function settings_page() {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        $tabs = [
            'general' => 'General',
            'maps' => 'Mapas',
            'autocomplete' => 'Autocomplete',
            'pages' => 'Page Mapping',
            'shortcodes' => 'Shortcodes',
        ];
        echo '<div class="wrap"><h1>EWO Location Services – Configuración</h1>';
        // Tabs
        echo '<nav class="ewo-tabs">';
        foreach ($tabs as $id => $label) {
            $active = ($tab === $id) ? 'active' : '';
            echo "<a href='#' class='ewo-tab-btn $active' data-tab='$id'>$label</a>";
        }
        echo '</nav>';
        echo '<form method="post" action="options.php">';
        settings_fields('ewo_location_services_options');
        echo '<div id="ewo-tab-content-general" class="ewo-tab-content" style="display:none">';
        // Selector de entorno
        $env = get_option('ewo_env', 'dev');
        echo '<table class="form-table">';
        echo '<tr><th>Entorno actual</th><td>';
        echo '<select name="ewo_env">';
        echo '<option value="dev"' . selected($env, 'dev', false) . '>Desarrollo</option>';
        echo '<option value="prod"' . selected($env, 'prod', false) . '>Producción</option>';
        echo '</select></td></tr>';
        // API Key global
        echo '<tr><th>API Key (para endpoints)</th><td><input type="text" name="ewo_api_key" value="' . esc_attr(get_option('ewo_api_key')) . '" size="40" /></td></tr>';
        // Endpoints agrupados
        $endpoint_groups = [
            'Serviceability Endpoints' => ['getServiceabilityDetails', 'getServiceableLocationsByLatLng'],
            'Package Endpoints' => ['getPackages', 'getDiscountTemplates', 'addPackageToParent'],
            'Customer Endpoints' => ['createCustomer', 'updateCustomer', 'createCustomerComment'],
            'Opportunity Endpoints' => ['createOpportunity', 'updateOpportunity', 'getOpportunity'],
        ];
        foreach ($endpoint_groups as $group_label => $eps) {
            echo "<tr><th colspan='2'><h3>$group_label</h3></th></tr>";
            foreach ($eps as $ep) {
                echo "<tr><th colspan='2'><h4>$ep</h4></th></tr>";
                echo "<tr><th>Dev</th><td><input type='text' name='ewo_endpoint_{$ep}_dev' value='" . esc_attr(get_option("ewo_endpoint_{$ep}_dev")) . "' size='60' /></td></tr>";
                echo "<tr><th>Prod</th><td><input type='text' name='ewo_endpoint_{$ep}_prod' value='" . esc_attr(get_option("ewo_endpoint_{$ep}_prod")) . "' size='60' /></td></tr>";
            }
        }
        echo '</table>';
        echo '</div>';
        echo '<div id="ewo-tab-content-maps" class="ewo-tab-content" style="display:none">';
        $map_provider = get_option('ewo_map_provider', 'osm');
        echo '<table class="form-table">';
        echo '<tr><th>Proveedor de mapas</th><td>';
        echo '<select name="ewo_map_provider" id="ewo_map_provider">';
        echo '<option value="osm"' . selected($map_provider, 'osm', false) . '>OpenStreetMap (Leaflet)</option>';
        echo '<option value="google"' . selected($map_provider, 'google', false) . '>Google Maps</option>';
        echo '<option value="mapbox"' . selected($map_provider, 'mapbox', false) . '>Mapbox</option>';
        echo '</select></td></tr>';
        // API key solo si aplica
        echo '<tr id="row_mapbox_api_key" style="display:' . ($map_provider === 'mapbox' ? 'table-row' : 'none') . '"><th>Mapbox API Key</th><td><input type="text" name="ewo_mapbox_api_key" value="' . esc_attr(get_option('ewo_mapbox_api_key')) . '" size="40" /></td></tr>';
        echo '<tr id="row_google_maps_api_key" style="display:' . ($map_provider === 'google' ? 'table-row' : 'none') . '"><th>Google Maps API Key</th><td><input type="text" name="ewo_google_maps_api_key" value="' . esc_attr(get_option('ewo_google_maps_api_key')) . '" size="40" /></td></tr>';
        // Campo de zoom
        $map_zoom = get_option('ewo_map_zoom', 15);
        echo '<tr><th>Nivel de zoom al seleccionar punto</th><td>';
        echo '<input type="number" name="ewo_map_zoom" value="' . esc_attr($map_zoom) . '" min="1" max="21" />';
        echo '<p class="description">Nivel de zoom que se aplicará al centrar el mapa (por defecto: 15).</p>';
        echo '</td></tr>';
        echo '</table>';
        echo '</div>';
        echo '<div id="ewo-tab-content-autocomplete" class="ewo-tab-content" style="display:none">';
        $autocomplete_provider = get_option('ewo_autocomplete_provider', 'osm');
        echo '<table class="form-table">';
        echo '<tr><th>Proveedor de autocomplete</th><td>';
        echo '<select name="ewo_autocomplete_provider" id="ewo_autocomplete_provider">';
        echo '<option value="osm"' . selected($autocomplete_provider, 'osm', false) . '>Nominatim (OSM)</option>';
        echo '<option value="google"' . selected($autocomplete_provider, 'google', false) . '>Google Places</option>';
        echo '<option value="mapbox"' . selected($autocomplete_provider, 'mapbox', false) . '>Mapbox</option>';
        echo '</select></td></tr>';
        // API key solo si aplica
        echo '<tr id="row_autocomplete_mapbox_api_key" style="display:' . ($autocomplete_provider === 'mapbox' ? 'table-row' : 'none') . '"><th>Mapbox API Key</th><td><input type="text" name="ewo_autocomplete_mapbox_api_key" value="' . esc_attr(get_option('ewo_autocomplete_mapbox_api_key')) . '" size="40" /></td></tr>';
        echo '<tr id="row_autocomplete_google_api_key" style="display:' . ($autocomplete_provider === 'google' ? 'table-row' : 'none') . '"><th>Google Places API Key</th><td><input type="text" name="ewo_autocomplete_google_api_key" value="' . esc_attr(get_option('ewo_autocomplete_google_api_key')) . '" size="40" /></td></tr>';
        echo '</table>';
        echo '</div>';
        echo '<div id="ewo-tab-content-pages" class="ewo-tab-content" style="display:none">';
        echo '<table class="form-table">';
        // Page mapping fields
        $pages = get_pages();
        function page_dropdown($name, $selected, $label) {
            echo '<tr><th>' . esc_html($label) . '</th><td>';
            wp_dropdown_pages([
                'name' => $name,
                'selected' => $selected,
                'show_option_none' => '-- Select page --',
                'option_none_value' => ''
            ]);
            echo '</td></tr>';
        }
        page_dropdown('ewo_page_coverage_yes', get_option('ewo_page_coverage_yes'), 'Coverage Yes Page');
        page_dropdown('ewo_page_coverage_maybe', get_option('ewo_page_coverage_maybe'), 'Coverage Maybe Page');
        page_dropdown('ewo_page_coverage_not_yet', get_option('ewo_page_coverage_not_yet'), 'Coverage Not Yet Page');
        page_dropdown('ewo_page_coverage_no', get_option('ewo_page_coverage_no'), 'Coverage No Page');
        page_dropdown('ewo_page_service_selection', get_option('ewo_page_service_selection'), 'Service Selection Page');
        page_dropdown('ewo_page_addons', get_option('ewo_page_addons'), 'Addons Popup Page');
        page_dropdown('ewo_page_cart', get_option('ewo_page_cart'), 'Cart Page');
        page_dropdown('ewo_page_checkout', get_option('ewo_page_checkout'), 'Checkout Page');
        echo '</table>';
        echo '</div>';
        echo '<div id="ewo-tab-content-shortcodes" class="ewo-tab-content" style="display:none">';
        echo '<h2>Shortcodes Usage</h2>';
        echo '<p>Use the following shortcodes to integrate the location service flow into your pages. You can copy and paste them as needed.</p>';
        echo '<h3>[ewo_location_services]</h3>';
        echo '<pre>[ewo_location_services]</pre>';
        echo '<p>Shows the location selection form (map, autocomplete, geolocation). Place this on the first step/page of the flow.</p>';
        echo '<h3>[ewo_location_coverage status="Yes"]...[/ewo_location_coverage]</h3>';
        echo '<pre>[ewo_location_coverage status="Yes"]
  &lt;h2&gt;Great news! We have coverage in your area.&lt;/h2&gt;
  &lt;a href="/select-plan" class="btn btn-primary"&gt;See available plans&lt;/a&gt;
[/ewo_location_coverage]</pre>';
        echo '<p>Shows the content only if the <code>status</code> parameter matches one of: <strong>Yes, Maybe, Not Yet, No</strong>. Use this on any page to display conditional content based on coverage result.</p>';
        echo '<h3>[ewo_location_coverage_auto]...[/ewo_location_coverage_auto]</h3>';
        echo '<pre>[ewo_location_coverage_auto]
  &lt;h2&gt;Great news! We have coverage in your area.&lt;/h2&gt;
  &lt;a href="/select-plan" class="btn btn-primary"&gt;See available plans&lt;/a&gt;
[/ewo_location_coverage_auto]</pre>';
        echo '<p>Shows the content only if the real coverage (from API/session) matches one of: <strong>yes, maybe, not yet, no</strong>. Use this on destination pages after automatic redirection.</p>';
        echo '<h4>Recommended Flow:</h4>';
        echo '<ol>';
        echo '<li>Create a page with <code>[ewo_location_services]</code> as the entry point.</li>';
        echo '<li>Map each step/page in the "Page Mapping" tab.</li>';
        echo '<li>On each mapped page, use <code>[ewo_location_coverage_auto]</code> to show the correct content for the user.</li>';
        echo '</ol>';
        echo '<p>For more advanced usage, you can combine shortcodes or add your own custom content inside them.</p>';
        echo '</div>';
        submit_button();
        echo '</form>';
        echo '</div></div>';
        // El script va fuera del echo para evitar errores de sintaxis
        ?>
        <script>
        jQuery(function($){
            function showTab(tab) {
                $('.ewo-tab-btn').removeClass('active');
                $('.ewo-tab-content').hide();
                $(".ewo-tab-btn[data-tab='" + tab + "']").addClass('active');
                $("#ewo-tab-content-" + tab).show();
            }
            $('.ewo-tab-btn').on('click', function(e){
                e.preventDefault();
                var tab = $(this).data('tab');
                showTab(tab);
                localStorage.setItem('ewo_service_tab', tab);
            });
            var lastTab = localStorage.getItem('ewo_service_tab') || 'general';
            showTab(lastTab);
            // Mostrar/ocultar campos de API key según proveedor
            $('#ewo_map_provider').on('change', function(){
                $('#row_mapbox_api_key').toggle(this.value==='mapbox');
                $('#row_google_maps_api_key').toggle(this.value==='google');
            });
            $('#ewo_autocomplete_provider').on('change', function(){
                $('#row_autocomplete_mapbox_api_key').toggle(this.value==='mapbox');
                $('#row_autocomplete_google_api_key').toggle(this.value==='google');
            });
        });
        </script>
        <?php
    }
    // Métodos para logs, estilos, etc. a implementar
}

// Instanciar la clase
new Ewo_Location_Services_Admin(); 