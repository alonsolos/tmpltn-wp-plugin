<?php

/**
 * Fired during plugin activation
 *
 * @link       templeton.com.pe
 * @since      1.0.0
 *
 * @package    Tmpltn_Plugin
 * @subpackage Tmpltn_Plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Tmpltn_Plugin
 * @subpackage Tmpltn_Plugin/includes
 * @author     Alonso Lavado <alon.laob@gmail.com>
 */
class Tmpltn_Plugin_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		global $jal_db_version;
		$table_name = $wpdb->prefix . 'tmpltn_snapshot';
		$charset_collate = $wpdb->get_charset_collate();
	
		$sql = "CREATE TABLE $table_name (
					id INT NOT NULL AUTO_INCREMENT,
					content VARCHAR(80000) NOT NULL,
					created_time DATETIME NOT NULL,
					PRIMARY KEY (id) )";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	
		add_option( 'jal_db_version', $jal_db_version );
	}
}
