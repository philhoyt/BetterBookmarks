<?php
/**
 * Unit tests for Better_Bookmarks_Rest.
 *
 * Covers pure-logic methods that don't require a live WordPress install.
 * WP functions used by is_safe_url() are mocked via Brain\Monkey.
 *
 * @package BetterBookmarks
 */

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class RestTest extends TestCase {

	private Better_Bookmarks_Rest $rest;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Load the class under test after Brain\Monkey is active.
		if ( ! class_exists( 'Better_Bookmarks_Rest' ) ) {
			require_once dirname( __DIR__, 2 ) . '/includes/class-rest.php';
		}

		$this->rest = new Better_Bookmarks_Rest();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// get_imdb_id() — pure regex, no WP dependencies.
	// -------------------------------------------------------------------------

	/** @test */
	public function it_extracts_an_imdb_id_from_a_title_url(): void {
		$method = new ReflectionMethod( Better_Bookmarks_Rest::class, 'get_imdb_id' );
		$method->setAccessible( true );

		$this->assertSame(
			'tt5370118',
			$method->invoke( $this->rest, 'https://www.imdb.com/title/tt5370118/' )
		);
	}

	/** @test */
	public function it_returns_empty_string_for_non_imdb_urls(): void {
		$method = new ReflectionMethod( Better_Bookmarks_Rest::class, 'get_imdb_id' );
		$method->setAccessible( true );

		$this->assertSame( '', $method->invoke( $this->rest, 'https://example.com' ) );
		$this->assertSame( '', $method->invoke( $this->rest, 'https://notimdb.com/title/tt1234567' ) );
	}

	/** @test */
	public function it_returns_empty_string_for_non_title_imdb_paths(): void {
		$method = new ReflectionMethod( Better_Bookmarks_Rest::class, 'get_imdb_id' );
		$method->setAccessible( true );

		$this->assertSame( '', $method->invoke( $this->rest, 'https://www.imdb.com/name/nm0000093/' ) );
	}

	// -------------------------------------------------------------------------
	// is_safe_url() — mocked WP functions via Brain\Monkey.
	// -------------------------------------------------------------------------

	private function stub_safe_url( string $url, string $ip ): void {
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'wp_http_validate_url' )->justReturn( $url );
	}

	/** @test */
	public function it_rejects_non_http_schemes(): void {
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'wp_http_validate_url' )->justReturn( false );

		$method = new ReflectionMethod( Better_Bookmarks_Rest::class, 'is_safe_url' );
		$method->setAccessible( true );

		$this->assertFalse( $method->invoke( $this->rest, 'ftp://example.com/file.jpg' ) );
		$this->assertFalse( $method->invoke( $this->rest, 'javascript:alert(1)' ) );
		$this->assertFalse( $method->invoke( $this->rest, 'file:///etc/passwd' ) );
	}

	/** @test */
	public function it_rejects_urls_that_fail_wp_http_validate_url(): void {
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'wp_http_validate_url' )->justReturn( false );

		$method = new ReflectionMethod( Better_Bookmarks_Rest::class, 'is_safe_url' );
		$method->setAccessible( true );

		$this->assertFalse( $method->invoke( $this->rest, 'http://192.168.1.1/image.jpg' ) );
	}

	/** @test */
	public function it_rejects_private_ip_ranges(): void {
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		// wp_http_validate_url passes — we want to test the IP filter step.
		Functions\when( 'wp_http_validate_url' )->returnArg( 1 );

		$method = new ReflectionMethod( Better_Bookmarks_Rest::class, 'is_safe_url' );
		$method->setAccessible( true );

		// gethostbyname returns the host unchanged for IP literals.
		// 10.x.x.x is a private range — filter_var should reject it.
		$this->assertFalse( $method->invoke( $this->rest, 'http://10.0.0.1/img.jpg' ) );
		$this->assertFalse( $method->invoke( $this->rest, 'http://169.254.169.254/latest/meta-data/' ) );
	}
}
