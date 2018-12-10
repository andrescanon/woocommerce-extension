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
        if (!defined('PLUGIN_NAME_VERSION'))
            define('PLUGIN_NAME_VERSION', '1.0.0');

        $this->version = PLUGIN_NAME_VERSION;
        $this->plugin_name = 'recommender';

        $this->load_dependencies();
        $this->define_hooks();
    }

    /**
     * Load the required dependencies for this plugins
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
        require_once WP_PLUGIN_DIR . '/woocommerce/includes/interfaces/class-wc-log-handler-interface.php' ;

        /**
         * Loads log handler
         */
        require_once WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-log-handler.php' ;

        /**
         * Loads log handler file so it can be extended
         */
        require_once WP_PLUGIN_DIR . '/woocommerce/includes/log-handlers/class-wc-log-handler-file.php';

        /**
         * Loads Async request class
         */
        require_once WP_PLUGIN_DIR . '/woocommerce/includes/libraries/wp-async-request.php';

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
         * The class responsible for displaying recommended products.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recommender-product-displayer.php';

        /**
         * The class responsible for syncing
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recommender-syncer.php';

        /**
         * The class responsible for custom Wordpress REST API endpoints
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recommender-endpoints.php';

        $this->loader = new Recommender_Loader();
    }

    /**

     * Register all of the hooks related to the plugin functionality
     *
     * @since    0.5.0
     * @access   private
     */
    private function define_hooks()
    {
        /**
         * Hooks for admin areas
         */
        $plugin_admin = new Recommender_Admin();

        $this->loader->add_action('admin_menu', $plugin_admin, 'recommender_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'recommender_admin_init');

        /**
         * Hooks for event catching
         */
        $event_catcher = new Recommender_Event_Catcher(new Recommender_API());

        $this->loader->add_action('template_redirect', $event_catcher, 'woocommerce_remove_default_callback');
        $this->loader->add_action('woocommerce_add_to_cart', $event_catcher,'woocommerce_add_to_cart_callback', 10, 6);
        $this->loader->add_action('woocommerce_single_product_summary', $event_catcher, 'woocommerce_single_product_summary_callback', 25);
        $this->loader->add_action('woocommerce_payment_complete', $event_catcher, 'woocommerce_payment_complete_callback');
        $this->loader->add_filter('pre_get_posts', $event_catcher,'woocommerce_search_callback');

        /**
         * Hooks for product displaying
         */

        $options = array("woocommerce_before_single_product_summary", "woocommerce_after_single_product_summary",
            "woocommerce_before_shop_loop", "woocommerce_after_shop_loop", "woocommerce_before_cart",
            "woocommerce_after_cart_table", "woocommerce_after_cart_totals","woocommerce_after_cart"
        );
        $displayers = array();

        foreach ($options as $option) {
            $enabled = get_option($option);
            $id = get_option($option . '_id');
            if ($enabled && $id)
            {
                $displayer = new Recommender_Product_Displayer(new Recommender_API());
                $displayers[$id] = $displayer;
                $displayer->set_box_properties($id, $option);
                $this->loader->add_action($option, $displayer, 'woocommerce_output_related_products', get_option($option));
            }
        }


        /**
         * Hook for starting the WooCommerce session
         */
        add_action( 'woocommerce_init', function(){
            $session = WC()->session;

            if ($session == null)
                return;

            if ( ! $session->has_session() ) {
                WC()->session->set_customer_session_cookie( true );
            }
        } );


        /**
         * Hook for creating the WP REST API endpoints
         */
        add_action( 'rest_api_init',  function () {
            $routes= new Recommender_Endpoints();
            return $routes->register_routes();
        });
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

    /**
     * Retrieve session ID of the customer
     *
     * @since     0.5.0
     * @return    string    The session ID of the customer
     */
    public static function get_session_id()
    {
        $session = WC()->session;
        if ($session == null)
            return null;
        $user_id = $session->get_customer_id();
        if ($user_id == null)
            return null;
        Recommender_WC_Log_Handler::logDebug( "Customer ID: " . $user_id );

        if ($user_id == null)
            Recommender_WC_Log_Handler::logError("Failed to get Customer ID; result was null");

        return $user_id;
    }
}
