<?php

/**
 * The event catching functionality of the plugin.
 *
 * Defines the plugin name, version and hooks, filter for catching events
 *
 * @since      0.2.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Martin Jürgel <martin457345@gmail.com>
 */

class Event_Catcher
{
    /**
     * The ID of this plugin.
     *
     * @since      0.2.0
     * @access     private
     * @var        string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since      0.2.0
     * @access     private
     * @var        string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since      0.2.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Callback for catching search events.
     *
     * @since       0.2.0
     * @param       WP_Query $query Object containing query data.
     * @return      WP_Query $query Object containing query data.
     */
    public function filter_woocommerce_search($query)
    {
        if ( $query->is_search )
        {
            $query_string = get_search_query(true);
            //$args = $query->query;
            //TODO retrieve filters
            $data = [
                'stacc_id' => get_current_user_id(),
                'query' => $query_string,
                'filters' => [],
                'website' => get_site_url(),
                'properties' => []
            ];
            Recommender_API::get_instance()::send_event($data, 'search');
        }
        return $query;
    }

    /**
     * Callback for catching add to cart events.
     *
     * @since      0.2.0
     * @param      string $cart_item_key The cart item key.
     * @param      int $product_id ID of the product being added to cart.
     * @param      int $quantity Quantity selected by the user.
     * @param      int $variation_id The variation ID.
     * @param      WC_Product_Variation $variation The variation.
     * @param      array $cart_item_data Cart item data.
     */
    public function action_woocommerce_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data )
    {
        $properties =  [
            'categories' => wc_get_product_category_list($product_id),
            'stock_status' => wc_get_product($product_id)->get_stock_status()
        ];
        $data = [
            'item_id' => $product_id,
            'stacc_id' => get_current_user_id(),
            'website' => get_site_url(),
            'properties' => $properties
        ];
        Recommender_API::get_instance()::send_event($data, 'add');
    }

    /**
     * Callback for catching product view events.
     *
     * @since      0.2.0
     */
    public function action_woocommerce_single_product_summary()
    {
        global $product;
        $id = $product->get_id();

        $properties =  [
            'categories' => wc_get_product_category_list($id),
            'stock_status' => $product->get_stock_status()
        ];

        $data = [
            'item_id' => $id,
            'stacc_id' => get_current_user_id(),
            'website' => get_site_url(),
            'properties' => $properties
        ];
        Recommender_API::get_instance()::send_event($data, 'view');
    }

    /**
     * Callback for catching payment complete events.
     *
     * @since      0.2.0
     * @param      int $order_id Order ID.
     */
    public function action_woocommerce_payment_complete( $order_id )
    {
        $order = wc_get_order( $order_id );
        $items = $order->get_items();
        $item_list = array();
        foreach ( $items as $item )
        {
            $product_id = $item->get_id();
            $product_quantity = $item->get_quantity();
            $price = $order->get_item_total($item);
            $item_arr =  [
                'item_id' => $product_id,
                'quantity' => $product_quantity,
                'price' => $price
            ];
            array_push($item_list, $item_arr);
        }
        $currency = $order->get_currency();


        $data = [
            'stacc_id' => get_current_user_id(),
            'item_list' => $item_list,
            'website' => get_site_url(),
            'currency' => $currency,
            'properties' => []
        ];
        Recommender_API::get_instance()::send_event($data, 'purchase');
    }
}