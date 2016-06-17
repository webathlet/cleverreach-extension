<?php namespace CleverreachExtension\Core;

use CleverreachExtension\Core\Api;

/**
 * Register and parse shortcode and also plugin integrations.
 *
 * @since      0.1.0
 * @package    Cleverreach_Extension
 * @subpackage Cleverreach_Extension/includes
 * @author     Sven Hofmann <info@hofmannsven.com>
 */
class Cre_Models {

	/**
	 * Init plugin shortcode.
	 *
	 * @since 0.1.0
	 */
	public function init_shortcodes() {

		add_shortcode(
			'cleverreach_extension',
			array( $this, 'parse_shortcode' )
		);

	}

	/**
	 * Parse shortcode parameters.
	 *
	 * @since 0.3.0
	 * @param $params
	 *
	 * @return string
	 */
	public function parse_shortcode( $params ) {

		wp_enqueue_script( 'cleverreach-extension' );

		$helper = new Cre_Helper();
		$client = new Api\Cleverreach();
		$form   = new Api\Cleverreach_Form_Adapter( $client );

		// Parse shortcode attributes with defaults.
		$atts = shortcode_atts(
			array(
				'list_id'     => $helper->get_option( 'list_id' ),
				'form_id'     => $helper->get_option( 'form_id' ),
				'custom_form' => $helper->get_option( 'custom_form' ),
				'source'      => $helper->get_option( 'source' ),
			), $params, 'cleverreach_extension'
		);

		$html = '<div class="cr_form-container">';

		// Render (custom/default) form.
		if ( 'custom' === $atts['form_id'] || $atts['custom_form'] ) {

			$html .= apply_filters( 'cleverreach_extension_subscribe_form', esc_html__( 'Please apply your own form within your plugin or theme.', 'cleverreach-extension' ) );

		} else {

			// Get form code or message from CleverReach.
			$embedded_code = $form->get_embedded_code( $atts['form_id'] );

			if ( is_object( $embedded_code ) ) {
				// Force HTTPS everywhere.
				$embedded_code = str_replace( 'http://', 'https://', $embedded_code->data );
				// Remove default CleverReach scripts (including recaptcha widget).
				$embedded_code = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $embedded_code );
			}

			$html .= $embedded_code;

		}

		// Include custom `form_id`.
		if ( ! empty( $atts['form_id'] ) ) {
			$form_id = ( 'custom' === $atts[ 'form_id' ] ) ? $helper->get_option( 'form_id' ) : $atts[ 'form_id' ];
			$html = str_replace( '<form ', '<form data-form="' . $form_id . '" ', $html );
		}

		// Include custom `list_id`.
		if ( ! empty( $atts['list_id'] ) ) {
			$html = str_replace( '<form ', '<form data-list="' . $atts[ 'list_id' ] . '" ', $html );
		}

		// Include custom `source`.
		if ( ! empty( $atts['source'] ) ) {
			$html = str_replace( '<form ', '<form data-source="' . $atts[ 'source' ] . '" ', $html );
		}

		return $html  . '</div>'; // end of .cr_form-container

	}

}
