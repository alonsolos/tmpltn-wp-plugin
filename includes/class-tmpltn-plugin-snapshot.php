<?php

/**
 * Manages Snapshot
 *
 * @link       templeton.com.pe
 * @since      1.0.0
 *
 * @package    Tmpltn_Plugin
 * @subpackage Tmpltn_Plugin/includes
 */

/**
 * Manages Snapshot.
 *
 * This class defines all code necessary to insert and list snapshots.
 *
 * @since      1.0.0
 * @package    Tmpltn_Plugin
 * @subpackage Tmpltn_Plugin/includes
 * @author     Alonso Lavado <alon.laob@gmail.com>
 */
class Tmpltn_Plugin_Snapshot {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function insert_snapshot($content) {
		global $wpdb;
		$table_name = $wpdb->prefix . "tmpltn_snapshot"; 

		$now = new DateTime();
		if (!$wpdb->insert($table_name, array(
			"content" => $content,
			"created_time" => current_time('mysql', 1)
		))) {
			$wpdb->print_error();
		}
		
	}

	public static function list_snapshots() {
		global $wpdb;
		$table_name = $wpdb->prefix . "tmpltn_snapshot"; 

		return $wpdb->get_results("
			SELECT id,created_time from $table_name
		");
	}

	public static function load_snapshot($idsnapshot) {
		global $wpdb;
		$table_name = $wpdb->prefix . "tmpltn_snapshot"; 
		$prepared = $wpdb->prepare("SELECT content FROM $table_name WHERE id = %d",$idsnapshot);
		return $wpdb->get_results($prepared);
	}
}
