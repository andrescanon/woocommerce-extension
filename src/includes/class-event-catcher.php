<?php

/**
 * The event catching functionality of the plugin.
 *
 * Defines the plugin name, version and hooks, filter for catching events
 *
 * @since      0.1.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Martin Jürgel <martin457345@gmail.com>
 */

class Event_Catcher
{

    /**
     * The ID of this plugin.
     *
     * @since      0.1.0
     * @access     private
     * @var        string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since      0.1.0
     * @access     private
     * @var        string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since      0.1.0
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
     * @since 0.1.0
     * @param       WP_Query $query Object containing query data.
     * @return      WP_Query $query Object containing query data.
     */
    public function filter_woocommerce_search($query)
    {
        if ( $query->is_search )
        {
            $args = implode(',', $query->query);
            error_log("Search, args: $args");
        }
        return $query;
    }

    /**
     * Callback for catching add to cart events.
     *
     * @since 0.1.0
     * @param $cart_item_key The cart item key.
     * @param $product_id ID of the product being added to cart.
     * @param $quantity Quantity selected by the user.
     * @param $variation_id The variation ID.
     * @param $variation The variation.
     * @param $cart_item_data Cart item data.
     */
    public function action_woocommerce_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data )
    {
        error_log("Add to cart, ID: $product_id");

    }

    /**
     * Callback for catching product view events.
     *
     * @since 0.1.0
     */
    public function action_woocommerce_single_product_summary()
    {
        global $product;
        $id = $product->get_id();
        error_log("Product view, ID: $id");
    }

    /**
     * Callback for catching payment complete events.
     *
     * @since 0.1.0
     * @param $order_id Order ID.
     */
    public function action_woocommerce_payment_complete( $order_id ) {
        error_log( "Payment complete, order ID: $order_id" );
    }
}