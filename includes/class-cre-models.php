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
				'list_id' => $helper->get_option( 'list_id' ),
				'form_id' => $helper->get_option( 'form_id' ),
				'custom_form' => $helper->get_option( 'custom_form' ),
				'source' => $helper->get_option( 'source' ),
			), $params, 'cleverreach_extension'
		);

		$html = '<div class="cr_form-container">';

		// Build (custom) form according to shortcode attributes.
		if ( 'custom' === $atts['form_id'] || $atts['custom_form'] ) {

			// Get filtered custom form.
			$html_form = apply_filters( 'cleverreach_extension_subscribe_form', esc_html__( 'Please apply your own form within your plugin or theme.', 'cleverreach-extension' ) );

			// Append custom `list_id`.
			if ( $atts['list_id'] ) {
				$html_form = str_replace(
					'<form ', '<form data-list="' . $atts[ 'list_id' ] . '" ', $html_form
				);
			}

			// Append custom `source`.
			if ( $atts['source'] ) {
				$html_form = str_replace(
					'<form ', '<form data-source="' . $atts[ 'source' ] . '" ', $html_form
				);
			}

			$html .= $html_form;

		} else {

			// Get form code from CleverReach.
			$html_form = $form->get_embedded_code( $atts['form_id'] );
			$html .= $html_form->data;

		}

		$html .= '</div>'; // end of .cr_form-container

		return $html;

	}

}