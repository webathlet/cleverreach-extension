<?php namespace CleverreachExtension\Core\Supports;

use CleverreachExtension\Core\Api\Cleverreach;
use CleverreachExtension\Core\Cre_Helper;

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
	 * Pattern to detect plugin tag: `[cleverreach_extension]`
	 *
	 * @since  0.3.0
	 * @access protected
	 * @var    string
	 */
	protected $pattern = '/\[cleverreach_extension(.*)\]/';

	/**
	 * Contact Form 7 constructor.
	 */
	public function __construct() {

		$this->client = new Cleverreach();
		$this->helper = new Cre_Helper();

	}

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
		$defined_options = $this->helper->get_option_group();

		?>
		<div class="control-box">
			<fieldset>
				<table class="form-table">
					<tbody>

						<tr>
							<th scope="row">
								<label for="<?php echo esc_attr( $args['content'] . '-list' ); ?>"><?php echo esc_html__( 'List ID', 'cleverreach-extension' ); ?></label>
							</th>
							<td>
								<input type="text" name="list" class="oneline option numeric" id="<?php echo esc_attr( $args['content'] . '-list' ); ?>" value="<?php echo esc_attr( $defined_options['list_id'] ); ?>" />
								<p class="description"><?php echo sprintf( esc_html__( 'You can get another list ID from the %splugin settings%s.', 'cleverreach-extension' ), '<a href="' . admin_url( 'options-general.php?page=cleverreach-extension' ) . '">', '</a>' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="<?php echo esc_attr( $args['content'] . '-source' ); ?>"><?php echo esc_html__( 'Source', 'cleverreach-extension' ); ?></label>
							</th>
							<td>
								<input type="text" name="source" class="oneline option" id="<?php echo esc_attr( $args['content'] . '-source' ); ?>" value="<?php echo esc_html( $defined_options['source'] ); ?>" />
								<p class="description"><?php echo esc_html__( '(optional)', 'cleverreach-extension' ); ?></p>
							</td>
						</tr>

					</tbody>
				</table>
			</fieldset>
		</div>

		<div class="insert-box">
			<input type="text" name="<?php echo esc_attr( $args['id'] ); ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

			<div class="submitbox">
				<input type="button" class="button button-primary insert-tag" value="<?php echo esc_html__( 'Insert Tag', 'contact-form-7' ); ?>" />
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
		if ( 'form' === $prop && 'cleverreach-signup' === $current ) {

			$template = $this->get_form_template();

		}

		return $template;

	}

	/**
	 * CleverReach from template for Contact From 7.
	 *
	 * @since   0.3.0
	 *
	 * @return string
	 */
	private function get_form_template() {

		$defined_options = $this->helper->get_option_group();

		return '[cleverreach_extension list:' . esc_attr( $defined_options['list_id'] ) . ' source:' . esc_html( $defined_options['source'] ) . ']' . "\n\n" .
				'<label>' . esc_html__( 'Gender', 'cleverreach-extension' ) . '</label><br />' . "\n" .
				'[select gender id:cre_gender "' . esc_html__( 'Unknown', 'cleverreach-extension' ) . '" "' . esc_html__( 'Female', 'cleverreach-extension' ) . '" "' . esc_html__( 'Male', 'cleverreach-extension' ) . '"]' . "\n\n" .

				'<label>' . esc_html__( 'First Name', 'cleverreach-extension' ) . '</label><br />' . "\n" .
				'[text firstname id:cre_firstname]' . "\n\n" .

				'<label>' . esc_html__( 'Last Name', 'cleverreach-extension' ) . '</label><br />' . "\n" .
				'[text lastname id:cre_lastname]' . "\n\n" .

				'<label>' . esc_html__( 'Email', 'cleverreach-extension' ) . ' ' . esc_html__( '(required)', 'cleverreach-extension' ) . '</label><br />' . "\n" .
				'[email* email id:cre_email]' . "\n\n" .

				'[submit id:cre_submit "' . esc_html__( 'Submit', 'cleverreach-extension' ) . '"]';

	}

	/**
	 * Filter Contact Form 7 form elements.
	 *
	 * @since   0.3.0
	 *
	 * @param   $content
	 *
	 * @wp-hook wpcf7_form_elements
	 * @return  mixed HTML
	 */
	public function filter_form_elements( $content ) {

		// Filter everything in `the_content` if `wpcf7_form_elements` contains the plugin tag.
		$matches = preg_match( $this->pattern, $content );
		if ( $matches ) {

			wp_enqueue_script( 'cleverreach-extension' );
			add_filter( 'the_content', array( $this, 'filter_the_content' ), 9, 1 );

		}

		return $content;

	}

	/**
	 * Filter `the_content` to update the form wrapper and elements.
	 *
	 * @since   0.3.0
	 *
	 * @param   $content
	 *
	 * @wp-hook the_content
	 * @return  string HTML
	 */
	public function filter_the_content( $content ) {

		$content = $this->filter_form_wrapper( $content );
		$content = $this->filter_form_element( $content );
		$content = $this->filter_form_content( $content );

		return $content;

	}

	/**
	 * Filter Contact Form 7 form wrapper.
	 * Update form wrapper to match CleverReach instead of Contact Form 7.
	 *
	 * @since   0.3.1
	 *
	 * @param   $content
	 *
	 * @return  string HTML
	 */
	private function filter_form_wrapper( $content ) {

		return str_replace( 'class="wpcf7"', 'class="wpcf7 cr_form-container"', $content );

	}

	/**
	 * Filter Contact Form 7 form element.
	 * Add data attributes to form tag.
	 *
	 * @since   0.3.0
	 *
	 * @param   $content
	 *
	 * @return  string HTML
	 */
	private function filter_form_element( $content ) {

		// Re-parse raw attributes as HTML data attributes for each form.
		preg_match_all( $this->pattern, $content, $matches );
		foreach ( $matches[1] as $match ) {

			$data_attr = '';
			$attributes = explode( ' ', trim( $match ) );
			foreach( $attributes as $attribute ) {

				$attribute = str_replace( ':', '="', esc_attr( $attribute ) );
				$data_attr .= ' data-' . $attribute . '"';

			}

			// Check for first plain default Contact Form 7 form class name and append HTML data attributes.
			$pos = strpos( $content, 'class="wpcf7-form');
			if ( false !== $pos ) {
				$content = substr_replace( $content, $data_attr, $pos, strlen( 'class="wpcf7-form"' ) .'class="wpcrcf7-form');
			}

		}

		return $content;

	}

	/**
	 * Filter Contact Form 7 form content.
	 * Remove all plugin tags from HTML output.
	 *
	 * @since   0.3.0
	 *
	 * @param   $content
	 *
	 * @return  string HTML
	 */
	private function filter_form_content( $content ) {

		return preg_replace( $this->pattern, '', $content, -1 );

	}

}