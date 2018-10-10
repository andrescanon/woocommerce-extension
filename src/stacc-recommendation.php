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

if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 */
define('PLUGIN_NAME_VERSION', '0.1.0');

/**
 * The code that runs during plugin activation.
 */
function activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-activator.php';
    Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-deactivator.php';
    Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate');
register_deactivation_hook(__FILE__, 'deactivate');

/**
 * The core plugin class that is used to define admin-specific hooks
 */
require plugin_dir_path(__FILE__) . 'includes/class-recommender.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_recommender()
{

    $plugin = new Recommender();
    $plugin->run();

}

run_recommender();