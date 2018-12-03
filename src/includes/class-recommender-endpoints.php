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
     * Register the routes for the objects of the controller.
     *
     * @since 0.4.0
     */
    public function register_routes()
    {
        $version = '1';
        $namespace = 'recommender/v' . $version;
        $base = 'sync';

        register_rest_route( $namespace, '/' . $base . '/' . 'products', array(
            'methods' => 'GET',
            'callback' => array( $this, 'sync_products' ),
        ) );

        register_rest_route( $namespace, '/' . $base . '/' . 'logs', array(
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