<?php namespace CleverreachExtension\Tests\Integration;

use CleverreachExtension\Core\Cleverreach_Extension;
use CleverreachExtension\Viewadmin;

/**
 * Contains all admin-specific tests.
 *
 * @since      0.3.0
 * @package    Cleverreach_Extension
 * @subpackage Cleverreach_Extension/Tests
 * @author     Sven Hofmann <info@hofmannsven.com>
 */
class AdminTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Viewadmin\Cre_Admin
	 */
	private $plugin;

	private $plugin_name = 'CleverReach Extension';

	private $plugin_slug = 'cleverreach-extension';

	private $plugin_version = '0.0.0';

	public function setUp() {

		new CleverReach_Extension( 'CleverReach Extension', 'cleverreach-extension', 'cleverreach-extension', 'cleverreach-extension/cleverreach-extension.php', '0.3.0' );
		$this->plugin = new Viewadmin\Cre_Admin( $this->plugin_name, $this->plugin_slug, $this->plugin_version );

	}

	public function tearDown() {

		$this->plugin = NULL;

	}

	/**
	 * Test if admin styles are enqueued.
	 *
	 * @since 0.3.0
	 * @group admin
	 */
	function testAdminStylesEnqueue() {

		$settings_page = 'settings_page_' . $this->plugin_slug;
		$this->plugin->admin_enqueue_styles( $settings_page );
		$this->assertTrue( wp_style_is( $this->plugin_name . '_admin' ) );

	}

	/**
	 * Test if admin scripts are enqueued.
	 *
	 * @since 0.3.0
	 * @group admin
	 */
	function testAdminScriptsEnqueue() {

		$settings_page = 'settings_page_' . $this->plugin_slug;
		$this->plugin->admin_enqueue_scripts( $settings_page );
		$this->assertTrue( wp_script_is( $this->plugin_name . '_admin' ) );

	}

}