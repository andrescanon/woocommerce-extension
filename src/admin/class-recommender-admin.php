<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      0.1.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Lauri Leiten <leitenlauri@gmail.com>
 * @author       Stiivo Siider <stiivosiider@gmail.com>
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
     * Registers the options for the menu
     *
     * @since      0.1.0
     * @access     widget
     */
    public function admin_init()
    {
        register_setting('recommender_options', 'shop_id');
        register_setting('recommender_options', 'api_key');
    }

    /**
     * Registers the menu for the admin area
     * Adds the menu under the WooCommerce settings panel
     *
     * @since      0.1.0
     * @access     widget
     */
    public function admin_menu()
    {
        add_submenu_page(
            'woocommerce',
            'Recommender Options',
            'Recommender Options',
            'manage_options',
            'stacc_recommender',
            array($this, 'admin_recommender_main')
        );
    }

    /**
     * Creates the page for options page
     *
     * @since      0.1.0
     * @access     widget
     */
    public function admin_recommender_main()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('recommender_options');
                // output setting sections and their fields
                do_settings_sections('stacc_recommender');
                ?>
                <table class="form-table">
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
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }
}