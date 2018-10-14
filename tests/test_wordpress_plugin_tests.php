<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_WordPress_Plugin_Tests extends WP_UnitTestCase{
  
	static function setUpBeforeClass()
	{
		activate_plugin("woocommerce/woocommerce.php");
		activate_plugin("woocommerce-extension/stacc-recommendation.php");
	}
	
	/**
	 * Run a simple test to ensure that the tests are running
	 */
	function test_tests() {

		$this->assertTrue( true );

	}

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function test_plugin_activated() {

		$this->assertTrue( is_plugin_active( 'woocommerce-extension/stacc-recommendation.php' ) );

	}

    /**
     * Tests if adding and getting options works.
     */
	function test_options(){
	    $api = "api_key";
	    $shop = "shop_id";
        $value = "123";

	    if(!(get_option($api) === false)){
	        delete_option($api);
        }
        if(!(get_option($shop) === false)){
            delete_option($shop);
        }

        add_option($api, $value);
        $this->assertTrue($value === get_option($api));

        add_option($shop, $value);
        $this->assertTrue($value === get_option($shop));
    }

}
