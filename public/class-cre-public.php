<?php namespace CleverreachExtension\Viewpublic;

use CleverreachExtension\Core\Api;
use CleverreachExtension\Core\Cre_Helper;

/**
 * Contains all public-specific functionality of the plugin.
 *
 * @since      0.1.0
 * @package    Cleverreach_Extension
 * @subpackage Cleverreach_Extension/public
 * @author     Sven Hofmann <info@hofmannsven.com>
 */
class Cre_Public {

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

	}

	/**
	 * Register the localized public scripts for this plugin.
	 *
	 * @since   0.1.0
	 *
	 * @wp-hook wp_enqueue_scripts
	 */
	public function enqueue_scripts() {

		// Check if script debug mode is active.
		$minified = ( SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script(
			$this->plugin_slug,
			plugin_dir_url( __FILE__ ) . 'js/cleverreach-extension-public' . $minified . '.js',
			array( 'jquery' ),
			$this->plugin_version,
			true
		);

		wp_register_script(
			$this->plugin_slug . '-fallback',
			plugin_dir_url( __FILE__ ) . 'js/cleverreach-extension-fallback' . $minified . '.js',
			array( 'jquery', $this->plugin_slug ),
			$this->plugin_version,
			true
		);

		wp_localize_script(
			$this->plugin_slug,
			'cre',
			array(
				'ajaxurl'            => esc_url( apply_filters( 'cleverreach_extension_ajaxurl', admin_url( 'admin-ajax.php' ) ) ),
				'nonce'              => wp_create_nonce( $this->plugin_name . '_ajax_interaction_nonce' ),
				'loading'            => sanitize_text_field( apply_filters( 'cleverreach_extension_loading_msg', esc_html__( 'Saving...', 'cleverreach-extension' ) ) ),
				'success'            => sanitize_text_field( apply_filters( 'cleverreach_extension_success_msg', esc_html__( 'Please check your email to confirm your subscription.', 'cleverreach-extension' ) ) ),
				'error'              => sanitize_text_field( apply_filters( 'cleverreach_extension_error_msg', esc_html__( 'Sorry, there was a problem saving your data. Please try later or contact the administrator.', 'cleverreach-extension' ) ) ),
				'selector'           => esc_attr( '.' ), // Selector supports only classes, yet.
				'container_selector' => sanitize_html_class( apply_filters( 'cleverreach_extension_container_selector', 'cr_form-container' ) ),
				'loading_selector'   => sanitize_html_class( apply_filters( 'cleverreach_extension_loading_selector', 'cr_loading' ) ),
				'success_selector'   => sanitize_html_class( apply_filters( 'cleverreach_extension_success_selector', 'cr_success' ) ),
				'response_selector'  => sanitize_html_class( apply_filters( 'cleverreach_extension_response_selector', 'cr_response' ) ),
				'error_selector'     => sanitize_html_class( apply_filters( 'cleverreach_extension_error_selector', 'cr_error' ) )
			)
		);

	}

	/**
	 * Parse form submission via ajax and return status response.
	 *
	 * @since   0.1.0
	 *
	 * @wp-hook wp_ajax_nopriv_cre_ajax_controller_interaction
	 * @wp-hook wp_ajax_cre_ajax_controller_interaction
	 */
	public function ajax_controller_interaction() {

		check_ajax_referer( $this->plugin_name . '_ajax_interaction_nonce', 'nonce' );

		$result = $post_attr = array();

		// @TODO: if ( $_POST['cr_form'] ) {

		// Parse serialized ajax post data as `$post` (array).
		parse_str( $_POST['cr_form'], $post ); // @TODO: Get rid of `parse_str()`

		if ( is_email( $post['email'] ) ) :

			// Prepare receiver adapter.
			$helper   = new Cre_Helper();
			$client   = new Api\Cleverreach();
			$receiver = new Api\Cleverreach_Receiver_Adapter( $client );
			$group    = new Api\Cleverreach_Group_Adapter( $client );

			// Populate `$post_attr` (array) according to CleverReach API defaults.
			foreach ( $post as $key => $value ) {

				// Join values if $value is an array.
				if ( is_array( $value ) ) {
					$value = join( ', ', $value );
				}

				if ( 'email' != $key ) { // Skip 'email' as this is not needed as separate attribute.

					// Create fields for default forms.
					if ( 'true' === $helper->get_option( 'ajax' ) ) {

						// Attribute `$key` may only contain lowercase a-z and 0-9. Everything else will be converted to `_`.
						$key = strtolower( $key );
						$key = str_replace( ' ', '_', $key );
						$key = str_replace( '-', '_', $key );
						$key = preg_replace( '/[^A-Za-z0-9\-]/', '_', $key );

						if ( $value && $key[ 0 ] !== '_' ) { // Exclude (hidden) fields starting with an underscore.
							$group->attribute_add( $key, 0 );
						}

					}

					array_push(
						$post_attr,
						array(
							'key'   => sanitize_html_class( $key ),
					        'value' => sanitize_text_field( $value )
						)
					);
				}

			}

			// Populate `$source` (string)
			$source = get_bloginfo( 'name' );
			if ( isset( $_POST['source'] ) ) {
				$source = $_POST['source'];
			} elseif ( $helper->has_option( 'source' ) ) {
				$source = $helper->get_option( 'source' );
			}

			// Populate `$user` (array) according to CleverReach API defaults.
			$user = array(
				'email'      => sanitize_email( $post['email'] ),
				'registered' => time(),
				// 'activated' => time(), // Force double opt-in.
				'source'     => esc_html( trim( $source ) ),
				'attributes' => $post_attr
			);

			$list_id = ( empty( $_POST[ 'list' ] ) ? $helper->get_option( 'list_id' ) : (int) $_POST[ 'list' ] );
			$receiver_added = $receiver->add( $user, $list_id );

			// Test returned data.
			if ( is_object( $receiver_added ) && 'SUCCESS' == $receiver_added->status ) {

				$result['type'] = 'success';

				// Prepare form adapter.
				$form = new Api\Cleverreach_Form_Adapter( $client );

				// Send activation mail.
				$user_data = array(
					'user_ip'    => '127.0.0.1', // Populate `user_ip` with fake IP address.
					'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:14.0) Gecko/20100101 Firefox/14.0.1', // Populate `user_agent` also fake data.
					'referer'    => esc_url( home_url() ),
				);

				$form_id = ( empty( $_POST[ 'form' ] ) ? $helper->get_option( 'form_id' ) : (int) $_POST[ 'form' ] );
				$form->send_activation_mail( $form_id, sanitize_email( $post['email'] ), $user_data );

			} else {

				$result['type'] = 'error';

				if ( 'duplicate data' == $receiver_added ) {
					$result['status'] = sanitize_text_field( apply_filters( 'cleverreach_extension_error_msg_duplicate', esc_html__( 'It seems like you\'re already subscribed to our list.', 'cleverreach-extension' ) ) );
				} else {
					$result['status'] = sanitize_text_field( apply_filters( 'cleverreach_extension_error_msg_common', esc_html__( 'Sorry, there seems to be a problem with your data.', 'cleverreach-extension' ) ) );
				}

			} // end of is_object() && 'SUCCESS'

		else :

			$result['type']   = 'error';
			$result['status'] = sanitize_text_field( apply_filters( 'cleverreach_extension_error_msg_invalid_email', esc_html__( 'Sorry, there seems to be a problem with your email address.', 'cleverreach-extension' ) ) );

		endif; // end of is_email()

		// Finally return JSON result.
		$result = json_encode( $result );
		echo $result;
		die();

	}

}
