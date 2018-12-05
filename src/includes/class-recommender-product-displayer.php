<?php
/**
 * The product displaying functionality of the plugin.
 *
 * Defines the plugin name, version and callbacks for displaying related products
 *
 * @since      0.5.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Hannes Saariste <hannes.saariste@gmail.com>
 * @author     Lauri Leiten    <leitenlauri@gmail.com>
 */
class Recommender_Product_Displayer
{

    /**
     * Used for storing products to display in the widget
     *
     * @since      0.3.0
     * @access     private
     * @var        array $products_to_show The ID's of the products to display.
     */
    private $products_to_show = null;

    /**
     * Used for storing the reference to the API object
     *
     * @since      0.5.0
     * @access     private
     * @var        Recommender_API $api API object
     */
    private $api = null;

    /**
     * Initialize the class and set its properties.
     *
     * @since      0.5.0
     * @access     private
     */
    public function __construct($api)
    {
        $this->api = $api;
    }

    /**
     * @param array $to_display array of products id's to show at widget
     * @since 0.3.0
     */
    public function set_products_for_display($to_display = array()){
        $this->products_to_show = $to_display;
    }

    /**
     * @return array products_to_show
     * @since 0.3.0
     */
    public function display_my_related_products(){
        return $this->products_to_show;
    }

    /**
     * Callback for outputting related products.
     *
     * @since 0.3.0
     */
    public function woocommerce_output_related_products(){
        if(get_option( 'cred_check_failed', $default = true )) {
            return;
        }

        global $product;
        
        //if cant get product, takes first product from product list
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

        //widget related
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

        $received_ids = $this->api->send_post($data_to_send, 'recs' );
        if($received_ids == false || $received_ids == []){
            Recommender_WC_Log_Handler::logWarning('Recommender_Product_Displayer didnt recieve products from API');
            return;
        }
        Recommender_WC_Log_Handler::logDebug('Recommended product IDs received from API: ' . json_encode($received_ids));
        $this->set_products_for_display($received_ids);
        add_filter( 'woocommerce_related_products', array( $this, 'display_my_related_products') );
        woocommerce_related_products( apply_filters( 'woocommerce_output_related_products_args', $args ) );
    }
}
