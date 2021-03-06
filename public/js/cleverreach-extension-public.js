(
	function( $ ) {
		'use strict';
                
		$( cre.selector + cre.container_selector + ' form' ).on( 'submit', function() {
                        
                        if( !$( this ).hasClass('wpcf7-form') ){
                            
                            $( this ).crFormSubmit();
                            
                            return false;
                        }
                });
                
                $.fn.crFormSubmit = function() {

			var $cr_container = $( this ).closest( cre.selector + cre.container_selector );

			$.ajax( {
				url       : cre.ajaxurl,
				type      : 'POST',
				dataType  : 'JSON',
				data      : {
					action : 'cre_ajax_controller_interaction',
					nonce  : cre.nonce,
					form   : $( this ).data( 'form' ),
					list   : $( this ).data( 'list' ),
					source : $( this ).data( 'source' ),
					cr_form: $( this ).serialize()
				},
				beforeSend: function() {
					$cr_container.find( cre.selector + cre.response_selector ).remove();
					$cr_container.append( '<p class="' + cre.loading_selector + '">' + cre.loading + '</p>' );
				},
				success   : function( response ) {
					$cr_container.find( cre.selector + cre.loading_selector ).remove();
					if ( response.type === 'success' ) {
						$cr_container.append( '<p class="' + cre.response_selector + ' ' + cre.success_selector + '">' + cre.success + '</p>' );
					} else {
						$cr_container.append( '<p class="' + cre.response_selector + ' ' + cre.error_selector + '">' + response.status + '</p>' );
					}
				},
				error     : function() {
					$cr_container.find( cre.selector + cre.loading_selector ).remove();
					$cr_container.append( '<p class="' + cre.response_selector + ' ' + cre.error_selector + '">' + cre.error + '</p>' );
				}
			} );

		} 

	}
)( jQuery );

function callCrFormSubmit(){
    jQuery( cre.selector + cre.container_selector + ' form' ).crFormSubmit();
}