<?php

/**
 * @link              stacc.ee
 * @since             0.1.0
 * @package           Recommendations
 *
 * @wordpress-plugin
 * Plugin Name:       Recommendations for WooCommerce
 * Plugin URI:        stacc.ee
 * Description:       Displays personalized product recommendations to the user.
 * Version:           0.1.0
 * Author:            STACC
 * Author URI:        stacc.ee
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'PLUGIN_NAME_VERSION', '0.1.0' );

/**
 * The code that runs during plugin activation.
 */
function activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate' );
register_deactivation_hook( __FILE__, 'deactivate' );

/**
 * The code that runs during every time admin area is loaded
 */

function onLoad() {
	if ( ( !is_plugin_active("woocommerce/woocommerce.php") ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}
}

add_action( 'admin_init', 'onLoad');