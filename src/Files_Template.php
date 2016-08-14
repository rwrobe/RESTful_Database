<?php
/**
 * Adds our OD template part via a shortcode.
 *
 * This is adapted from the sample code on WPExplorer by Harri Bell-Thomas:
 * https://github.com/wpexplorer/page-templater
 */

namespace notne\rdb;


class Files_Template {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	public function __construct() {
		add_shortcode( 'rdb', array( &$this, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'scripts' ) );
	}

	/**
	 * Enqueue the front-end script
	 */
	function scripts() {
		wp_enqueue_script( 'rdb-data-tables', RDB_PLUGIN_URL . 'js/jquery.dataTables.min.js', array( 'jquery' ), '1.10.12', true );
		wp_enqueue_script( 'rdb-app', RDB_PLUGIN_URL . 'js/app.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'rdb-app', 'rdb_options', array(
			'site_url'    => get_site_url(),
		) );
	}

	/**
	 * Locate template.
	 *
	 * @param 	string 	$template_name			Template to load.
	 * @param 	string 	$string $template_path	Path to templates.
	 * @param 	string	$default_path			Default path to template files.
	 * @return 	string 							Path to the template file.
	 */
	function locate_template( $template_name, $template_path = '', $default_path = '' ) {

		if ( ! $template_path ) {
			$template_path = 'database/';
		}

		// Set default plugin templates path.
		if ( ! $default_path ) {
			$default_path = RDB_BASE_DIR . '/templates/'; // Path to the template folder
		}

		// Search template file in theme folder.
		$template = locate_template( array(
			$template_path . $template_name,
			$template_name
		) );

		// Get plugins template file.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		return apply_filters( 'rdb_locate_template', $template );

	}


	/**
	 * Get template.
	 *
	 * Search for the template and include the file.
	 *
	 * @param string 	$template_name			Template to load.
	 * @param array 	$args					Args passed for the template file.
	 * @param string 	$string $template_path	Path to templates.
	 * @param string	$default_path			Default path to template files.
	 */
	function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

		$template_file = $this->locate_template( $template_name, $template_path, $default_path );

		// Bail if the template isn't there
		if ( ! file_exists( $template_file ) ) {
			return;
		}

		include $template_file;

	}


	/**
	 * OneDrive Shortcode
	 *
	 * The redeem gift card shortcode will output the template
	 * file from the templates/folder.
	 */
	function shortcode() {
		return $this->get_template( 'page-data.php' );
	}
}

$ft = new Files_Template();