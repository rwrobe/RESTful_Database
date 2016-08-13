<?php
/**
 * Class to create the DB table and offer up static methods for interacting with it.
 */

namespace notne\rdb;


if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class DB {

	/** @var string $textdomain The text domain for the plugin, defined in constructor */
	public $textdomain = '';
	/** @var mixed $items The Database items retrieved */
	public $items = array();


	public function __construct() {
		$this->textdomain = 'rdb';
	}

	/**
	 * Given the extensive nature of the Database file-tree, we're creating a custom table for it.
	 * This should also allow us to include accented characters.
	 */
	public static function create_db() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'restful_Database';

		$sql = "CREATE TABLE $table_name (
			item_id varchar(30) NOT NULL DEFAULT '',
			parent_item_id varchar	(30) NOT NULL DEFAULT '0',
			children varchar(140) NOT NULL DEFAULT '',
			item_title varchar(140) NOT NULL DEFAULT '',
			item_type varchar(20) NOT NULL DEFAULT 'dir',
			item_download_url VARCHAR(140) NOT NULL DEFAULT '',
			item_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			item_groups varchar(140) NOT NULL DEFAULT 'admin',
			PRIMARY KEY  (item_id),
			KEY item_groups (item_groups)
		) $charset_collate; ";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * List a directory from the parent ID
	 *
	 * @param string $dir_id The parent ID
	 */
	public static function list_dir( $dir_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restful_Database';

		$sql = "SELECT * FROM `$table_name` WHERE `parent_item_id` = %s;";

		return $wpdb->get_results(
			$wpdb->prepare(
				$sql, stripslashes( $dir_id )
			),
			ARRAY_A );
	}

	/**
	 * Get an item from it's ID
	 *
	 * @param string $item_id
	 */
	public static function get_item( $item_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restful_Database';

		$sql = "SELECT * FROM `$table_name` WHERE `item_id` = %s;";


		$result = $wpdb->get_results(
			$wpdb->prepare(
				$sql, stripslashes( $item_id )
			),
			ARRAY_A );

		if ( $result ) {
			return $result[0];
		} else {
			return new \WP_Error( '100', __( 'There is no item for that ID' ) );
		}
	}

	/**
	 * Get an item's parent from it's ID
	 *
	 * @param string $item_id
	 */
	public static function get_parent( $item_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restful_Database';

		$sql = "SELECT * FROM `$table_name` WHERE `item_id` = %s;";


		$results = $wpdb->get_results(
			$wpdb->prepare(
				$sql, stripslashes( $item_id )
			),
			ARRAY_A );

		return $results[0]['parent_item_id'];
	}

	/**
	 * Get an item's children from it's ID
	 *
	 * @param string $item_id
	 */
	public static function get_children( $item_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restful_Database';

		$sql = "SELECT * FROM `$table_name` WHERE `parent_item_id` = %s;";

		$results = $wpdb->get_results(
			$wpdb->prepare(
				$sql, $item_id
			),
			ARRAY_A );

		return $results;
	}

	/**
	 * Save the Database response JSON in the database
	 *
	 * @param $json
	 */
	public static function save_drive( $record ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restful_Database';

		$sql = "INSERT INTO `$table_name` (item_id, parent_item_id, children, item_title, item_type, item_download_url, item_modified, item_groups) 
				SELECT 
					%s, %s, %s, %s, %s, %s, %s, %s 
				FROM 
					dual 
				WHERE 
					NOT EXISTS (
						SELECT 
							* 
						FROM 
							`$table_name` 
						WHERE 
							`item_id` = %s
					);";

		$return = $wpdb->get_results( $wpdb->prepare( $sql,
			$record['item_id'],
			$record['parent_item_id'],
			'',
			$record['item_title'],
			$record['item_type'],
			$record['item_download_url'],
			$record['item_modified'],
			'',
			$record['item_id']
		) );

		return $return;
	}

	/**
	 * Grab the root directory's ID
	 *
	 * @return string   The item ID
	 */
	public static function quicky_root_finder() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restful_Database';

		$sql = "SELECT * FROM $table_name WHERE item_title = 'root'";

		$return = $wpdb->get_results( $sql );

		return $return[0]->item_id;
	}

	/**
	 * A quick function to get only the directories, for permissions purposes
	 *
	 * @return mixed    An array of item IDs
	 */
	public static function get_dirs(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'restful_Database';
		$return = array();

		$sql = "SELECT item_ID FROM $table_name WHERE item_type = 'dir'";

		$results = $wpdb->get_results( $sql, ARRAY_N );

		/** Flatten the array */
		foreach ( $results as $result ) {
			$return[] = sanitize_text_field( $result[0] );
		}

		return $return;
	}

	/**
	 * Save the links generated by the Database embed URL generator
	 */
	public static function save_share_link( $item_id, $url ){
		global $wpdb;
		$table_name = $wpdb->prefix . 'restful_Database';
		$return = '';

		$sql = "UPDATE `$table_name` SET item_download_url = '%s' WHERE item_id = '%s';";

		$results = $wpdb->get_results( $wpdb->prepare( $sql,
				$url,
				$item_id
			),
		ARRAY_N );

		return $return;
	}
}
