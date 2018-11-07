<?php

/**
 * The file that defines the core plugin class
 *
 * @since      0.1.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define admin-specific and event catcher hooks and filters
 *
 * @since      0.1.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Lauri Leiten <leitenlauri@gmail.com>
 * @author     Stiivo Siider <stiivosiider@gmail.com>
 * @author     Martin Jürgel <martin457345@gmail.com>
 * @author     Hannes Saariste <hannes.saariste@gmail.com>
 */
class Recommender
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      Recommender_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies and set the hooks and filters for event catching and the admin area
     *
     * @since    0.1.0
     */
    public function __construct()
    {
        if (defined('PLUGIN_NAME_VERSION')) {
            $this->version = PLUGIN_NAME_VERSION;
        } else {
            $this->version = '0.1.0';
        }
        $this->plugin_name = 'recommender';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_event_hooks_filters();
        $this->define_Log_Handler();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following file that make up the plugin:
     *
     * - Recommender_Loader. Orchestrates the hooks of the plugin.
     * - Recommender_Admin. Defines all hooks for the admin area.
     * - Event_Catcher. Defines all hooks for catching events.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * Loads log handler interface
         */
        require_once plugin_dir_path(dirname(__FILE__)) . '../' . 'woocommerce/includes/interfaces/class-wc-log-handler-interface.php';

        /**
         * Loads log handler
         */
        require_once plugin_dir_path(dirname(__FILE__)) . '../' . 'woocommerce/includes/abstracts/abstract-wc-log-handler.php';
        
        /**
         * Loads log handler file so it can be extended
         */
        require_once plugin_dir_path(dirname(__FILE__)) . '../' . 'woocommerce/includes/log-handlers/class-wc-log-handler-file.php';


        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recommender-loader.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-recommender-admin.php';

        /**
         * The class responsible for communicating with the API.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recommender-api.php';

        /**
         * Log handler for logging.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recommender-wc-handler.php';

        /**
         * The class responsible for catching WooCommerce events.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recommender-event-catcher.php';

        /**
         * The class responsible for syncing the stores catalog.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recommender-catalog-syncer.php';



        $this->loader = new Recommender_Loader();

    }

    /**
     * instance Recommender_WC_Log_Handler
     *
     * @since    0.3.0
     * @access   private
     */
    private function define_Log_Handler(){
        new Recommender_WC_Log_Handler($this->get_version());
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    0.1.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Recommender_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_menu', $plugin_admin, 'recommender_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'recommender_admin_init');
    }

    /**
     * Register all of the hooks and filters related to the event catching functionality
     * of the plugin.
     *
     * @since    0.1.0
     * @access   private
     */
    private function define_event_hooks_filters()
    {

        $plugin_catcher = new Recommender_Event_Catcher($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('woocommerce_add_to_cart', $plugin_catcher,'woocommerce_add_to_cart_callback', 10, 6);
        $this->loader->add_action('woocommerce_single_product_summary', $plugin_catcher, 'woocommerce_single_product_summary_callback', 25);
        $this->loader->add_action('woocommerce_payment_complete', $plugin_catcher, 'woocommerce_payment_complete_callback');
        $this->loader->add_filter('pre_get_posts', $plugin_catcher,'woocommerce_search_callback');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    0.1.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress
     *
     * @since     0.1.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     0.1.0
     * @return    Recommender_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     0.1.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}
