<?php
/**
 * The product displaying functionality of the plugin.
 *
 * Defines the plugin name, version and callbacks for displaying related products
 *
 * @since      0.3.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Hannes Saariste <hannes.saariste@gmail.com>
 */
class Recommender_Product_Displayer
{
    /**
     * An instance of this class
     *
     * @since      0.3.0
     * @access     private
     * @var        Recommender_Product_Displayer $instance An instance of this class
     */
    private static $instance = null;

    /**
     * The ID of this plugin.
     *
     * @since      0.3.0
     * @access     private
     * @var        string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since      0.3.0
     * @access     private
     * @var        string $version The current version of this plugin.
     */
    private $version;

    /**
     * Used for storing products to display in the widget
     *
     * @since      0.3.0
     * @access     private
     * @var        array $products_to_show The ID's of the products to display.
     */
    private static $products_to_show;

    /**
     * Prevents cloning of a class instance
     *
     * @since      0.3.0
     * @access     private
     */
    private function __clone() {}

    /**
     * Returns an instance of this class
     *
     * @since      0.3.0
     */
    public static function get_instance()
    {
        return self::$instance;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     * @since      0.3.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * @param array $to_display array of products id's to show at widget
     * @since 0.3.0
     */
    public function set_products_for_display($to_display = array()){
        self::$products_to_show = $to_display;
    }


    /**
     * @return array products_to_show
     * @since 0.3.0
     */
    static function display_my_related_products(){
        return self::$products_to_show;
    }

    /**
     * Callback for outputting related products.
     *
     * @since 0.3.0
     */
    public function woocommerce_output_related_products(){
        global $product;

        /*
         * Just a temporary solution for testing purposes. If no product available to get related products to,
         * get products related to product ID 15.
         */
        if ( ! $product ) {
            $ids = wc_get_products( array( 'return' => 'ids', 'limit' => -1 ) );
            $ids = array_reverse($ids);
            $id = array_pop($ids);
            $product = wc_get_product($id);
            if ( ! $product )
            {
                return;
            }
        }

        $args = array(
            'posts_per_page' => 4,
            'columns' => 4,
            'orderby' => 'rand'
        );

        $data_to_send = [
            'item_id' => $product->get_id(),
            'stacc_id' => get_current_user_id(),
            'block_id' => '0',
            'website' => get_site_url(),
            'properties' => []
        ];

        $received_recommendations = Recommender_API::get_instance()->send_post($data_to_send, 'recs' );
        $received_ids = $received_recommendations->{"items"};
        Recommender_WC_Log_Handler::logInformational('Recommended product IDs received from API: ' . json_encode($received_ids));

        $this->set_products_for_display($received_ids);
        add_filter( 'woocommerce_related_products', array( __CLASS__, 'display_my_related_products') );
        woocommerce_related_products( apply_filters( 'woocommerce_output_related_products_args', $args ) );

    }
}