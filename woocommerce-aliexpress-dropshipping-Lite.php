<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              	cedcommerce.com
 * @since            	1.0.0
 * @package           	CedWad
 *
 * @wordpress-plugin
 * Plugin Name:      	 Woocommerce Aliexpress Dropshipping Lite
 * Plugin URI:       	 cedcommerce.com
 * Description:      	 CedCommerce Aliexpress Dropshipping extension allows you to import products from Aliexpress.com and get started with Dropshipping. 
 *Requires at least: 	 4.4
 *Tested up to:		  	 4.9
 *WC requires at least:	 3.0.0
 *WC tested up to:    	 3.4.3
 * Version:              2.0.0
 * Author:               CedCommerce
 * Author URI:           https://cedcommerce.com
 * Text Domain:          CedWad
 * Domain Path:          /language
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-CedWad-activator.php
 */
function activate_CedWad() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-CedWad-activator.php';
	CedWad_Activator::activate();
}



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-CedWad.php';

/**
 * The core plugin file consist the core funstions of this plugin,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/CedWad_core_functions.php';

if(ced_wad_check_woocommerce_active()){

	register_activation_hook( __FILE__, 'activate_CedWad' );
	run_CedWad();
}
else
{
	add_action( 'admin_init', 'ced_plugin_deactivate' );

	function ced_plugin_deactivate(){

		// add_action( 'admin_notices', 'deactivate_ced_wad_woo_missing' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action('admin_notices', 'ced_wad_woo_missing_notice' );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

	}
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_CedWad() {

	$plugin = new CedWad();
	$plugin->run();

}
register_deactivation_hook( __FILE__, 'deactivate_CedWad' );
// run_CedWad();
