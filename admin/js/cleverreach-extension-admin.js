(
	function( $ ) {
		'use strict';

		// Render select options from response.
		$.fn.renderSelectOptions = function( $response, $empty ) {
			// Reset and append empty option.
			var $select = $( this );
			$select.empty().append( '<option value="">' + $empty + '</option>' );

			if ( typeof $response !== 'undefined' && $response.length > 0 ) {
				// Parse options from response.
				$.each( $response, function( i, obj ) {
					$select.append( $( '<option>', {
						value: obj.id,
						text : obj.name
					} ) );

					// Select selected option.
					if ( obj.selected ) {
						$select.find( 'option[value="' + obj.id + '"]' ).prop( 'selected', true );
					}

				} );
			}
		};

		// Validate response and update status: confirmed/invalid
		$.fn.validateResponse = function( $response ) {
			var $selector = $( this ).parent().find( cre_admin_response_selector );
			if ( true === $response ) {
				$selector.removeClass( 'invalid' ); // Cleanup
				$selector.addClass( 'confirmed' ); // Confirmed
			} else {
				$selector.removeClass( 'confirmed' ); // Cleanup
				$selector.addClass( 'invalid' ); // Invalid
			}
		};

		var cre_admin_timeout;
		var cre_admin_selector = cre_admin.selector,
			cre_admin_container_selector = cre_admin_selector + cre_admin.container_selector,
			cre_admin_key_selector = cre_admin_selector + cre_admin.key_selector,
			cre_admin_list_selector = cre_admin_selector + cre_admin.list_selector,
			cre_admin_form_selector = cre_admin_selector + cre_admin.form_selector,
			cre_admin_source_selector = cre_admin_selector + cre_admin.source_selector,
			cre_admin_ajax_selector = cre_admin_selector + cre_admin.ajax_selector,
			cre_admin_response_selector = cre_admin_selector + cre_admin.response_selector;

		// Display as few rows as possible.
		if ( 0 !== $( cre_admin_key_selector ).val().length ) {
			// Display all rows.
			$( cre_admin_container_selector + ' form tr' ).each( function() {
				$( this ).closest( 'tr' ).fadeIn();
			} );
		} else {
			// Display only main rows.
			$( cre_admin_container_selector + ' form tr input' ).each( function() {
				$( this ).closest( 'tr' ).fadeIn();
			} );
		}

		// Listen for changes.
		$( cre_admin_key_selector + ',' + cre_admin_list_selector + ',' + cre_admin_form_selector + ',' + cre_admin_source_selector + ',' + cre_admin_ajax_selector ).on( 'input change', function() {
			var $response = $( this ).parent().find( cre_admin_response_selector );
			$response.removeClass( 'confirmed invalid' ); // Cleanup status
			$response.addClass( 'updating animate' ); // Loading

			// Live preview.
			$( cre_admin_list_selector + '-preview' ).text( $( cre_admin_list_selector ).val() );
			$( cre_admin_form_selector + '-preview' ).text( $( cre_admin_form_selector ).val() );
			$( cre_admin_source_selector + '-preview' ).text( $( cre_admin_source_selector ).val() );
		} );

		// Submit and render response.
		$( cre_admin_container_selector + ' form' ).on( 'input change', function() {

			clearTimeout( cre_admin_timeout );
			cre_admin_timeout = setTimeout( function() {

				var cre_admin_form_data = {
					api_key: $( cre_admin_key_selector ).val(),
					list_id: $( cre_admin_list_selector ).val(),
					form_id: $( cre_admin_form_selector ).val(),
					source: $( cre_admin_source_selector ).val(),
					ajax: $( cre_admin_ajax_selector ).is( ':checked' )
				};

				$.ajax( {
					url     : cre_admin.ajaxurl,
					type    : 'POST',
					dataType: 'JSON',
					data    : {
						action       : 'cre_admin_ajax_controller_interaction',
						nonce        : cre_admin.nonce,
						cr_admin_form: cre_admin_form_data // User input
					},
					success: function( response ) {

						// Cleanup loading.
						$( cre_admin_response_selector ).removeClass( 'updating animate' );

						// Render response.
						if ( response.api.status ) {
							$( cre_admin_list_selector ).closest( 'tr' ).fadeIn();
						}

						if ( response.list.status ) {
							$( cre_admin_list_selector + ',' + cre_admin_form_selector ).closest( 'tr' ).fadeIn();
						}

						// Validate response.
						$( cre_admin_key_selector ).validateResponse( response.api.status );

						$( cre_admin_list_selector ).validateResponse( response.list.status );
						$( cre_admin_list_selector ).renderSelectOptions( response.list.options, cre_admin.list_empty );
						$( cre_admin_list_selector + '-preview' ).text( $( cre_admin_list_selector ).val() );

						$( cre_admin_form_selector ).validateResponse( response.form.status );
						$( cre_admin_form_selector ).renderSelectOptions( response.form.options, cre_admin.form_empty );
						$( cre_admin_form_selector + '-preview' ).text( $( cre_admin_form_selector ).val() );

						$( cre_admin_source_selector ).validateResponse( response.source.status );
						$( cre_admin_source_selector + '-preview' ).text( $( cre_admin_source_selector ).val() );

						$( cre_admin_ajax_selector ).validateResponse( response.ajax.status );

					},
					error: function() {

						$( cre_admin_response_selector ).removeClass( 'updating animate' ); // Cleanup loading
						$( cre_admin_list_selector ).empty(); // Reset
						$( cre_admin_form_selector ).empty(); // Reset
						$( cre_admin_response_selector ).addClass( 'invalid' ); // Invalid

					}
				} );

			}, 1000 ); // Auto-save one second after the last change.

			return false;

		} );

	}
)( jQuery );