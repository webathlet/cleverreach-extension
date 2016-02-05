<?php
/**
 * CleverReach WordPress Extension
 *
 * @package     Cleverreach_Extension
 * @author      Sven Hofmann <info@hofmannsven.com>
 * @license     GPLv3
 * @link        https://github.com/hofmannsven/cleverreach-extension
 *
 * @wordpress-plugin
 * Plugin Name: CleverReach Extension
 * Plugin URI:  https://github.com/hofmannsven/cleverreach-extension
 * Description: Simple interface for CleverReach newsletter software using the official CleverReach SOAP API.
 * Version:     0.3.0
 * Author:      CODE64
 * Author URI:  https://code64.de
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: cleverreach-extension
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check requirements during plugin activation.
 *
 * @since 0.1.0
 */
register_activation_hook( __FILE__, 'cleverreachextension_check_requirements' );
function cleverreachextension_check_requirements() {

	// Define plugin requirements.
	$required_php_version   = '5.3.0';
	$required_php_extension = 'soap';

	// Check requirements.
	if ( version_compare( PHP_VERSION, $required_php_version, '<=' ) ) {

		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			sprintf( esc_html__( 'CleverReach Extension plugin requires PHP %s or greater.', 'cleverreach-extension' ), $required_php_version ),
			esc_html__( 'Plugin Activation Error', 'cleverreach-extension' ),
			array( 'back_link' => true )
		);

	} elseif ( ! extension_loaded( $required_php_extension ) ) {

		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			sprintf( esc_html__( 'CleverReach Extension plugin requires PHP %s extension.', 'cleverreach-extension' ), strtoupper( $required_php_extension ) ),
			esc_html__( 'Plugin Activation Error', 'cleverreach-extension' ),
			array( 'back_link' => true )
		);

	} else {
		return;
	}

}

/**
 * TODO: Cleanup database during plugin deactivation.
 */
// register_deactivation_hook( __FILE__, 'cleverreachextension_cleanup' );

/**
 * Run plugin if everything is ready.
 *
 * @since 0.3.0
 */
add_action( 'plugins_loaded', 'cleverreachextension_run' );
function cleverreachextension_run() {

	$plugin_dir_path = plugin_dir_path( __FILE__ );
	require_once $plugin_dir_path . 'includes/class-cleverreach-extension.php';

	$plugin = new \CleverreachExtension\Core\CleverReach_Extension();
	$plugin->run();

}