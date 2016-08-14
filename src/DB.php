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
	/** @var mixed $field_names Fields in the DB */
	public static $field_names = array();


	public function __construct() {
		$this->textdomain  = 'rdb';
		self::$field_names = array(
			'v_id',
			'updated',
			'original_date',
			'v_name',
			'voca_client',
			'new_client',
			'activated_cont',
			'dod',
			'jurisdiction',
			'type_of_crime',
			'dob',
			'client_name',
			'relationship',
			'address',
			'email',
			'telephone',
			'voca_ct_race',
			'voca_ct_gender',
			'voca_age',
			'voca_service_area',
			'voca_victimization',
			'voca_disabilities',
			'status_date',
			'status'
		);
	}

	/**
	 * Given the extensive nature of the Database file-tree, we're creating a custom table for it.
	 * This should also allow us to include accented characters.
	 *
	 * @todo Function for creating this table based on user-selected parameters from a setup script.
	 */
	public static function create_db() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'restful_database';

		$sql = "CREATE TABLE $table_name (
			v_id BIGINT(30) NOT NULL AUTO_INCREMENT,
			updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			original_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			v_name_first VARCHAR(140) NOT NULL DEFAULT '',
			v_name_middle VARCHAR(140) NOT NULL DEFAULT '',
			v_name_last VARCHAR(140) NOT NULL DEFAULT '',
			voca_client VARCHAR(10) NOT NULL DEFAULT '',
			new_client VARCHAR(10) NOT NULL DEFAULT '',
			activated_cont DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			dod DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			jurisdiction VARCHAR(10) NOT NULL DEFAULT '',
			type_of_crime varchar(140) NOT NULL DEFAULT '',
			dob DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			client_name_first VARCHAR(140) NOT NULL DEFAULT '',
			client_name_middle VARCHAR(140) NOT NULL DEFAULT '',
			client_name_last VARCHAR(140) NOT NULL DEFAULT '',
			relationship VARCHAR(140) NOT NULL DEFAULT '',
			email VARCHAR(140) NOT NULL DEFAULT '',
			telephone VARCHAR(140) NOT NULL DEFAULT '',
			voca_ct_race VARCHAR(1) NOT NULL DEFAULT '',
			voca_ct_gender VARCHAR(1) NOT NULL DEFAULT '',
			voca_age VARCHAR(5) NOT NULL DEFAULT '',
			voca_service_area VARCHAR(1) NOT NULL DEFAULT '',
			voca_victimization VARCHAR(1) NOT NULL DEFAULT '',
			voca_disabilities VARCHAR(1) NOT NULL DEFAULT '',
			status_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			status VARCHAR(140) NOT NULL DEFAULT '',
			UNIQUE KEY v_id (v_id)
		) $charset_collate; ";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );
	}

	/**
	 * Save rows into the DB
	 *
	 * @param mixed $request An array of values corresponding to the columns of our db
	 */
	public static function save_data( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'restful_database';
		/**
		 * @var mixed $clean_data We flip and replace they array keys here to order the array to match the DB fields.
		 */
		$clean_data = array_merge( array_flip( map_deep( $request, 'sanitize_text_field' ) ), self::$field_names );
		$fields     = explode( ',', self::$field_names );
		$wildcards  = ( "%s" * sizeof( $fields ) );

		wp_die( $clean_data );

		$sql = "INSERT INTO `$table_name` ($fields) 
				SELECT 
					$wildcards
				FROM 
					dual 
				WHERE 
					NOT EXISTS (
						SELECT 
							* 
						FROM 
							`$table_name` 
						WHERE 
							`v_id` = %s
					);";

		return $wpdb->get_results( $wpdb->prepare( $sql, $clean_data ) );
	}

	/**
	 * Grab rows from the DB
	 *
	 * @param mixed $args The query arguments
	 */
	public static function get_data( $args ) {
		global $wpdb;
		$table_name     = $wpdb->prefix . 'restful_database';
		$posts_per_page = $args['posts_per_page'] ? esc_html( $args['posts_per_page'] ) : 20;
		$page           = $args['page'] ? esc_html( $args['page'] ) : 0;
		$page_offset    = $page * $posts_per_page;
		$return         = array();
		$results        = array();

		$sql      = "SELECT * FROM $table_name LIMIT %d OFFSET %d";

		$prepared = $wpdb->prepare( $sql, array(
			$posts_per_page,
			$page_offset
		) );

		$results  = $wpdb->get_results( $prepared, ARRAY_N );

		return $results;
	}
}
