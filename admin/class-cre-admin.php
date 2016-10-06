<?php namespace CleverreachExtension\Viewadmin;

use CleverreachExtension\Core;
use CleverreachExtension\Core\Api;

/**
 * Contains all admin-specific functionality of the plugin.
 *
 * @since      0.1.0
 * @package    Cleverreach_Extension
 * @subpackage Cleverreach_Extension/admin
 * @author     Sven Hofmann <info@hofmannsven.com>
 */
class Cre_Admin {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	private $plugin_name;

	/**
	 * The unique identifier slug of this plugin.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string $plugin_slug The string used to uniquely identify this plugin.
	 */
	private $plugin_slug;

	/**
	 * The current version of the plugin.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string $plugin_version The current version of the plugin.
	 */
	private $plugin_version;

	/**
	 * Define the admin-specific functionality of the plugin.
	 *
	 * @since 0.1.0
	 *
	 * @param string $plugin_name    The name of this plugin.
	 * @param string $plugin_slug    The slug of this plugin.
	 * @param string $plugin_version The current version of this plugin.
	 */
	public function __construct( $plugin_name, $plugin_slug, $plugin_version ) {

		$this->plugin_name    = $plugin_name;
		$this->plugin_slug    = $plugin_slug;
		$this->plugin_version = $plugin_version;

		$helper        = new Core\Cre_Helper();
		$this->api_key = $helper->get_option( 'api_key' );
		$this->list_id = $helper->get_option( 'list_id' );
		$this->list_auto_fields    = $helper->get_option( 'list_auto_fields' );
		$this->form_id = $helper->get_option( 'form_id' );
		$this->source  = $helper->get_option( 'source' );
		$this->ajax    = $helper->get_option( 'ajax' );

	}

	/**
	 * Register the admin styles for this plugin.
	 *
	 * @since   0.2.0
	 *
	 * @wp-hook admin_enqueue_scripts
	 * @param   $hook
	 */
	public function admin_enqueue_styles( $hook ) {

		// Enqueue scripts for plugin options page only.
		if ( 'settings_page_' . $this->plugin_slug != $hook ) {
			return;
		}

		wp_register_style(
			$this->plugin_slug . '-admin',
			plugin_dir_url( __FILE__ ) . 'css/cleverreach-extension-admin.css',
			array(),
			$this->plugin_version,
			'all'
		);

		wp_enqueue_style( $this->plugin_slug . '-admin' );

	}

	/**
	 * Register the localized admin scripts for this plugin.
	 *
	 * @since   0.2.0
	 *
	 * @wp-hook admin_enqueue_scripts
	 * @param   $hook
	 */
	public function admin_enqueue_scripts( $hook ) {

		// Enqueue scripts for plugin options page only.
		if ( 'settings_page_' . $this->plugin_slug != $hook ) {
			return;
		}

		wp_register_script(
			$this->plugin_slug . '-admin',
			plugin_dir_url( __FILE__ ) . 'js/cleverreach-extension-admin.js',
			array( 'jquery' ),
			$this->plugin_version,
			true
		);

		wp_localize_script(
			$this->plugin_slug . '-admin', 'cre_admin',
			array(
				'ajaxurl'            => esc_url( admin_url( 'admin-ajax.php' ) ),
				'nonce'              => wp_create_nonce( $this->plugin_name . '_admin_ajax_interaction_nonce' ),
				'selector'           => esc_attr( '.' ), // Selector supports only classes, yet.
				'container_selector' => sanitize_html_class( 'cre-admin-form-container' ),
				'response_selector'  => sanitize_html_class( 'cre-js-response' ),
				'key_selector'       => sanitize_html_class( 'cre-admin-input-key' ),
				'list_selector'      => sanitize_html_class( 'cre-admin-select-list' ),
				'form_selector'      => sanitize_html_class( 'cre-admin-select-form' ),
				'source_selector'    => sanitize_html_class( 'cre-admin-input-source' ),
				'ajax_selector'      => sanitize_html_class( 'cre-admin-input-ajax' ),
				'list_auto_fields_selector'      => sanitize_html_class( 'cre-admin-input-list_auto_fields' ),
				'list_empty'         => esc_html__( 'Please select a list', 'cleverreach-extension' ),
				'form_empty'         => esc_html__( 'Please select a form', 'cleverreach-extension' )
			)
		);

		wp_enqueue_script( $this->plugin_slug . '-admin' );

	}

	/**
	 * Save data and return API response as JSON.
	 *
	 * @since   0.3.0
	 *
	 * @wp-hook wp_ajax_cre_admin_ajax_controller_interaction
	 * @return  array Return JSON `$result` with API response.
	 */
	public function admin_ajax_controller_interaction() {

		check_ajax_referer( $this->plugin_name . '_admin_ajax_interaction_nonce', 'nonce' );

		if ( $_POST['cr_admin_form'] && current_user_can( 'manage_options' ) ) { // Requires $_POST from admin user.

			// Create or update database entry.
			update_option( 'cleverreach_extension', $this->sanitize( $_POST['cr_admin_form'] ) );

			// Get plugin option group.
			$helper = new Core\Cre_Helper();
			$defined_options = $helper->get_option_group();

			// Prepare API with adapters.
			$client = new Api\Cleverreach();
			$group = new Api\Cleverreach_Group_Adapter( $client );
			$form = new Api\Cleverreach_Form_Adapter( $client );

			// Cleanup transients.
			$client->delete_transients();

			// Check API key.
			$has_api_key = $client->has_valid_api_key();

			// Build array with data and status.
			$result = array(
				'api' => array(
					'status' => ( $has_api_key ) ? true : false
				),
				'list' => array(
					'status' => ( $has_api_key && $defined_options['list_id'] ) ? true : false,
					'options' => ( $has_api_key ) ? $helper->parse_list( $group->get_list(), 'list_id' ) : false
				),
				'form' => array(
					'status' => ( $has_api_key && $defined_options['form_id'] ) ? true : false,
					'options' => ( $has_api_key ) ? $helper->parse_list( $form->get_list( $defined_options['list_id'] ), 'form_id', true ) : false
				),
				'source' => array(
					'status' => ( $defined_options['source'] ) ? true : false
				),
				'ajax' => array(
					'status' => ( $defined_options['ajax'] ) ? true : false
				),
				'list_auto_fields' => array(
					'status' => ( $defined_options['list_auto_fields'] ) ? true : false
				)
			);

			// Return result as JSON.
			$result = json_encode( $result );
			echo $result;
			die();
		}

	}

	/**
	 * Add custom action links to plugins page.
	 *
	 * @since   0.1.0
	 *
	 * @param   array $links
	 *
	 * @wp-hook plugin_action_links_cleverreach-extension/cleverreach-extension.php
	 * @return  array
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . esc_html__( 'Settings', 'cleverreach-extension' ) . '</a>'
			),
			$links
		);

	}

	/**
	 * Add plugin options page.
	 *
	 * @since   0.1.0
	 *
	 * @wp-hook admin_menu
	 */
	public function add_options_page() {

		add_options_page(
			$this->plugin_name,
			$this->plugin_name,
			'manage_options', // Requires admin user.
			$this->plugin_slug,
			array( $this, 'render_options_page' )
		);

	}

	/**
	 * Render options page.
	 *
	 * @since 0.1.0
	 */
	public function render_options_page() {

		echo '<div class="wrap cleverreach-extension-options-page">';
		echo '<h1>' . esc_html( $this->plugin_name ) . '</h1>';

		echo wp_kses(
			$this->render_promotion_notice(),
			array(
				'p'   => array(),
				'a'   => array(
					'href' => array()
				),
				'img' => array(
					'src' => array()
				)
			)
		);

		echo '<div class="cre-admin-form-container">';
		echo '<form method="post" action="options.php">';

		settings_fields( 'cleverreach_extension_group' );
		do_settings_sections( $this->plugin_slug );

		submit_button();

		echo '</form>';
		echo '</div>'; // end of .cre-admin-form-container

		echo wp_kses(
			$this->render_shortcode_preview(),
			array(
				'div'     => array(
					'class' => array()
				),
				'h2'     => array(),
				'h3'     => array(),
				'h4'     => array(),
				'p'      => array(),
				'strong' => array(),
				'a'      => array(
					'href' => array()
				),
				'span'   => array(
					'class' => array()
				),
				'br'     => array(),
				'pre'    => array(),
				'code'   => array()
			)
		);

		echo '</div>'; // end of .wrap

	}

	/**
	 * Register settings via WordPress Settings Api
	 *
	 * @since   0.1.0
	 * @see     https://codex.wordpress.org/Settings_API
	 *
	 * @wp-hook admin_init
	 */
	public function register_settings() {

		register_setting(
			'cleverreach_extension_group',
			'cleverreach_extension',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'cleverreach_extension_setting',
			esc_html__( 'Settings', 'cleverreach-extension' ),
			array( $this, 'render_section_info' ),
			$this->plugin_slug
		);

		add_settings_field(
			'api_key',
			esc_html__( 'API Key', 'cleverreach-extension' ),
			array( $this, 'render_api_key_field' ),
			$this->plugin_slug,
			'cleverreach_extension_setting'
		);

		add_settings_field(
			'list_id',
			esc_html__( 'List', 'cleverreach-extension' ),
			array( $this, 'render_list_field' ),
			$this->plugin_slug,
			'cleverreach_extension_setting'
		);

		add_settings_field(
			'form_id',
			esc_html__( 'Form', 'cleverreach-extension' ),
			array( $this, 'render_form_field' ),
			$this->plugin_slug,
			'cleverreach_extension_setting'
		);

		add_settings_field(
			'source',
			esc_html__( 'Source', 'cleverreach-extension' ),
			array( $this, 'render_source_field' ),
			$this->plugin_slug,
			'cleverreach_extension_setting'
		);

		add_settings_section(
			'cleverreach_extension_default_forms',
			esc_html__( 'Default forms', 'cleverreach-extension' ),
			array( $this, 'render_default_form_section_info' ),
			$this->plugin_slug
		);

		add_settings_field(
			'ajax',
			esc_html__( 'Enable Ajax', 'cleverreach-extension' ),
			array( $this, 'render_ajax_field' ),
			$this->plugin_slug,
			'cleverreach_extension_default_forms'
		);

		add_settings_field(
			'list_auto_fields',
			esc_html__( 'Enable auto creating list fields', 'cleverreach-extension' ),
			array( $this, 'render_list_auto_fields_field' ),
			$this->plugin_slug,
			'cleverreach_extension_default_forms'
		);

	}

	/**
	 * Prepare input to be saved in database.
	 *
	 * @since 0.1.0
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public function sanitize( $input ) {

		$new_input = array();

		if ( isset( $input['api_key'] ) ) {
			$new_input['api_key'] = sanitize_key( trim( $input['api_key'] ) );
		}

		if ( isset( $input['list_id'] ) ) {
			$new_input['list_id'] = sanitize_key( absint( trim( $input['list_id'] ) ) );
		}

		if ( isset( $input['form_id'] ) ) {

			if ( 'custom' === $input['form_id'] ) {
				$new_input['form_id'] = 'custom';
			} else {
				$new_input['form_id'] = sanitize_key( absint( trim( $input['form_id'] ) ) );
			}

		}

		if ( isset( $input['source'] ) ) {
			$new_input['source'] = sanitize_text_field( trim( $input['source'] ) );
		}

		if ( isset( $input['ajax'] ) ) {
			$new_input['ajax'] = sanitize_key( trim( $input['ajax'] ) );
		}

		if ( isset( $input['list_auto_fields'] ) ) {
			$new_input['list_auto_fields'] = sanitize_key( trim( $input['list_auto_fields'] ) );
		}

		return $new_input;

	}

	/**
	 * Render Api Key input field and description.
	 *
	 * @since 0.1.0
	 */
	public function render_api_key_field() {

		echo '<div class="cre-input-container">';

		$client  = new Api\Cleverreach();
		printf(
			'<input type="text" class="cre-admin-input-key" size="45" name="cleverreach_extension[api_key]" value="%s" /><div class="dashicons-before cre-info-message %s cre-js-response"></div>',
			isset( $this->api_key ) ? esc_attr( $this->api_key ) : '',
			$client->has_valid_api_key() ? 'confirmed' : 'invalid'
		);

		echo '</div>'; // end of .input-container

		echo '<p class="description">' . esc_html__( 'Get your API Key: CleverReach Account » Extras » SOAP API', 'cleverreach-extension' ) . '</p>';

	}

	/**
	 * Render list input field and description.
	 *
	 * @since 0.1.0
	 */
	public function render_list_field() {

		$html = '<select class="cre-admin-select-list" name="cleverreach_extension[list_id]">';

		$client = new Api\Cleverreach();
		$helper = new Core\Cre_Helper();
		$default = esc_html__( 'Please select a list', 'cleverreach-extension' );
		if ( $client->has_valid_api_key() ) {

			$group = new Api\Cleverreach_Group_Adapter( $client );
			$html .= $helper->parse_list_html(
				$this->list_id,
				$group->get_list(),
				'list_id',
				$default
			);

		} else {
			$html .= '<option value="">' . $default . '</option>';
		}

		$html .= '</select>';

		echo '<div class="cre-input-container">';
		echo wp_kses( $html, $helper->allowed_html_select() );
		printf(
			'<div class="dashicons-before cre-info-message %s cre-js-response"></div>',
			( $client->has_valid_api_key() && $helper->has_option( 'list_id' ) ) ? 'confirmed' : 'invalid'
		);
		echo '</div>';

	}

	/**
	 * Render form input field and description.
	 *
	 * @since 0.1.0
	 */
	public function render_form_field() {

		$html = '<select class="cre-admin-select-form" name="cleverreach_extension[form_id]">';

		$client = new Api\Cleverreach();
		$helper = new Core\Cre_Helper();
		$default = esc_html__( 'Please select a form', 'cleverreach-extension' );
		if ( $client->has_valid_api_key() ) {

			$form = new Api\Cleverreach_Form_Adapter( $client );
			$html .= $helper->parse_list_html(
				$this->form_id,
				$form->get_list( $this->list_id ),
				'form_id',
				$default,
				true
			);

		} else {
			$html .= '<option value="">' . $default . '</option>';
		}

		$html .= '</select>';

		echo '<div class="cre-input-container">';
		echo wp_kses( $html, $helper->allowed_html_select() );
		printf(
			'<div class="dashicons-before cre-info-message %s cre-js-response"></div>',
			( $client->has_valid_api_key() && $helper->has_option( 'list_id' ) && $helper->has_option( 'form_id' ) ) ? 'confirmed' : 'invalid'
		);
		echo '</div>';

	}

	/**
	 * Render source input field and description.
	 *
	 * @since 0.1.0
	 */
	public function render_source_field() {

		echo '<div class="cre-input-container">';

		$source = $this->source;

		printf(
			'<input type="text" class="cre-admin-input-source" size="45" name="cleverreach_extension[source]" value="%s" />',
			isset( $source ) ? esc_attr( $source ) : ''
		);

		printf(
			'<div class="dashicons-before cre-info-message %s cre-js-response"></div>',
			! empty( $source ) ? 'confirmed' : 'optional'
		);

		echo '</div>';

		echo '<p class="description">' . esc_html__( '(optional)', 'cleverreach-extension' ) . '</p>';

	}

	/**
	 * Render ajax input field and description.
	 *
	 * @since 0.3.0
	 */
	public function render_ajax_field() {

		echo '<div class="cre-input-container">';

		$ajax = $this->ajax;

		printf(
			'<input type="checkbox" class="cre-admin-input-ajax" name="cleverreach_extension[ajax]" value="' . esc_attr( 'true' ) . '" %s />',
			checked( $ajax, 'true', 0 )
		);

		printf(
			'<div class="dashicons-before cre-info-message %s cre-js-response"></div>',
			! empty( $ajax ) ? 'confirmed' : 'optional'
		);

		echo '</div>';

		echo '<p class="description">' . esc_html__( 'Check to submit default forms without page reload.', 'cleverreach-extension' ) . '</p>';

	}

	/**
	 * Render list_auto_fields input field and description.
	 *
	 * @since 0.3.1
	 */
	public function render_list_auto_fields_field() {

		echo '<div class="cre-input-container">';

		$list_auto_fields = $this->list_auto_fields;

		printf(
			'<input type="checkbox" class="cre-admin-input-list_auto_fields" name="cleverreach_extension[list_auto_fields]" value="' . esc_attr( 'true' ) . '" %s />',
			checked( $list_auto_fields, 'true', 0 )
		);

		printf(
			'<div class="dashicons-before cre-info-message %s cre-js-response"></div>',
			! empty( $list_auto_fields ) ? 'confirmed' : 'optional'
		);

		echo '</div>';

		echo '<p class="description">' . esc_html__( 'Check to create form fields in chosen list automatically (only with activated ajax submission).', 'cleverreach-extension' ) . '</p>';

	}

	/**
	 * Render section intro message.
	 *
	 * @since 0.1.0
	 */
	public function render_section_info() {

		echo '<p>' . esc_html__( 'Use the fields below to connect WordPress and CleverReach.', 'cleverreach-extension' ) . '</p>';

		// Check if `$client` is available.
		$client = new Api\Cleverreach();
		if ( $client->has_error() ) {
			echo '<p class="error">';
			echo '<strong>' . esc_html__( 'Error:', 'cleverreach-extension' ) . '</strong> ';
			esc_html_e( 'Could not load data from CleverReach.', 'cleverreach-extension' );
			echo '<p>';
		}

	}

	/**
	 * Render section intro message.
	 *
	 * @since 0.3.0
	 */
	public function render_default_form_section_info() {

	}

	/**
	 * Render promotion section.
	 * For users without api key only.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function render_promotion_notice() {

		$result = '';

		$helper = new Core\Cre_Helper();

		if ( ! $helper->has_option( 'api_key' ) ) {

			$signup_url = 'https://www.cleverreach.com/land/account?rk=85097mbwkysub';
			$banner_url = 'https://s3-eu-west-1.amazonaws.com/cloud-files.crsend.com/img/affiliate/en/468_60.png';

			$result .= '<p>';
			$result .= esc_html__( 'Still need a CleverReach account?', 'cleverreach-extension' ) . ' ';
			$result .= '<a href="' . esc_url( $signup_url ) . '">' . esc_html__( 'Sign up for free!', 'cleverreach-extension' ) . '</a>';
			$result .= '</p>';

			$result .= '<p><a href="' . esc_url( $signup_url ) . '"><img src="' . esc_url( $banner_url ) . '" /></a></p>';

		}

		return $result;

	}

	/**
	 * Render shortcode preview section.
	 *
	 * @since  0.3.0
	 *
	 * @return string
	 */
	public function render_shortcode_preview() {

		$result = '<div class="card">';

		$result .= '<h2 class="title">' . esc_html__( 'Your Shortcode', 'cleverreach-extension' ) . '</h2>';
		$result .= '<p>';
		$result .= esc_html__( 'Embed the shortcodes below anywhere on your site.', 'cleverreach-extension' );
		$result .= '</p>';

		$result .= '<h4>' . esc_html__( 'Default usage:', 'cleverreach-extension' ) . '</h4>';
		$result .= '<pre><code>[cleverreach_extension]</code></pre>';

		$result .= '<h4>' . esc_html__( 'Advanced usage:', 'cleverreach-extension' ) . '</h4>';
		$list_id = $this->list_id ? $this->list_id : '';
		$form_id = $this->form_id ? $this->form_id : '';
		$source = $this->source ? $this->source : '';
		$result .= '<pre><code>[cleverreach_extension list_id="<span class="cre-admin-select-list-preview">' . $list_id . '</span>" form_id="<span class="cre-admin-select-form-preview">' . $form_id . '</span>" source="<span class="cre-admin-input-source-preview">' . $source . '</span>"]</code></pre>';

		$result .= '<p>';
		$result .= esc_html__( 'Add the additional parameters to the shortcode, if you need to support multiple forms on one page.', 'cleverreach-extension' ) . ' ';
		$wiki_url = esc_url( 'https://github.com/hofmannsven/cleverreach-extension/wiki' );
		$result .= sprintf( esc_html__( 'Please check the %swiki%s on how to apply further customization.', 'cleverreach-extension' ), '<a href="' . $wiki_url . '">', '</a>' );
		$result .= '</p>';

		$result .= '</div>'; // end of .card

		return $result;

	}

}