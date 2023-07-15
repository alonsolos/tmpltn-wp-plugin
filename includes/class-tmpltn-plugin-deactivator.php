<?php

/**
 * Fired during plugin deactivation
 *
 * @link       templeton.com.pe
 * @since      1.0.0
 *
 * @package    Tmpltn_Plugin
 * @subpackage Tmpltn_Plugin/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Tmpltn_Plugin
 * @subpackage Tmpltn_Plugin/includes
 * @author     Alonso Lavado <alon.laob@gmail.com>
 */
class Tmpltn_Plugin_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;
		$table_name = $wpdb->prefix . "tmpltn_snapshot"; 

		$sql = "DROP TABLE IF EXISTS $table_name";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}
