<?php namespace CleverreachExtension\Core\Supports;

/**
 * Contact From 7 plugin integration.
 *
 * Contact Form 7 with accessible defaults:
 * @link https://wordpress.org/plugins/contact-form-7-accessible-defaults/
 *
 * @since      0.3.0
 * @package    Cleverreach_Extension
 * @subpackage Cleverreach_Extension/includes/supports
 * @link       http://contactform7.com/
 * @docs       http://contactform7.com/docs/
 * @author     Sven Hofmann <info@hofmannsven.com>
 */
class Contact_Form_7 {

	/**
	 * URL to the official plugin wiki.
	 *
	 * @since  0.3.0
	 * @access protected
	 * @var    string
	 */
	protected $wiki_url = 'https://github.com/hofmannsven/cleverreach-extension/wiki';

	/**
	 * Extends default `WPCF7_Editor` panels.
	 *
	 * @since   0.3.0
	 * @param   $panels array
	 *
	 * @wp-hook wpcf7_editor_panels
	 * @return  mixed
	 */
	public function extend_editor_panels( $panels ) {

		$panels['cleverreach-extension-forms'] = array(
			'title'    => esc_html__( 'Templates', 'cleverreach-extension' ),
			'callback' => array( $this, 'render_editor_panel_view' )
		);

		return $panels;

	}

	/**
	 * Renders the `WPCF7_Editor` panel view as HTML.
	 *
	 * @since 0.3.0
	 */
	public function render_editor_panel_view() {

		echo '<h2>' . esc_html__( 'CleverReach Extension', 'cleverreach-extension' ) . '</h2>';
		echo '<p>' . esc_html__( 'Create a new form based on one of the following templates:', 'cleverreach-extension' ) . '</p>';

		echo '<ul style="list-style-type:disc;padding:0 20px;">';

		$cf7_admin = admin_url( 'admin.php?page=wpcf7-new' );
		$url = add_query_arg( 'custom_form', 'subscribe', $cf7_admin );
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html__( 'Subscribe form template', 'cleverreach-extension' ) . '</a></li>';

		echo '</ul>';

		echo sprintf( esc_html__( 'Please check the %swiki%s on how to apply further customization.', 'cleverreach-extension' ), '<a href="' . $this->wiki_url . '">', '</a>' );

	}

	/**
	 * Extends the default `WPCF7_ContactFormTemplate` setup.
	 *
	 * @since   0.3.0
	 * @param   $template mixed Default template for Contact Form 7.
	 * @param   $prop     string Current panel holding the template.
	 *
	 * @wp-hook wpcf7_default_template
	 * @return  mixed
	 */
	public function extend_default_template( $template, $prop ) {

		$current = ( isset( $_GET['custom_form'] ) ) ? $_GET['custom_form'] : '';
		if ( $prop === 'form' ) {
			if ( 'subscribe' === $current ) {

				$template = '<label>Gender</label><br />' . "\n" .
							'[select gender id:cre_gender "Female" "Male" "Other"]' . "\n\n" .

							'<label>First Name</label><br />' . "\n" .
							'[text firstname id:cre_firstname]' . "\n\n" .

							'<label>Last Name</label><br />' . "\n" .
							'[text lastname id:cre_lastname]' . "\n\n" .

							'<label>Your Email (required)</label><br />' . "\n" .
							'[email* email id:cre_email]' . "\n\n" .

							'[submit id:cre_submit "Submit"]';

			}
		} else if ( $prop === 'mail' ) {
			if ( 'subscribe' === $current ) {

				$template['subject'] = '';
				$template['body'] = '';

			}
		}

		return $template;

	}

}