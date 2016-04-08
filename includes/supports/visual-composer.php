<?php namespace CleverreachExtension\Core\Supports;

use CleverreachExtension\Core;
use CleverreachExtension\Core\Api;

/**
 * Visual Composer plugin integration.
 * Hooks into `cleverreach_extension` shortcode.
 *
 * @since      0.3.0
 * @package    Cleverreach_Extension
 * @subpackage Cleverreach_Extension/includes/supports
 * @link       https://vc.wpbakery.com/
 * @docs       https://wpbakery.atlassian.net/wiki/display/VC/Visual+Composer+Pagebuilder+for+WordPress
 * @author     Sven Hofmann <info@hofmannsven.com>
 */
class Visual_Composer {

	/**
	 * Load API.
	 *
	 * @since  0.3.0
	 * @access protected
	 * @var    Api\Cleverreach
	 */
	protected $client;

	/**
	 * Load helper.
	 *
	 * @since  0.3.0
	 * @access protected
	 * @var    Core\Cre_Helper
	 */
	protected $helper;

	/**
	 * URL to the official plugin wiki.
	 *
	 * @since  0.3.0
	 * @access protected
	 * @var    string
	 */
	protected $wiki_url = 'https://github.com/hofmannsven/cleverreach-extension/wiki';

	/**
	 * Visual Composer constructor.
	 */
	public function __construct() {

		$this->client = new Api\Cleverreach();
		$this->helper = new Core\Cre_Helper();

	}

	/**
	 * Init Visual Composer plugin integration.
	 * Hooks into `vc_map` function from Visual Composer.
	 *
	 * @since 0.3.0
	 */
	public function init() {

		$defined_options = $this->helper->get_option_group();

		vc_map(
			array(
				'name'              => esc_html__( 'CleverReach Form', 'cleverreach-extension' ),
				'description'       => esc_html__( 'Place CleverReach Form', 'cleverreach-extension' ),
				'base'              => 'cleverreach_extension',
				'class'             => 'cleverreach_extension',
				'icon'              => 'icon-wpb-cleverreach',
				'category'          => esc_html__( 'Content', 'js_composer' ), // Get category translation from Visual Composer.
				'admin_enqueue_js'  => array(),
				'admin_enqueue_css' => array(
					esc_url( plugins_url( 'admin/css/cleverreach-extension-admin-vc.css', dirname( dirname( __FILE__ ) ) ) )
				),
				'params'            => array(
					array(
						'type'       => 'dropdown',
						'holder'     => 'div',
						'class'      => 'cre_list_id',
						'heading'    => esc_html__( 'List', 'cleverreach-extension' ),
						'param_name' => 'list_id',
						'value'      => $this->get_values( 'list' ),
						'std'        => $defined_options['list_id'] // Use default value from plugin options.
					),
					array(
						'type'        => 'dropdown',
						'holder'      => 'div',
						'class'       => 'cre_form_id',
						'heading'     => esc_html__( 'Form', 'cleverreach-extension' ),
						'param_name'  => 'form_id',
						'value'       => $this->get_values( 'form' ),
						'std'         => $defined_options['form_id'], // Use default value from plugin options.
						'description' => sprintf(
							esc_html__( 'Please check the %swiki%s on how to apply further customization.', 'cleverreach-extension' ),
							'<a href="' . esc_url( $this->wiki_url ) . '">',
							'</a>'
						)
					),
					array(
						'type'        => 'checkbox',
						'holder'      => 'div',
						'class'       => 'cre_custom_form',
						'heading'     => esc_html__( 'Enable custom form', 'cleverreach-extension' ),
						'param_name'  => 'custom_form',
						'value'       => '',
						'std'         => 0
					),
					array(
						'type'        => 'textfield',
						'holder'      => 'div',
						'class'       => 'cre_source',
						'heading'     => esc_html__( 'Source', 'cleverreach-extension' ),
						'param_name'  => 'source',
						'value'       => $defined_options['source'], // Use default value from plugin options.
						'description' => esc_html__( '(optional)', 'cleverreach-extension' )
					)
				)
			)
		);

	}

	/**
	 * Formats array for drop down in terms of Visual Composer.
	 *
	 * @since  0.3.0
	 * @access private
	 * @param  string
	 *
	 * @return array
	 */
	private function get_values( $param ) {

		$return = array();
		$items = array();

		$defined_options = $this->helper->get_option_group();

		if ( $this->client->has_valid_api_key() && $defined_options['list_id'] ) {

			if ( 'list' == $param ) {

				$group = new Api\Cleverreach_Group_Adapter( $this->client );
				$items = $this->helper->parse_list( $group->get_list(), 'list_id' );

			} elseif ( 'form' == $param ) {

				$form  = new Api\Cleverreach_Form_Adapter( $this->client );
				$items = $this->helper->parse_list( $form->get_list( $defined_options['list_id'] ), 'form_id', true );

			}

			// Prepare drop down lists in terms of Visual Composer.
			foreach ( $items as $item ) {
				$return[ $item['name'] ] = $item['id'];
			}

		}

		return $return;

	}

}