<?php
/**
 * La clase principal del plugin.
 *
 * Esta es la clase principal que coordina todas las funcionalidades del plugin.
 *
 * @since      1.0.0
 * @package    Ewo_Location_Services
 */

class Ewo_Location_Services {

    /**
     * El loader que es responsable de mantener y registrar todos los hooks que alimentan el plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Ewo_Location_Services_Loader    $loader    Mantiene y registra todos los hooks para el plugin.
     */
    protected $loader;

    /**
     * El identificador único de este plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    El nombre o identificador de este plugin.
     */
    protected $plugin_name;

    /**
     * La versión actual del plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    La versión actual del plugin.
     */
    protected $version;

    /**
     * El sistema de registro del plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Ewo_Location_Services_Logger    $logger    El sistema de registro del plugin.
     */
    protected $logger;

    /**
     * Define las funcionalidades principales del plugin.
     *
     * Establece el nombre y la versión del plugin que puede ser utilizado a lo largo del plugin.
     * Carga las dependencias, define la configuración regional, y establece los hooks para
     * el área de administración y el frontend del sitio.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'ewo-location-services';
        $this->version = EWO_LOCATION_SERVICES_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Carga las dependencias requeridas para este plugin.
     *
     * Incluye los siguientes archivos que componen el plugin:
     *
     * - Ewo_Location_Services_Loader. Orquesta los hooks del plugin.
     * - Ewo_Location_Services_i18n. Define la funcionalidad de internacionalización.
     * - Ewo_Location_Services_Admin. Define todos los hooks del área de administración.
     * - Ewo_Location_Services_Public. Define todos los hooks del frontend del sitio.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        // La clase responsable de orquestar las acciones y filtros del núcleo del plugin.
        require_once plugin_dir_path(__FILE__) . 'class-ewo-location-services-loader.php';

        // La clase responsable de definir la funcionalidad de internacionalización del plugin.
        require_once plugin_dir_path(__FILE__) . 'class-ewo-location-services-i18n.php';

        // La clase responsable de definir todas las acciones en el área de administración.
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ewo-location-services-admin.php';

        // La clase responsable de definir todas las acciones en el área pública.
        require_once plugin_dir_path(dirname(__FILE__)) . 'frontend/class-ewo-location-services-frontend.php';

        // Inicializar el logger
        $this->logger = new Ewo_Location_Services_Logger();

        $this->loader = new Ewo_Location_Services_Loader();
    }

    /**
     * Define la configuración regional para la internacionalización.
     *
     * Utiliza la clase Ewo_Location_Services_i18n para establecer el dominio y registrar el hook
     * con WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Ewo_Location_Services_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Registra todos los hooks relacionados con la funcionalidad del área de administración
     * del plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Ewo_Location_Services_Admin($this->get_plugin_name(), $this->get_version(), $this->logger);

        // Registrar scripts y estilos
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Registrar opciones y menú de administración
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // Registrar endpoint AJAX para el probador de API
        $this->loader->add_action('wp_ajax_ewo_api_test', $plugin_admin, 'handle_api_test');
    }

    /**
     * Registra todos los hooks relacionados con la funcionalidad del área pública
     * del plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Ewo_Location_Services_Frontend($this->get_plugin_name(), $this->get_version(), $this->logger);

        // Registrar scripts y estilos
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Registrar shortcode para el formulario de ubicación
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');

        // Registrar endpoints de AJAX
        $this->loader->add_action('wp_ajax_ewo_location_search', $plugin_public, 'handle_location_search');
        $this->loader->add_action('wp_ajax_nopriv_ewo_location_search', $plugin_public, 'handle_location_search');
        
        $this->loader->add_action('wp_ajax_ewo_submit_user', $plugin_public, 'handle_user_submission');
        $this->loader->add_action('wp_ajax_nopriv_ewo_submit_user', $plugin_public, 'handle_user_submission');
    }

    /**
     * Ejecuta el loader para ejecutar todos los hooks con WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * El nombre del plugin utilizado para identificarlo dentro de WordPress.
     *
     * @since     1.0.0
     * @return    string    El nombre del plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * La referencia a la clase que orquesta los hooks del plugin.
     *
     * @since     1.0.0
     * @return    Ewo_Location_Services_Loader    Orquesta los hooks del plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Recupera el número de versión del plugin.
     *
     * @since     1.0.0
     * @return    string    El número de versión del plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Recupera el objeto logger del plugin.
     *
     * @since     1.0.0
     * @return    Ewo_Location_Services_Logger    El objeto logger del plugin.
     */
    public function get_logger() {
        return $this->logger;
    }

    public function enqueue_scripts() {
        // ... código existente ...
        wp_enqueue_script(
            'ewo-location-services-frontend',
            plugin_dir_url(__FILE__) . '../frontend/js/ewo-location-services-frontend.js',
            array('jquery'),
            $this->version,
            true
        );
        // Pasar opciones del admin al JS
        $listing_options = get_option('ewo_service_listing_options', array());
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
            'step_active_bg' => isset($listing_options['step_active_bg']) ? $listing_options['step_active_bg'] : '#ffe3ef',
            'step_inactive_bg' => isset($listing_options['step_inactive_bg']) ? $listing_options['step_inactive_bg'] : '#eee',
            'step_size' => isset($listing_options['step_size']) ? intval($listing_options['step_size']) : 32,
            'show_step_labels' => isset($listing_options['show_step_labels']) ? $listing_options['show_step_labels'] : 'yes',
            'step_label_1' => isset($listing_options['step_label_1']) ? $listing_options['step_label_1'] : 'Location',
            'step_label_2' => isset($listing_options['step_label_2']) ? $listing_options['step_label_2'] : 'Service',
            'step_label_3' => isset($listing_options['step_label_3']) ? $listing_options['step_label_3'] : 'Addons',
            'step_label_4' => isset($listing_options['step_label_4']) ? $listing_options['step_label_4'] : 'Your Information',
            'step_label_5' => isset($listing_options['step_label_5']) ? $listing_options['step_label_5'] : 'Confirmation',
            'step_icon_1' => isset($listing_options['step_icon_1']) ? $listing_options['step_icon_1'] : 'location',
            'step_icon_2' => isset($listing_options['step_icon_2']) ? $listing_options['step_icon_2'] : 'location',
            'step_icon_3' => isset($listing_options['step_icon_3']) ? $listing_options['step_icon_3'] : 'location',
            'step_icon_4' => isset($listing_options['step_icon_4']) ? $listing_options['step_icon_4'] : 'location',
            'step_icon_5' => isset($listing_options['step_icon_5']) ? $listing_options['step_icon_5'] : 'location',
        ));
    }
}
