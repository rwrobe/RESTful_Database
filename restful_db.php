<?php
/**
 * RESTful Database
 *
 * @package RDB
 * @author Rob Ward <rwrobe@gmail.com>
 * @version 0.2
 *
 * @wordpress
 * Plugin Name: RESTful Database
 * Plugin URI: https://github.com/thelookandfeel/RESTful_DB
 * Description: Creates a custom database for use with the jQuery DataTables library.
 * Author: <a href="http://notne.com">Rob Ward</a>
 * Version: 0.1
 * Text Domain: rdb
 */


if ( ! defined( 'RDB_BASE_FILE' ) ) {
	define( 'RDB_BASE_FILE', __FILE__ );
}
if ( ! defined( 'RDB_BASE_DIR' ) ) {
	define( 'RDB_BASE_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename( RDB_BASE_FILE ) ) );
}
if ( ! defined( 'RDB_PLUGIN_URL' ) ) {
	define( 'RDB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'RDB_PLUGIN_PATH' ) ) {
	define( 'RDB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! function_exists( 'rdb_set_version' ) ) {
	/** Set the version number */
	function rdb_set_version() {
		update_option( 'rdb_version', '0.1' );
	}
}

/** Create the DB table */
require_once 'src/DB.php';
register_activation_hook( __FILE__, array( '\notne\rdb\DB', 'create_db' ) );

/** Add the REST endpoints */
require_once 'src/RESTful_DB.php';

/** Add an admin page */
require_once 'rdb_options.php';
