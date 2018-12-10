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
 * @author     Martin Jürgel <martin457345@gmail.com>
 */
class Recommender_Admin
{
    /**
     * The current version of the plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string $version The current version of the plugin.
     */
    private $version;
    /**
     * The maximum number of related products to be displayed.
     *
     * @since      0.6.0
     * @access     private
     * @var        int  $products_limit The maximum number of related products to be displayed.
     */
    private $products_limit = 12;
    /**
     * The maximum number of columns related products can be arranged in.
     *
     * @since      0.6.0
     * @access     private
     * @var        int  $columns_limit  The maximum number of columns related products can be arranged in.
     */
    private $columns_limit = 6;
    /**
     * Initialize the class and set its properties.
     *
     * @since      0.1.0
     */
    public function __construct()
    {
        $this->version = PLUGIN_NAME_VERSION;
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

        //Settings for connecting to the API
        register_setting('recommender_options', 'shop_id', array('sanitize_callback'  => 'sanitize_text_field' ));
        register_setting('recommender_options', 'api_key', array('sanitize_callback'  => 'sanitize_text_field' ));

        //Settings for enabling boxes in different parts of the store
        register_setting('box_options', 'woocommerce_before_single_product_summary', array('sanitize_callback'  => array( $this, 'recommender_sanitize_checkbox' )));
        register_setting('box_options', 'woocommerce_after_single_product_summary', array('sanitize_callback'  => array( $this, 'recommender_sanitize_checkbox' )));
        register_setting('box_options', 'woocommerce_before_shop_loop', array('sanitize_callback'  => array( $this, 'recommender_sanitize_checkbox' )));
        register_setting('box_options', 'woocommerce_after_shop_loop', array('sanitize_callback'  => array( $this, 'recommender_sanitize_checkbox' )));
        register_setting('box_options', 'woocommerce_before_cart', array('sanitize_callback'  => array( $this, 'recommender_sanitize_checkbox' )));
        register_setting('box_options', 'woocommerce_after_cart_table', array('sanitize_callback'  => array( $this, 'recommender_sanitize_checkbox' )));
        register_setting('box_options', 'woocommerce_after_cart_totals', array('sanitize_callback'  => array( $this, 'recommender_sanitize_checkbox' )));
        register_setting('box_options', 'woocommerce_after_cart', array('sanitize_callback'  => array( $this, 'recommender_sanitize_checkbox' )));

        //Settings for selecting the number of products to be displayed per box
        register_setting('box_options', 'woocommerce_before_single_product_summary_rows', array('sanitize_callback'  => array( $this, 'recommender_row_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_single_product_summary_rows', array('sanitize_callback'  => array( $this, 'recommender_row_sanitizer' )));
        register_setting('box_options', 'woocommerce_before_shop_loop_rows', array('sanitize_callback'  => array( $this, 'recommender_row_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_shop_loop_rows', array('sanitize_callback'  => array( $this, 'recommender_row_sanitizer' )));
        register_setting('box_options', 'woocommerce_before_cart_rows', array('sanitize_callback'  => array( $this, 'recommender_row_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_cart_table_rows', array('sanitize_callback'  => array( $this, 'recommender_row_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_cart_totals_rows', array('sanitize_callback'  => array( $this, 'recommender_row_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_cart_rows', array('sanitize_callback'  => array( $this, 'recommender_row_sanitizer' )));

        //Settings for selecting the number of columns to arrange products in for each box
        register_setting('box_options', 'woocommerce_before_single_product_summary_columns', array('sanitize_callback'  => array( $this, 'recommender_column_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_single_product_summary_columns', array('sanitize_callback'  => array( $this, 'recommender_column_sanitizer' )));
        register_setting('box_options', 'woocommerce_before_shop_loop_columns', array('sanitize_callback'  => array( $this, 'recommender_column_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_shop_loop_columns', array('sanitize_callback'  => array( $this, 'recommender_column_sanitizer' )));
        register_setting('box_options', 'woocommerce_before_cart_columns', array('sanitize_callback'  => array( $this, 'recommender_column_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_cart_table_columns', array('sanitize_callback'  => array( $this, 'recommender_column_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_cart_totals_columns', array('sanitize_callback'  => array( $this, 'recommender_column_sanitizer' )));
        register_setting('box_options', 'woocommerce_after_cart_columns', array('sanitize_callback'  => array( $this, 'recommender_column_sanitizer' )));

        //Settings for assigning an ID to each box
        register_setting('box_options', 'woocommerce_before_single_product_summary_id', array('sanitize_callback'  => 'sanitize_text_field' ));
        register_setting('box_options', 'woocommerce_after_single_product_summary_id', array('sanitize_callback'  => 'sanitize_text_field' ));
        register_setting('box_options', 'woocommerce_before_shop_loop_id', array('sanitize_callback'  => 'sanitize_text_field' ));
        register_setting('box_options', 'woocommerce_after_shop_loop_id', array('sanitize_callback'  => 'sanitize_text_field' ));
        register_setting('box_options', 'woocommerce_before_cart_id', array('sanitize_callback'  => 'sanitize_text_field' ));
        register_setting('box_options', 'woocommerce_after_cart_table_id', array('sanitize_callback'  => 'sanitize_text_field' ));
        register_setting('box_options', 'woocommerce_after_cart_totals_id', array('sanitize_callback'  => 'sanitize_text_field' ));
        register_setting('box_options', 'woocommerce_after_cart_id', array('sanitize_callback'  => 'sanitize_text_field' ));
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
        $api = new Recommender_API();
        if ( isset( $_GET['settings-updated'] ) ) {
            if ($api->has_connection()) {
                add_settings_error('recommender_messages', 'recommender_api_connection', __('API Online', 'recommender'), 'updated');
                $data = [
                    'log_sync_url' => Recommender_Endpoints::getLogURL() . '&',
                    'product_sync_url' => Recommender_Endpoints::getProductURL() . '&'
                ];
                if ($api->send_post($data, 'creds')){
                    add_settings_error('recommender_messages', 'recommender_message', __('Settings Saved - Plugin Setup Successful', 'recommender'), 'updated');
                    update_option( 'cred_check_failed', false);
                    Recommender_WC_Log_Handler::logDebug('Settings Saved');
                } else {
                    add_settings_error('recommender_messages', 'recommender_message', __('Validation Error - Plugin Setup Failed - Check your Shop ID and API Key', 'recommender'), 'error');
                    update_option( 'cred_check_failed', true);
                    Recommender_WC_Log_Handler::logError('Validation Error');
                }
            } else {
                add_settings_error('recommender_messages', 'recommender_api_connection', __('API Offline - Plugin Setup Failed', 'recommender'), 'error');
                update_option( 'cred_check_failed', true);
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
        if ( isset( $_GET['settings-updated'] ) )
        {
            if ( get_settings_errors( 'error_on_column_validation' ))
                add_settings_error( 'recommender_messages', 'recommender_column_validation', __('Invalid value(s) replaced with default value 2. You can only enter numbers in range 1 - 6.', 'recommender'), 'updated' );
            if ( get_settings_errors( 'error_on_products_validation' ))
                add_settings_error( 'recommender_messages', 'recommender_products_validation', __('Invalid value(s) replaced with default value 2. You can only enter numbers in range 1 - 12.', 'recommender'), 'updated' );
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
                    </tr>
                    <tr valign="center">
                        <th scope="row" style="font-size: large">Single product view</th>
                    </tr>
                    <tr valign="top">
                        <th>ID</th>
                        <th>Box placement</th>
                        <th>Enabled</th>
                        <th>Products</th>
                        <th>Columns</th>
                    </tr>
                    <tr valign="top">
                        <th><input type="text" name="woocommerce_before_single_product_summary_id" value="<?php echo esc_attr(get_option('woocommerce_before_single_product_summary_id', $default = '1')); ?>"/></th>
                        <th scope="row">Before product summary</th>
                        <th><input type="checkbox" name="woocommerce_before_single_product_summary" value="10" <?php checked( 10 == get_option( 'woocommerce_before_single_product_summary' ) ); ?>"/></th>
                        <th scope="row"><input type="number" name="woocommerce_before_single_product_summary_rows" value="<?php echo esc_attr(get_option('woocommerce_before_single_product_summary_rows', $default = 2)); ?>" min="1" max=<?php echo $this->products_limit?> </th>
                        <th scope="row"><input type="number" name="woocommerce_before_single_product_summary_columns" value="<?php echo esc_attr(get_option('woocommerce_before_single_product_summary_columns', $default = 2)); ?>" min="1" max=<?php echo $this->columns_limit?> </th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr valign="top">
                        <th><input type="text" name="woocommerce_after_single_product_summary_id" value="<?php echo esc_attr(get_option('woocommerce_after_single_product_summary_id', $default = '2')); ?>"/></th>
                        <th scope="row">After product summary</th>
                        <th><input type="checkbox" name="woocommerce_after_single_product_summary" value="25" <?php checked( 25 == get_option( 'woocommerce_after_single_product_summary' ) ); ?>"/></th>
                        <th scope="row"><input type="number" name="woocommerce_after_single_product_summary_rows" value="<?php echo esc_attr(get_option('woocommerce_after_single_product_summary_rows', $default = 2)); ?>" min="1" max=<?php echo $this->products_limit?> </th>
                        <th scope="row"><input type="number" name="woocommerce_after_single_product_summary_columns" value="<?php echo esc_attr(get_option('woocommerce_after_single_product_summary_columns', $default = 2)); ?>" min="1" max=<?php echo $this->columns_limit?> </th>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-size: large">Multiple product view</th>
                    </tr>
                    <tr valign="top">
                        <th>ID</th>
                        <th>Box placement</th>
                        <th>Enabled</th>
                        <th>Products</th>
                        <th>Columns</th>
                    </tr>
                    <tr valign="top">
                        <th><input type="text" name="woocommerce_before_shop_loop_id" value="<?php echo esc_attr(get_option('woocommerce_before_shop_loop_id', $default = '3')); ?>"/></th>
                        <th scope="row">Before products</th>
                        <th><input type="checkbox" name="woocommerce_before_shop_loop" value="10" <?php checked( 10 == get_option( 'woocommerce_before_shop_loop' ) ); ?>"/></th>
                        <th scope="row"><input type="number" name="woocommerce_before_shop_loop_rows" value="<?php echo esc_attr(get_option('woocommerce_before_shop_loop_rows', $default = 2)); ?>" min="1" max=<?php echo $this->products_limit?> </th>
                        <th scope="row"><input type="number" name="woocommerce_before_shop_loop_columns" value="<?php echo esc_attr(get_option('woocommerce_before_shop_loop_columns', $default = 2)); ?>" min="1" max=<?php echo $this->columns_limit?> </th>
                    </tr>
                    <tr valign="top">
                        <th><input type="text" name="woocommerce_after_shop_loop_id" value="<?php echo esc_attr(get_option('woocommerce_after_shop_loop_id', $default = '4')); ?>"/></th>
                        <th scope="row">After products</th>
                        <th><input type="checkbox" name="woocommerce_after_shop_loop" value="10" <?php checked( 10 == get_option( 'woocommerce_after_shop_loop' ) ); ?>"/></th>
                        <th scope="row"><input type="number" name="woocommerce_after_shop_loop_rows" value="<?php echo esc_attr(get_option('woocommerce_after_shop_loop_rows', $default = 2)); ?>" min="1" max=<?php echo $this->products_limit?> </th>
                        <th scope="row"><input type="number" name="woocommerce_after_shop_loop_columns" value="<?php echo esc_attr(get_option('woocommerce_after_shop_loop_columns', $default = 2)); ?>" min="1" max=<?php echo $this->columns_limit?> </th>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-size: large">Shopping cart</th>
                    </tr>
                    <tr valign="top">
                        <th>ID</th>
                        <th>Box placement</th>
                        <th>Enabled</th>
                        <th>Products</th>
                        <th>Columns</th>
                    </tr>
                    <tr valign="top">
                        <th><input type="text" name="woocommerce_before_cart_id" value="<?php echo esc_attr(get_option('woocommerce_before_cart_id', $default = '5')); ?>"/></th>
                        <th scope="row">Before cart</th>
                        <th><input type="checkbox" name="woocommerce_before_cart" value="10" <?php checked( 10 == get_option( 'woocommerce_before_cart' ) ); ?>"/></th>
                        <th scope="row"><input type="number" name="woocommerce_before_cart_rows" value="<?php echo esc_attr(get_option('woocommerce_before_cart_rows', $default = 2)); ?>" min="1" max=<?php echo $this->products_limit?> </th>
                        <th scope="row"><input type="number" name="woocommerce_before_cart_columns" value="<?php echo esc_attr(get_option('woocommerce_before_cart_columns', $default = 2)); ?>" min="1" max=<?php echo $this->columns_limit?> </th>
                    </tr>
                    <tr valign="top">
                        <th><input type="text" name="woocommerce_after_cart_table_id" value="<?php echo esc_attr(get_option('woocommerce_after_cart_table_id', $default = '6')); ?>"/></th>
                        <th scope="row">After cart table</th>
                        <th><input type="checkbox" name="woocommerce_after_cart_table" value="10" <?php checked( 10 == get_option( 'woocommerce_after_cart_table' ) ); ?>"/></th>
                        <th scope="row"><input type="number" name="woocommerce_after_cart_table_rows" value="<?php echo esc_attr(get_option('woocommerce_after_cart_table_rows', $default = 2)); ?>" min="1" max=<?php echo $this->products_limit?> </th>
                        <th scope="row"><input type="number" name="woocommerce_after_cart_table_columns" value="<?php echo esc_attr(get_option('woocommerce_after_cart_table_columns', $default = 2)); ?>" min="1" max=<?php echo $this->columns_limit?> </th>
                    </tr>
                    <tr valign="top">
                        <th><input type="text" name="woocommerce_after_cart_totals_id" value="<?php echo esc_attr(get_option('woocommerce_after_cart_totals_id', $default = '7')); ?>"/></th>
                        <th scope="row">After cart totals</th>
                        <th><input type="checkbox" name="woocommerce_after_cart_totals" value="10" <?php checked( 10 == get_option( 'woocommerce_after_cart_totals' ) ); ?>"/></th>
                        <th scope="row"><input type="number" name="woocommerce_after_cart_totals_rows" value="<?php echo esc_attr(get_option('woocommerce_after_cart_totals_rows', $default = 2)); ?>" min="1" max=<?php echo $this->products_limit?> </th>
                        <th scope="row"><input type="number" name="woocommerce_after_cart_totals_columns" value="<?php echo esc_attr(get_option('woocommerce_after_cart_totals_columns', $default = 2)); ?>" min="1" max=<?php echo $this->columns_limit?> </th>
                    </tr>
                    <tr valign="top">
                        <th><input type="text" name="woocommerce_after_cart_id" value="<?php echo esc_attr(get_option('woocommerce_after_cart_id', $default = '8')); ?>"/></th>
                        <th scope="row">After cart</th>
                        <th><input type="checkbox" name="woocommerce_after_cart" value="10" <?php checked( 10 == get_option( 'woocommerce_after_cart' ) ); ?>"/></th>
                        <th scope="row"><input type="number" name="woocommerce_after_cart_rows" value="<?php echo esc_attr(get_option('woocommerce_after_cart_rows', $default = 2)); ?>" min="1" max=<?php echo $this->products_limit?> </th>
                        <th scope="row"><input type="number" name="woocommerce_after_cart_columns" value="<?php echo esc_attr(get_option('woocommerce_after_cart_columns', $default = 2)); ?>" min="1" max=<?php echo $this->columns_limit?> </th>
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
     * Wrapper function to sanitize the 'columns' field
     *
     * @since 1.0.0
     * @param $data Input data to be sanitized
     * @return string   Sanitized data
     */
    public function recommender_column_sanitizer($data)
    {
        return $this->recommender_validate_number( $data, false );
    }

    /**
     * Wrapper function to sanitize the 'products' field
     *
     * @since 1.0.0
     * @param $data Input data to be sanitized
     * @return string   Sanitized data
     */
    public function recommender_row_sanitizer( $data )
    {
        return $this->recommender_validate_number( $data, true );
    }

    /**
     * Function to sanitize numeric values for the product and column fields
     *
     * @since 1.0.0
     * @param $data Input data to be sanitized
     * @param $row  boolean true called by recommender_row_sanitizer?
     * @return string   Sanitized data
     */
    public function recommender_validate_number( $data, $row )
    {
        $limit = $row ? $this->products_limit : $this->columns_limit;
        if ( is_numeric($data) && $data >= 1 && $data <= $limit )
        {
            return sanitize_text_field( $data );
        }
        add_settings_error( $row ? 'error_on_products_validation' : 'error_on_column_validation', '', '');
        return '2';
    }

    /**
     * Function to sanitize checkboxes
     *
     * @since 1.0.0
     * @param $input    Input to be sanitized
     * @return mixed    If checkbox ticked, input, otherwise false
     */
    public function recommender_sanitize_checkbox( $input )
    {
        return ( isset( $input ) ? $input : false );
    }
}