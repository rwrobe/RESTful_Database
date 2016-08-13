<?php
/**
 * RESTful Database
 *
 * An implementation of the Database JavaScript API intended to allow for online viewing,
 * caching via the REST API and offline viewing.
 *
 * @todo: Get redirect URI to work in the main window after auth.
 * @todo: Save the directory structure in JSON either using the Options API or the included
 *        folders CPT
 * @todo: Include some styles (if necessary)
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
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ) );
		add_shortcode( 'one-drive', array( &$this, 'od_button_shortcode' ) );
		add_filter( 'template_include', array( &$this, 'register_callback' ) );
	}

	function scripts() {

		wp_enqueue_script( 'rdb-od-app', RDB_PLUGIN_URL . 'js/OD_App.js', array( 'jquery' ), '1.0', true );

		wp_localize_script( 'rdb-od-app', 'rdb_options', array(
			'site_url' => get_site_url(),
			'site_id'  => get_current_blog_id(),
		) );

	}

	/**
	 * Enqueues admin scripts to make OD authorization possible
	 */
	function admin_scripts() {

		$screen = get_current_screen();

		if ( in_array( $screen->base, array(
			"toplevel_page_RESTful_DB/rdb_options",
			"toplevel_page_restful-one-drive/rdb_options"
		) ) ) {
			wp_enqueue_script( 'rdb-od-auth', RDB_PLUGIN_URL . 'js/OD_Auth.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'rdb-od-rest', RDB_PLUGIN_URL . 'js/REST.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'rdb-od-admin', RDB_PLUGIN_URL . 'js/OD_Admin_App.js', array( 'jquery' ), '1.0', true );

			wp_localize_script( 'rdb-od-admin', 'rdb_options', array(
				'is_admin'    => true,
				'site_url'    => get_site_url(),
				'site_id'     => get_current_blog_id(),
				'clientId'    => esc_attr( get_option( 'rdb_api_key' ) ),
				'scopes'      => 'Database.readwrite wl.signin',
				'redirectUri' => untrailingslashit( get_site_url() ) . '/rdb-callback',
			) );

			wp_enqueue_style( 'rdb-style', RDB_PLUGIN_URL . 'css/rdb-style.css' );
		}
	}

	/**
	 * Creates our virtual callback URL
	 */
	public function register_callback( $original_template ) {

		$url          = $_SERVER['REQUEST_URI'];
		$rdb_callback = strpos( $url, 'rdb-callback' );

		if ( $rdb_callback !== false ) {
			return RDB_PLUGIN_PATH . '/templates/callback.php';
		} else {
			return $original_template;
		}

	}

	/**
	 * A shortcode to display the launch button.
	 *
	 * @param $atts The label for the launch button
	 *
	 * @return string   The markup
	 */
	public function od_button_shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'label' => __( 'Launch Database' )
		), $atts );

		$output = '<button onClick="OD_Auth.challengeForAuth();">' . esc_html( $atts['label'] ) . '</button >';

		return $output;
	}

	/**
	 * Register the route for the REST API.
	 *
	 * @todo: Add a POST endpoint (or POST to an endpoint) for caching the file structure.
	 */
	public function register_api_routes() {
		/**
		 * @var string $namespace The base REST route, explorable at BASE_URL/wp-json/resful-od/v1/
		 */
		$namespace = 'restful-od/v1';

		register_rest_route( $namespace, '/post-folders/', array(
			'methods'  => 'POST',
			'callback' => array( &$this, 'create_folders' ),
		) );

		register_rest_route( $namespace, '/post-token/', array(
			'methods'  => 'POST',
			'callback' => array( &$this, 'save_token' ),
		) );
	}

	/**
	 * Create a JSON entry in the database
	 *
	 * @param JSON  The request
	 *
	 * @return \WP_Error
	 */
	public function create_folders( $request ) {
		$data   = array();
		$return = '';

		if ( is_array( $json = $request->get_body_params() ) && isset( $json["value"] ) ) {
			$items = map_deep( wp_unslash( $json["value"] ), 'sanitize_text_field' );

			foreach ( $items as $item ) {
				$data[ $item['id'] ][] = $this->save_od_json( $item );
				$this->save_dl_url( $item['id'] );
			}

			return $items;

			if ( $data ) {
				$return = $data;
			}
		} else {
			$return = esc_html__( 'This request failed. Please contact the developer' );
		}

		$response = new \WP_REST_Response( $return );
		$response->header( 'Access-Control-Allow-Origin', apply_filters( 'rmaps_access_control_allow_origin', '*' ) );

		return $response;
	}

	/**
	 * The callback to save the JSON
	 *
	 * @param mixed The sanitized request params
	 *
	 * @return mixed|bool A confirmation message
	 */
	public function save_od_json( $item ) {
		if ( $item ) {

			$type = '';

			if ( isset( $item["folder"] ) ) {
				$type = "dir";
			} else if ( isset( $item["file"] ) ) {
				$type = $item["file"]["mimeType"];
			} else {
				$type = 'unk';
			}

			$record = array(
				"item_id"           => $item["id"],
				"parent_item_id"    => $item["parentReference"]["id"] ? $item["parentReference"]["id"] : '',
				//"children"          => $item["children"] ? $item["children"] : '',
				"item_title"        => isset( $item["name"] ) ? $item["name"] : '',
				"item_type"         => $type ? $type : '',
				"item_od_id"        => isset( $item["id"] ) ? $item["id"] : '',
				"item_download_url" => '',
				"item_modified"     => isset( $item["lastModifiedDateTime"] ) ? $item["lastModifiedDateTime"] : strtotime( '00-00-00 00:00:00' ),
			);

			/** @var mixed $return The $wpdb return. 1 if successful, false otherwise */
			$return = DB::save_drive( $record );

			return $return;
		}

		return false;
	}

	/**
	 * Save the Database API token if in request, or access it.
	 *
	 * @param mixed $request The request
	 *
	 * @return mixed|void   True if saving the token; The token if retrieving it; Or 511 if the token does not exist
	 */
	public function save_token( $request ) {
		$return = '';
		$token  = $request->get_body();

		if ( $token != '' && ! get_option( 'rdb_token' ) ) {
			update_option( 'rdb_token', $token );
			$return = (int) 202;
		} else if ( ! get_option( 'rdb_token' ) ) {
			$return = (int) 511;
		} else {
			$return = get_option( 'rdb_token' );
		}

		$response = new \WP_REST_Response( $return );
		$response->header( 'Access-Control-Allow-Origin', apply_filters( 'rdb_access_control_allow_origin', '*' ) );

		if ( is_int( $return ) ) {
			$response->set_status( $return );
		}

		return $response;
	}


	/**
	 * Save the download link using the DB static method
	 */
	public function save_dl_url( $item_id ) {

		$url = $this->get_dl_url( $item_id );

		return DB::save_share_link( $item_id, $url );
	}

	/**
	 * Grab the download URL for one item
	 */
	public function get_dl_url( $item_id ) {

		$token = get_option( 'rdb_token' );

		if ( ! $token || ! $item_id ) {
			return false;
		}

		$response = '';

		$url  = "https://api.Database.com/v1.0/drive/items/$item_id/action.createLink";
		$args = array(
			'method'  => 'POST',
			'headers' => array(
				"Authorization" => "bearer {$token}",
				"Content-Type"  => "application/json"
			),
			"body"    => json_encode( array(
				"type" => "embed"
			) )
		);

		$response      = wp_remote_post( $url, $args );
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );

		switch ( $response_code ) {
			case 401 :
				return false;
				break;
			case 415 :
				echo 'not supported';

				return false;
			default:
				break;
		}

		return $response_body->link->webUrl;

	}
}

$rmaps = new RESTful_DB();
