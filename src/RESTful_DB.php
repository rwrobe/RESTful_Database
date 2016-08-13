<?php
/**
 * RESTful Database
 *
 * @package RDB
 */


namespace notne\rdb;

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class RESTful_DB {

	/** @var string $textdomain The text domain for the plugin, defined in constructor */
	public $textdomain = '';

	public function __construct() {
		$this->textdomain = 'rdb';

		add_action( 'rest_api_init', array( &$this, 'register_api_routes' ) );
		add_action( 'enqueue_scripts', array( &$this, 'scripts' ) );
	}

	function scripts() {
		wp_enqueue_script( 'rdb-data-tables', RDB_PLUGIN_URL . 'js/jquery.dataTables.min.js', array( 'jquery' ), '1.10.12', true );
		wp_enqueue_script( 'rdb-app', RDB_PLUGIN_URL . 'js/app.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'rdb-app', 'rdb_options', array(
			'site_url'    => get_site_url(),
		) );
	}

	/**
	 * Register the route for the REST API.
	 */
	public function register_api_routes() {
		/**
		 * @var string $namespace The base REST route, explorable at BASE_URL/wp-json/resful-od/v1/
		 */
		$namespace = 'restful-od/v1';

		register_rest_route( $namespace, '/get-data/', array(
			'methods'  => 'GET',
			'callback' => array( &$this, 'get_data' ),
		) );
	}

	/**
	 * Grab the download URL for one item
	 *
	 * @var mixed $args The query args
	 */
	public function get_data( $args = array() ) {
		$return = array();

		$return = \notne\rdb\DB::get_data( $args );

		$response = new \WP_REST_Response( $return );
		$response->header( 'Access-Control-Allow-Origin', apply_filters( 'rdb_access_control_allow_origin', '*' ) );

		return $response;
	}
}

$rdb = new RESTful_DB();
