<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two callbacks for creating the options page
 *
 * @since      0.1.0
 * @package    Recommendations
 * @subpackage Recommendations/admin
 * @author     Lauri Leiten <leitenlauri@gmail.com>
 * @author     Stiivo Siider <stiivosiider@gmail.com>
 * @author     Hannes Saariste <hannes.saariste@gmail.com>
 */

class Recommender_Admin
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
     * Registers the options for the menu and checks whether WooCommerce is still active
     *
     * @since      0.1.0
     */
    public function recommender_admin_init()
    {
	    if (!is_plugin_active('woocommerce/woocommerce.php'))
		    deactivate_plugins('woocommerce-extension/stacc-recommendation.php');
	    register_setting('recommender_options', 'shop_id', array('sanitize_callback'  => array( $this, 'recommender_option_sanitizer' )));
	    register_setting('recommender_options', 'api_key', array('sanitize_callback'  => array( $this, 'recommender_option_sanitizer' )));
        register_setting('box_options', 'woocommerce_before_single_product_summary');
        register_setting('box_options', 'woocommerce_after_single_product_summary');
        register_setting('box_options', 'woocommerce_before_shop_loop');
        register_setting('box_options', 'woocommerce_after_shop_loop');
        register_setting('box_options', 'woocommerce_before_cart');
        register_setting('box_options', 'woocommerce_after_cart_table');
        register_setting('box_options', 'woocommerce_after_cart_totals');
        register_setting('box_options', 'woocommerce_after_cart');
        register_setting('box_options', 'disable_default_box');
    }

    /**
     * Adds the menu under the WooCommerce settings panel
     *
     * @since      0.1.0
     */
    public function recommender_admin_menu()
    {
        add_submenu_page(
            'woocommerce',
            'STACC Options',
            'STACC',
            'manage_options',
            'recommender_options',
            array($this, 'recommender_options_page')
        );
    }

    /**
     * Redirects the user to the appropriate page in the admin panel based on the tab that's currently active.
     *
     * @since      0.3.0
     */
    public function recommender_options_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'connect_to_api';

        if( $active_tab == 'connect_to_api' )
            $this->api_auth_page();
        else
            $this->box_preferences_page();
    }
  
    /**
     * Creates the page for API auth settings
     *
     * @since      0.1.0
     */
    public function api_auth_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if ( isset( $_GET['settings-updated'] ) ) {
            if (Recommender_API::get_instance()->has_connection()) {
                add_settings_error('recommender_messages', 'recommender_api_connection', __('API Online', 'recommender'), 'updated');
                $logurl = get_rest_url($path = 'recommender/v1/sync/logs');
                $producturl = get_rest_url($path = 'recommender/v1/sync/products');
                $data = [
                    'log_sync_url' => $logurl,
                    'product_sync_url' => $producturl
                ];
                if(Recommender_API::get_instance()->send_post($data, 'creds')){
                    add_settings_error('recommender_messages', 'recommender_message', __('Settings Saved', 'recommender'), 'updated');
                    Recommender_WC_Log_Handler::logDebug('Settings Saved');
                } else {
                    add_settings_error('recommender_messages', 'recommender_message', __('Settings Not Saved', 'recommender'), 'error');
                    update_option('shop_id', '');
                    update_option('api_key', '');
                    Recommender_WC_Log_Handler::logError('Validation Error - Settings not saved');
                }
            } else {
                add_settings_error('recommender_messages', 'recommender_api_connection', __('API Offline - Settings Not Saved', 'recommender'), 'error');
                update_option('shop_id', '');
                update_option('api_key', '');
                Recommender_WC_Log_Handler::logWarning('API Offline - Settings not saved');
            }
            settings_errors('recommender_messages');
        }

        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=recommender_options&tab=connect_to_api" class="nav-tab nav-tab-active">Connect to the API</a>
                <a href="?page=recommender_options&tab=box_preferences" class="nav-tab">Box Preferences</a>
            </h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('recommender_options');
                // output setting sections and their fields
                do_settings_sections('recommender_options');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Extension Version</th>
                        <td><?php echo $this->version; ?></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Shop ID</th>
                        <td><input type="text" name="shop_id" value="<?php echo esc_attr(get_option('shop_id')); ?>"/>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">API Key</th>
                        <td><input type="text" name="api_key" value="<?php echo esc_attr(get_option('api_key')); ?>"/>
                        </td>
                    </tr>
                </table>
                <?php
                // output save settings button
                submit_button('Confirm');
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Creates the page for recommender box preferences.
     *
     * @since      0.3.0
     */
    public function box_preferences_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error('recommender_messages', 'recommender_message', __('Settings Saved', 'recommender'), 'updated');
            settings_errors('recommender_messages');
        }
        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=recommender_options&tab=connect_to_api" class="nav-tab">Connect to the API</a>
                <a href="?page=recommender_options&tab=box_preferences" class="nav-tab nav-tab-active">Box Preferences</a>
            </h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('box_options');
                // output setting sections and their fields
                do_settings_sections('box_options');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Extension Version</th>
                        <td><?php echo $this->version; ?></td>
                        <th scope="row">Disable default box</th>
                        <td><input type="checkbox" name="disable_default_box" value="1" <?php checked( 1 == get_option( 'disable_default_box' ) ); ?>"/></td>
                    </tr>
                    <tr valign="center">
                        <th scope="row" style="font-size: large">Single product view</th>
                        <th scope="row"></th>
                        <th scope="row" style="font-size: large">Multiple product view</th>
                        <th scope="row"></th>
                        <th scope="row" style="font-size: large">Shopping cart</th>
                        <th scope="row"></th>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Before product summary</th>
                        <td><input type="checkbox" name="woocommerce_before_single_product_summary" value="10" <?php checked( 10 == get_option( 'woocommerce_before_single_product_summary' ) ); ?>"/></td>
                        <th scope="row">Before products</th>
                        <td><input type="checkbox" name="woocommerce_before_shop_loop" value="20" <?php checked( 20 == get_option( 'woocommerce_before_shop_loop' ) ); ?>"/></td>
                        <th scope="row">Before cart</th>
                        <td><input type="checkbox" name="woocommerce_before_cart" value="10" <?php checked( 10 == get_option( 'woocommerce_before_cart' ) ); ?>"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">After product summary</th>
                        <td><input type="checkbox" name="woocommerce_after_single_product_summary" value="25" <?php checked( 25 == get_option( 'woocommerce_after_single_product_summary' ) ); ?>"/></td>
                        <th scope="row">After products</th>
                        <td><input type="checkbox" name="woocommerce_after_shop_loop" value="10" <?php checked( 10 == get_option( 'woocommerce_after_shop_loop' ) ); ?>"/></td>
                        <th scope="row">After cart table</th>
                        <td><input type="checkbox" name="woocommerce_after_cart_table" value="10" <?php checked( 10 == get_option( 'woocommerce_after_cart_table' ) ); ?>"/></td>
                    </tr>
                    <tr valign="top">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <th scope="row">After cart totals</th>
                        <td><input type="checkbox" name="woocommerce_after_cart_totals" value="10" <?php checked( 10 == get_option( 'woocommerce_after_cart_totals' ) ); ?>"/></td>
                    </tr>
                    <tr valign="top">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <th scope="row">After cart</th>
                        <td><input type="checkbox" name="woocommerce_after_cart" value="10" <?php checked( 10 == get_option( 'woocommerce_after_cart' ) ); ?>"/></td>
                </table>
                <?php
                // output save settings button
                submit_button('Confirm');
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Function to sanitize the text field
     *
     * @param $data Input data that is to be sanitized
     * @return mixed Sanitized data
     */
    public function recommender_option_sanitizer($data)
    {
        return sanitize_text_field($data);
    }
}
