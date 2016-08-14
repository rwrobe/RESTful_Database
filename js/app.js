/**
 * jQuery App the consumes the RMaps REST endpoint for lat./long. and basic post data
 *
 * Response from the get_folders endpoint looks like this: ['ID', 'title', 'infowindow', 'see_more', 'permalink', 'lat', 'lon', 'current']
 */
;var REST = (
	function ( $ ) {

		/**
		 * Set object properties and build the endpoint objects
		 */
		var rdb = document.getElementById( 'rdb' ),
			$rdb = $( rdb ),
			$loading = $( document.getElementById( 'loading' ) ),
			folders = {},
			api_settings = {
				site_url: '',
				api_base: '',
				endpoints: {
					get_data: {route: 'get-data/', method: 'GET'}
				}
			};

		/**
		 * This function will be called on document.ready or at any other time to instantiate the object.
		 */
		var init = function () {
			api_settings.api_base = rdb_options.site_url + rdb_options.api_base;
			init_page();
		};

		/**
		 * Handles the DOM for the page.
		 */
		var init_page = function () {

			$rdb.empty();
			get_data();
		};

		/**
		 * Loads the data via the REST API
		 */
		var get_data = function ( args ) {

			do_ajax( api_settings.endpoints.get_data, args )
				.done( function ( data ) {
					$rdb.removeClass( 'loading' ).append( data );
				} )
				.fail( function ( data ) {
					$rdb.append( '<iframe>' + data.responseText + '</iframe>' );
				} );
		};

		/**
		 * Generic wrapper for the $.ajax() function
		 *
		 * @param endpoint
		 * @param data
		 * @returns REST response
		 */
		var do_ajax = function ( endpoint, data ) {
			return $.ajax( {
				url: api_settings.api_base + endpoint.route,
				method: endpoint.method,
				data: data
			} );
		};

		/** Public API */
		return {
			init: init
		};
	}
)( jQuery );

jQuery( document ).ready( function(){
	REST.init();
} );
