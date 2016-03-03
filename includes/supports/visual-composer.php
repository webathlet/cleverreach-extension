<?php
/**
 * Visual Composer Integration
 * Hooks into `cleverreach_extension` shortcode
 *
 * @since 0.2.0
 *
 * Tested and optimized for Visual Composer v4.10
 * @link https://vc.wpbakery.com/
 * @docs https://wpbakery.atlassian.net/wiki/display/VC/Visual+Composer+Pagebuilder+for+WordPress
 */

use CleverreachExtension\Core;
use CleverreachExtension\Core\Api;

$list_values = array();
$form_values = array();

$client = new Api\Cleverreach();
$helper = new Core\Cre_Helper();
$defined_options = $helper->get_option_group();

$wiki_url = esc_url( 'https://github.com/hofmannsven/cleverreach-extension/wiki' );

if ( $client->has_valid_api_key() && $defined_options['list_id'] ) {

	$group = new Api\Cleverreach_Group_Adapter( $client );
	$form = new Api\Cleverreach_Form_Adapter( $client );

	$lists = $helper->parse_list( $group->get_list(), 'list_id' );
	$forms = $helper->parse_list( $form->get_list( $defined_options['list_id'] ), 'form_id', true );

	// Prepare drop down lists in terms of Visual Composer.
	foreach ( $lists as $list ) {
		$list_values[ $list['name'] ] = $list['id'];
	}

	foreach ( $forms as $form ) {
		$form_values[ $form['name'] ] = $form['id'];
	}

}

vc_map(
	array(
		'name'              => esc_html__( 'CleverReach Form', 'cleverreach-extension' ),
		'base'              => 'cleverreach_extension',
		'class'             => 'cleverreach_extension',
		'icon'              => '',
		'category'          => esc_html__( 'CleverReach', 'cleverreach-extension' ),
		'admin_enqueue_js'  => array(),
		'admin_enqueue_css' => array(),
		'params'            => array(
			array(
				'type'        => 'dropdown',
				'holder'      => 'div',
				'class'       => 'cre_list_id',
				'heading'     => esc_html__( 'List', 'cleverreach-extension' ),
				'param_name'  => 'list_id',
				'value'       => $list_values,
				'std'         => $defined_options['list_id'] // Use default value from plugin options.
			),
			array(
				'type'        => 'dropdown',
				'holder'      => 'div',
				'class'       => 'cre_form_id',
				'heading'     => esc_html__( 'Form', 'cleverreach-extension' ),
				'param_name'  => 'form_id',
				'value'       => $form_values,
				'std'         => $defined_options['form_id'], // Use default value from plugin options.
				'description' => sprintf( esc_html__( 'Please check the %swiki%s on how to apply further customization.', 'cleverreach-extension' ), '<a href="' . $wiki_url . '">', '</a>' )
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => 'cre_source',
				'heading' => esc_html__( 'Source', 'cleverreach-extension' ),
				'param_name' => 'source',
				'value' => $defined_options['source'], // Use default value from plugin options.
				'description' => esc_html__( '(optional)', 'cleverreach-extension' )
			)
		)
	)
);