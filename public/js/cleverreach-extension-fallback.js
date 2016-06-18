(
	function( $ ) {
		'use strict';

		$( cre.selector + cre.container_selector + ' form input' ).each( function() {

			var input = $( this ),
				input_name = input.attr( 'name' );

			// Limit to input fields without custom name.
			if ( input_name.match( /([0-9]+)$/ ) ) {

				// Attempt to use label as name, id otherwise.
				var input_label = input.parent().find( 'label' ).text();
				if ( input_label.length ) {
					input_label = input_label.replace( /[^a-z0-9\s]/gi, '' ).replace( /[_\s]/g, '_' ).toLowerCase();
					input.attr( 'name', input_label );
				} else {
					var input_id = input.attr( 'id' );
					input.attr( 'name', input_id );
				}

			}

		} );

	}
)( jQuery );
