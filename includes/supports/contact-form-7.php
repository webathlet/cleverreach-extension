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
	 * Add a submenu page to the Contact Form 7 admin menu.
	 *
	 * @since   0.3.0
	 *
	 * @wp-hook admin_menu
	 */
	public function add_submenu_page() {

		add_submenu_page(
			'wpcf7',
			esc_html__( 'Add New Signup Form', 'cleverreach-extension' ),
			esc_html__( 'Add Signup Form', 'cleverreach-extension' ),
			'wpcf7_edit_contact_forms',
			'admin.php?page=wpcf7-new&template=cleverreach-signup'
		);

	}

	/**
	 * Add `cleverreach_extension` tag to the Contact From 7 default tags.
	 * Hooks into `WPCF7_TagGenerator`.
	 *
	 * @since   0.3.0
	 *
	 * @wp-hook wpcf7_admin_init
	 */
	public function extend_tag_generator() {

		$tag_generator = \WPCF7_TagGenerator::get_instance();
		$tag_generator->add(
			'cleverreach_extension',
			esc_html__( 'CleverReach', 'cleverreach-extension' ),
			array( $this, 'render_tag_generator_cleverreach' )
		);

	}

	/**
	 * Render admin view for `cleverreach_extension` tag generator.
	 *
	 * @since        0.3.0
	 *
	 * @param        $contact_form
	 * @param string $args
	 */
	public function render_tag_generator_cleverreach( $contact_form, $args = ''  ) {

		$args = wp_parse_args( $args, array() );

		?>
		<div class="control-box">
			<fieldset>
				<table class="form-table">
					<tbody>

						<tr>
							<th scope="row">
								<label for="<?php echo esc_attr( $args['content'] . '-list' ); ?>"><?php echo esc_html__( 'List', 'cleverreach-extension' ); ?></label>
							</th>
							<td>
								<input type="text" name="list_id" class="oneline option numeric" id="<?php echo esc_attr( $args['content'] . '-list' ); ?>" />
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="<?php echo esc_attr( $args['content'] . '-source' ); ?>"><?php echo esc_html__( 'Source', 'cleverreach-extension' ); ?></label>
							</th>
							<td>
								<input type="text" name="source" class="oneline option" id="<?php echo esc_attr( $args['content'] . '-source' ); ?>" />
							</td>
						</tr>

					</tbody>
				</table>
			</fieldset>
		</div>

		<div class="insert-box">
			<input type="text" name="<?php echo esc_attr( $args['id'] ); ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

			<div class="submitbox">
				<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
			</div>

			<br class="clear" />

			<p class="description <?php echo esc_attr( $args['id'] ); ?>-tag"><?php echo sprintf( esc_html__( 'Please check the %swiki%s on how to apply further customization.', 'cleverreach-extension' ), '<a href="' . $this->wiki_url . '">', '</a>' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Extends the default form template.
	 * Hooks into `WPCF7_ContactFormTemplate`.
	 *
	 * @since   0.3.0
	 *
	 * @param   $template mixed Default template for Contact Form 7.
	 * @param   $prop     string Current panel holding the template.
	 *
	 * @wp-hook wpcf7_default_template
	 * @return  mixed
	 */
	public function extend_default_template( $template, $prop ) {

		$current = ( isset( $_GET['template'] ) ) ? $_GET['template'] : '';
		if ( $prop === 'form' ) {
			if ( 'cleverreach-signup' === $current ) {

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

	/**
	 * Filter Contact Form 7 form elements.
	 *
	 * @since   0.3.0
	 *
	 * @wp-hook wpcf7_form_elements
	 */
	public function filter_form_elements( $content ) {

		/*
		// @TODO 2016/03/11: Detect shortcode and map `form_id` to current form.
		$content = str_replace( '[cleverreach_extension]', '', $content, $count );
		if ( 1 <= $count ) {
			// Wrap with custom selector
		}
		*/

		return $content;

	}

}