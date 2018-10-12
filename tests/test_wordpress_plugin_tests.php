<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_WordPress_Plugin_Tests extends WP_UnitTestCase{

	function __construct()
	{
		activate_plugin("/tmp/wordpress/build/wp-content/plugins/woocommerce-3.4.6/woocommerce.php");
		activate_plugin("/tmp/wordpress/build/wp-content/plugins/woocommerce-extension/stacc-recommendation.php");
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

}
