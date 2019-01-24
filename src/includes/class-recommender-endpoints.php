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
    private static $base = 'recommender/v2';

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
     * Register the routes for the objects of the controller.
     *
     * @since 0.4.0
     */
    public function register_routes()
    {
        $base = Recommender_Endpoints::getBase();
        $product_route = Recommender_Endpoints::getProductRoute() . '([&]?)';
        $log_route = Recommender_Endpoints::getLogRoute() . '([&]?)';

        register_rest_route( $base, $product_route, array(
            'methods'  => 'GET',
            'callback' => array( $this, 'sync_products' ),
	        'args'     => array(
	        	'h',
		        't'
	        )
        ) );

        register_rest_route( $base, $log_route, array(
            'methods'  => 'GET',
            'callback' => array( $this, 'sync_logs' ),
            'args'     => array(
	            'h',
	            't'
            )
        ) );
    }

    /**
     * Start the process of syncing products
     *
     * @since 0.6.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function sync_products( $request ) {
	    $handle = $this->handle_params("products", $request);
	    if (!is_bool($handle))
		    return $handle;

	    Recommender_WC_Log_Handler::get_instance()::logNotice("Product syncing started!");
	    Recommender_Syncer::get_instance()->sync_products();
	    Recommender_WC_Log_Handler::get_instance()::logNotice("Product syncing done!");
	    return new WP_REST_Response( array(), 200 );
    }

    /**
     * Start the process of syncing logs
     *
     * @since 0.6.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function sync_logs( $request ) {
	    $handle = $this->handle_params("logs", $request);
	    if (!is_bool($handle) || !$handle)
	    	return $handle;

	    Recommender_WC_Log_Handler::get_instance()::logNotice("Log syncing started!");
	    $success = Recommender_Syncer::get_instance()->sync_logs();
	    if (!$success)
        {
            Recommender_WC_Log_Handler::get_instance()::logError("Log syncing failed!");
            return new WP_REST_Response( array(), 500 );
        }
        Recommender_WC_Log_Handler::get_instance()::logNotice("Log syncing done!");
        return new WP_REST_Response( array(), 200 );
    }

	/**
	 * Handles the parameters of the request
	 *
	 * @since 0.6.0
	 * @param string $type The type of sync request
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|boolean
	 */
	private function handle_params( $type, $request ) {
		$h = $request->get_param('h');
		$t = $request->get_param('t');

		if ($h == null)
			$h = $request->get_param('?h');

		// Check if both parameters are set
		if ($h == null || $t == null)
		{
			Recommender_WC_Log_Handler::get_instance()::logError($type . " syncing error: param missing; h = " . $h . "; t = " . $t);
			return new WP_REST_Response( array("Parameters not set!"), 418 );
		}

		// Check if hash is correct
		$shop_id = get_option('shop_id');
		$key = get_option('api_key');
		$ourHash = hash("sha256", $shop_id . $key);
		
		if (!hash_equals($h, $ourHash))
		{
			Recommender_WC_Log_Handler::get_instance()::logError($type . " syncing error: unauthorized access");
			return new WP_REST_Response( array("Failure to authenticate!"), 418 );
		}

		// Check if timestamp is numeric
		if (!is_numeric($t))
		{
			Recommender_WC_Log_Handler::get_instance()::logError($type . " syncing error: timestamp isn't numeric");
			return new WP_REST_Response( array("Timestamp isn't valid!"), 418 );
		}

		return true;
	}

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
}
