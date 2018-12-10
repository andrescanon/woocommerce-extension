<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Lauri Leiten <leitenlauri@gmail.com>
 */
class Recommender_Activator
{
    /**
     * Runs on activation
     * @since    0.1.0
     */
    public static function activate()
    {
	    if ((!is_plugin_active("woocommerce/woocommerce.php"))) {
            deactivate_plugins(plugin_basename(__FILE__));
            die("WooCommerce isn't active");
        }
	    add_option("shop_id", '');
	    add_option("api_key", '');
	    add_option("cred_check_failed", true);
    }
}

