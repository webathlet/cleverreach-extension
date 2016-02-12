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
	 * Define connection via SOAP client and Api Key.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		try {

			$this->client = new \SoapClient( 'http://api.cleverreach.com/soap/interface_v5.1.php?wsdl' );

			$helper = new Core\Cre_Helper();
			$this->api_key = sanitize_key( trim( apply_filters( 'cleverreach_extension_api_key', $helper->get_option( 'api_key' ) ) ) );
			$this->list_id = sanitize_key( absint( trim( apply_filters( 'cleverreach_extension_list_id', $helper->get_option( 'list_id' ) ) ) ) );

		} catch ( \Exception $e ) {

			error_log( $e->getMessage() );
			$this->error = true;

		}

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
				$result = $this->client->clientGetDetails( $this->api_key );
				if ( 'SUCCESS' == $result->status ) {
					$status = true;
				}
			} catch ( \Exception $e ) {
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

		$result = $this->client->$method( $this->api_key, $param );

		if ( 'SUCCESS' != $result->status ) {
			throw new \Exception( esc_html__( 'Your API key is invalid.', 'cleverreach-extension' ) );
		}

		return $result;

	}

	/**
	 * Post data via CleverReach Api.
	 *
	 * @since 0.1.0
	 *
	 * @param       $method
	 * @param array $param
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function api_post( $method, $param = array() ) {

		$result = $this->client->$method( $this->api_key, $this->list_id, $param );

		if ( 'SUCCESS' != $result->status ) {
			throw new \Exception( esc_html__( $result->message ) );
		}

		return $result;

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

		$result = $this->client->$method( $this->api_key, $list_id, $param );

		if ( 'SUCCESS' != $result->status ) {
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

		// @TODO: sanitize input and apply_filters()
		// $form_id = sanitize_key( absint( trim( apply_filters( 'cleverreach_extension_form_id', $form_id ) ) ) );
		$result = $this->client->$method( $this->api_key, $form_id, $email, $data );

		if ( 'SUCCESS' != $result->status ) {
			throw new \Exception( esc_html__( $result->message ) );
		}

		return $result;

	}

}