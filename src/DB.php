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
	 */
	public static function create_db() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'restful_database';

		$sql = "CREATE TABLE $table_name (
			v_id BIGINT(30) NOT NULL AUTO_INCREMENT,
			updated DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			original_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			v_name VARCHAR(140) NOT NULL DEFAULT '',
			voca_client VARCHAR(10) NOT NULL DEFAULT '',
			new_client VARCHAR(10) NOT NULL DEFAULT '',
			activated_cont DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			dod DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			jurisdiction DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			type_of_crime varchar(140) NOT NULL DEFAULT '',
			dob VARCHAR(140) NOT NULL DEFAULT '',
			client_name VARCHAR(140) NOT NULL DEFAULT '',
			relationship VARCHAR(140) NOT NULL DEFAULT '',
			address VARCHAR(140) NOT NULL DEFAULT '',
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
			UNIQUE KEY id (id)
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
		$clean_data = map_deep( $request, 'sanitize_text_field' );
		$fields     = explode( ',', self::$field_names );
		$wildcards  = ( "%s" * sizeof( $fields ) );

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
							`item_id` = %s
					);";
	}
}
