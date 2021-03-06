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
	}

	/**
	 * Register the route for the REST API.
	 */
	public function register_api_routes() {
		/**
		 * @var string $namespace The base REST route, explorable at BASE_URL/wp-json/resful-od/v1/
		 */
		$namespace = 'restful-db/v1';

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
