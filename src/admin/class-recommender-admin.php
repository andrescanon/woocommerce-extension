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
	    register_setting('recommender_options', 'shop_id', array('sanitize_callback'  => array( $this, 'recommender_option_validation' )));
	    register_setting('recommender_options', 'api_key', array('sanitize_callback'  => array( $this, 'recommender_option_validation' )));
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
            'STACC',
            'STACC',
            'manage_options',
            'recommender_options',
            array($this, 'recommender_options_page')
        );
    }

    /**
     * Creates the page for options page
     *
     * @since      0.1.0
     */
    public function recommender_options_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        if ( isset( $_GET['settings-updated'] ) ) {
            if(!get_settings_errors('errorOnValidation')) {
                $bool = Recommender_API::get_instance()->has_connection();
                if ($bool) {
                    add_settings_error('recommender_messages', 'recommender_api_connection', __('API Online', 'recommender'), 'updated');
                } else {
                    add_settings_error('recommender_messages', 'recommender_api_connection', __('API Offline', 'recommender'), 'updated');
                }
                add_settings_error('recommender_messages', 'recommender_message', __('Settings Saved', 'recommender'), 'updated');
                settings_errors('recommender_messages');
            } else {
                settings_errors('errorOnValidation');
            }
        }
        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('recommender_options');
                // output setting sections and their fields
                do_settings_sections('recommender_options');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Recommender Version</th>
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
     * Function to check if the input consists of only alphanumeric characters
     *
     * @param $data Input data that is to be validated
     * @return mixed Valid data
     */
    public function recommender_option_validation($data)
    {
        if (ctype_alnum($data)){
            return sanitize_text_field($data);
        } else {
            add_settings_error(
                'errorOnValidation',
                'validationError',
                'This field must contain only numbers and letters. Must not be empty.',
                'error');
        }
    }
}
