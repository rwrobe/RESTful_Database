<?php
/**
 * RESTful OD Options
 *
 * @package RDB
 */

// create custom plugin settings menu
add_action('admin_menu', 'rdb_create_menu');

/**
 * Add a menu entry for Database settings
 */
function rdb_create_menu() {

	add_menu_page(
		'Database Settings',
		'Database Settings',
		'administrator',
		__FILE__,
		'rdb_settings_page',
		'dashicons-feedback'
	);

	add_action( 'admin_init', 'register_rdb_settings' );
}

/**
 * Register the settings group
 */
function register_rdb_settings() {
	//register our settings
	register_setting( 'rdb-settings-group', 'rdb_api_key' );
}

/**
 * The markup for the settings page
 *
 * @todo: Add nonce
 */
function rdb_settings_page() {
	?>
	<div class="wrap">
		<h2>RESTful Database Settings</h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'rdb-settings-group' ); ?>
			<?php do_settings_sections( 'rdb-settings-group' ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Database Client ID' ); ?><br /></th>
					<td class="key"><input type="text" name="rdb_api_key" value="<?php echo esc_attr( get_option( 'rdb_api_key' ) ); ?>" /></td>
					<td><button id="auth-btn" onClick="OD_Auth.challengeForAuth();"><?php esc_html_e( 'Authorize this App' ); ?></td>
					<td id="authorized" class="hidden"><?php esc_html_e( 'This app is authorized.' ); ?></td>
				</tr>
			</table>

			<div id="loading">
				<span><div id="loader"></div>Loading</span>
			</div>

			<a href="#" id="show-debug"><?php esc_html_e( 'Show debug info' ); ?></a>


			<div id="debug-info">
				<pre id="json-response"></pre>
			</div>

			<?php submit_button(); ?>

		</form>
	</div>
<?php } ?>