<?php

/**
 * Fired during plugin activation
 *
 * @link       cedcommerce.com
 * @since      1.0.0
 *
 * @package    woocommerce-aliexpress-dropshipping
 * @subpackage CedWad/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    woocommerce-aliexpress-dropshipping
 * @subpackage CedWad/includes
 * @author     CedCommerce <plugins@cedcommerce.com>
 */
class CedWad_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::createTables();
	}

	/**
	 * Tables necessary for CedWad.
	 * 
	 * @since 1.0.0
	 */
	private static function createTables(){
		
		if(defined('CedWad_PREFIX')){
            global $wpdb;
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $prefix = $wpdb->prefix . CedWad_PREFIX;
            $table_name = "{$prefix}filters";
            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                $create_profile = "CREATE TABLE {$prefix}filters (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(255) NOT NULL DEFAULT '',filter_data longtext DEFAULT NULL,PRIMARY KEY (id));";
                dbDelta( $create_profile );
            }
            $table_name = "{$prefix}bunches";
            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

                $create_bunch = "CREATE TABLE {$prefix}bunches (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,`filter-id` BIGINT(20) NOT NULL,`manually_created` VARCHAR(20),`blast` BIGINT(20),products longtext DEFAULT NULL,PRIMARY KEY (id));";
                dbDelta( $create_bunch );
            }

            $table_name = "{$prefix}blast_settings";
            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

                $create_blast_settings = "CREATE TABLE {$prefix}blast_settings (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(100) NULL,`settings` TEXT DEFAULT NULL,PRIMARY KEY (id));";
                dbDelta( $create_blast_settings );
            }
        }
    }

}
