<?php namespace CleverreachExtension\Core\Api;

use CleverreachExtension\Core;

/**
 * Class to connect to CleverReach using the CleverReach Api.
 *
 * @since      0.1.0
 * @package    Cleverreach_Extension
 * @subpackage Cleverreach_Extension/includes/api
 * @link       http://api.cleverreach.com/soap/doc/5.0/
 * @author     Sven Hofmann <info@hofmannsven.com>
 */
class Cleverreach {

	/**
	 * Client error status.
	 *
	 * @since  0.3.0
	 * @access protected
	 * @var    bool
	 */
	protected $error = false;

	/**
	 * Created transients to store returned API data.
	 *
	 * @since  0.3.0
	 * @access protected
	 * @var string
	 */
	protected $transients = 'cleverreach_extension_transients';

	/**
	 * Prepare Api Key and List ID.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		$helper = new Core\Cre_Helper();
		$this->api_key = sanitize_key( trim( apply_filters( 'cleverreach_extension_api_key', $helper->get_option( 'api_key' ) ) ) );
		$this->list_id = sanitize_key( absint( trim( apply_filters( 'cleverreach_extension_list_id', $helper->get_option( 'list_id' ) ) ) ) );

	}

	/**
	 * Connect via SOAP client.
	 *
	 * @since 0.3.0
	 * @return mixed|\SoapClient
	 */
	public function get_client() {

		try {

			$client = new \SoapClient( 'https://api.cleverreach.com/soap/interface_v5.1.php?wsdl' );

		} catch ( \Exception $e ) {

			$this->delete_transients();
			error_log( $e->getMessage() );

		}

		return $client;

	}

	/**
	 * Client error status.
	 * Returns `true` if data from Soap Client could not be loaded.
	 *
	 * @since 0.3.0
	 * @return bool
	 */
	public function has_error() {

		return $this->error;

	}

	/**
	 * Checks if Api Key is valid.
	 *
	 * @since 0.1.0
	 * @return bool
	 */
	public function has_valid_api_key() {

		$status = false;

		if ( $this->api_key ) {

			try {

				$result = $this->get_client()->clientGetDetails( $this->api_key );
				if ( 'SUCCESS' === $result->status ) {
					$status = true;
				}

			} catch ( \Exception $e ) {

				$this->delete_transients();
				error_log( $e->getMessage() );

			}

		}

		return $status;

	}

	/**
	 * Retrieve data via CleverReach Api.
	 *
	 * @since 0.1.0
	 *
	 * @param string $method
	 * @param string $param
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function api_get( $method = 'clientGetDetails', $param = '' ) {

		$transient = 'cleverreach_extension_' . $method . '_' . $param;
		if ( false === ( $result = get_transient( $transient ) ) ) {

			// Store result as new transient.
			$result = $this->get_client()->$method( $this->api_key, $param );
			set_transient( $transient, $result, 12 * HOUR_IN_SECONDS );

			// Monitor created transients.
			$this->monitor_transients( $transient );

		}

		if ( 'SUCCESS' !== $result->status ) {

			$this->delete_transients();
			throw new \Exception( esc_html__( 'Your API key is invalid.', 'cleverreach-extension' ) );

		}

		return $result;

	}

	/**
	 * Monitor created transients.
	 *
	 * @since 0.3.0
	 *
	 * @param $transient string Name of the transient.
	 */
	private function monitor_transients( $transient ) {

		$created_transients   = get_option( $this->transients, array() );
		$created_transients[] = $transient;

		update_option( $this->transients, $created_transients );

	}

	/**
	 * Delete all created transients.
	 *
	 * @since 0.3.0
	 */
	public function delete_transients() {

		$created_transients = get_option( $this->transients, array() );
		foreach ( $created_transients as $created_transient ) {
			delete_transient( $created_transient );
		}

		update_option( $this->transients, array() );

	}

	/**
	 * Post data to custom list via CleverReach Api.
	 *
	 * @since 0.3.0
	 *
	 * @param        $method
	 * @param string $list_id
	 * @param array  $param
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function api_post_to_list( $method, $param = array(), $list_id = '' ) {

		if ( empty( $list_id ) ) {
			$list_id = $this->list_id;
		} else {
			$list_id = sanitize_key( absint( trim( $list_id ) ) );
		}

		$result = $this->get_client()->$method( $this->api_key, $list_id, $param );

		if ( 'SUCCESS' !== $result->status ) {
			throw new \Exception( esc_html__( $result->message ) );
		}

		return $result;

	}

	/**
	 * Send mail via CleverReach Api.
	 *
	 * @param $method
	 * @param $form_id
	 * @param $email
	 * @param $data
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function api_send_mail( $method, $form_id, $email, $data ) {

		$form_id = sanitize_key( absint( trim( apply_filters( 'cleverreach_extension_form_id', $form_id ) ) ) );
		$result = $this->get_client()->$method( $this->api_key, $form_id, $email, $data );

		if ( 'SUCCESS' !== $result->status ) {
			throw new \Exception( esc_html__( $result->message ) );
		}

		return $result;

	}

}