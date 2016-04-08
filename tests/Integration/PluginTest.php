<?php namespace CleverreachExtension\Tests\Integration;

use WP_UnitTestCase;
use CleverreachExtension\Core;

/**
 * Contains all public-specific tests.
 *
 * @since      0.3.0
 * @package    Cleverreach_Extension
 * @subpackage Cleverreach_Extension/Tests
 * @author     Sven Hofmann <info@hofmannsven.com>
 */
class PluginTest extends \WP_UnitTestCase {

	/**
	 * @var Core\Cleverreach_Extension
	 */
	private $plugin;

	public function setUp() {

		$this->plugin = new Core\Cleverreach_Extension();
		$this->plugin->run();

	}

	public function tearDown() {

		$this->plugin = NULL;

	}

	/**
	 * Test if shortcode is parsed.
	 *
	 * @since 0.3.0
	 * @group public
	 */
	public function testParsedShortcode() {

		// Create new post.
		$post = $this->factory->post->create_and_get(
			array(
				'post_status'  => 'publish',
				'post_content' => '[cleverreach_extension]',
				'post_type'    => 'post'
			)
		);

		// Got to post and apply content filters.
		$this->go_to( get_the_permalink( $post->ID ) );
		$content = apply_filters( 'the_content', $post->post_content );

		// Assert that shortcode tag is not displayed in the content anymore.
		$this->assertTrue( have_posts() );
		$this->assertFalse( strpos( $content, '[cleverreach_extension]' ) );

	}

}