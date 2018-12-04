<?php

/**
 * Handles custom endpoints for this plugin (Wordpress REST API)
 *
 * This class defines all code necessary to create and handle custom endpoints
 *
 * @since      0.4.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Lauri Leiten <leitenlauri@gmail.com>
 */

class Recommender_Endpoints extends WP_REST_Controller {

    /**
     * Stores the base part of the endpoint
     *
     * @since    0.4.5
     * @access   private
     * @var      string $namespace Stores the base part of the endpoint
     */
    private static $base = 'recommender/v1';

    /**
     * Stores the route part of the product sync endpoint
     *
     * @since    0.4.5
     * @access   private
     * @var      string $product_sync_endpoint Stores the route value for products
     */
    private static $product_route = '/sync/products';

    /**
     * Stores the route part of the log sync endpoint
     *
     * @since    0.4.5
     * @access   private
     * @var      string $log_sync_endpoint Stores the route value for logs
     */
    private static $log_route = '/sync/logs';

    /**
     * Getter for static variable $base
     *
     * @since  0.4.5
     * @access public
     * @return string
     */
    public static function getBase(): string
    {
        return self::$base;
    }

    /**
     * Getter for static variable $product_route
     *
     * @since  0.4.5
     * @access public
     * @return string
     */
    public static function getProductRoute(): string
    {
        return self::$product_route;
    }

    /**
     * Getter for static variable $log_route
     *
     * @since  0.4.5
     * @access public
     * @return string
     */
    public static function getLogRoute(): string
    {
        return self::$log_route;
    }

    /**
     * Get REST URL for products
     *
     * @since  0.4.5
     * @access public
     * @return string
     */
    public static function getProductURL(): string
    {
        $url = Recommender_Endpoints::getBase() . Recommender_Endpoints::getProductRoute();
        return rest_url($url);
    }

    /**
     * Get REST URL for logs
     *
     * @since  0.4.5
     * @access public
     * @return string
     */
    public static function getLogURL(): string
    {
        $url = Recommender_Endpoints::getBase() . Recommender_Endpoints::getLogRoute();
        return rest_url($url);
    }

    /**
     * Register the routes for the objects of the controller.
     *
     * @since 0.4.0
     */
    public function register_routes()
    {
        $base = Recommender_Endpoints::getBase();
        $product_route = Recommender_Endpoints::getProductRoute();
        $log_route = Recommender_Endpoints::getLogRoute();

        register_rest_route( $base, $product_route, array(
            'methods' => 'GET',
            'callback' => array( $this, 'sync_products' ),
        ) );

        register_rest_route( $base, $log_route, array(
            'methods' => 'GET',
            'callback' => array( $this, 'sync_logs' ),
        ) );
    }

    /**
     * Start the process of syncing products
     *
     * @since 0.4.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function sync_products( $request ) {
        //Recommender_Syncer::get_instance()->sync_catalog();
        return new WP_REST_Response( array(), 200 );
    }

    /**
     * Start the process of syncing logs
     *
     * @since 0.4.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function sync_logs( $request ) {
        //Recommender_Syncer::get_instance()->sync_logs();
        return new WP_REST_Response( array(), 200 );
    }
}