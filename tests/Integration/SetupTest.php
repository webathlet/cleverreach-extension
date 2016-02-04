<?php namespace CleverreachExtension\Tests\Integration;

use CleverreachExtension\Viewpublic;

/**
 * Contains all public-specific tests.
 *
 * @since      0.3.0
 * @package    Cleverreach_Extension
 * @subpackage Cleverreach_Extension/Tests
 * @author     Sven Hofmann <info@hofmannsven.com>
 */
class SetupTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Sample test.
	 *
	 * @since 0.3.0
	 * @group setup
	 */
	function testSample() {

		$this->assertTrue( TRUE );

	}

	/**
	 * Test if plugin is active.
	 *
	 * @since 0.3.0
	 * @group setup
	 */
	function testPluginActive() {

		$this->markTestSkipped( 'Must be revisited.' ); // @TODO

		$this->assertTrue( is_plugin_active( 'cleverreach-extension/cleverreach-extension.php' ) );

	}

}