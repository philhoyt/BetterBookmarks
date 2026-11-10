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

	/**
	 * Instance under test.
	 *
	 * Untyped on purpose: typed properties are PHP 7.4 syntax and the suite
	 * runs on the plugin's PHP 7.3 CI floor.
	 *
	 * @var Better_Bookmarks_Rest
	 */
	private $rest;

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

	// -------------------------------------------------------------------------
	// get_preview() — every user-supplied URL goes through wp_safe_remote_get().
	// -------------------------------------------------------------------------

	/**
	 * Stub the WP helpers get_preview() calls around the fetch.
	 *
	 * @param array $responses Response bodies keyed by fetched URL.
	 */
	private function stub_fetch( array $responses ): void {
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'wp_http_validate_url' )->returnArg( 1 );
		Functions\when( 'is_wp_error' )->alias(
			static function ( $thing ) {
				return $thing instanceof WP_Error;
			}
		);
		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'wp_strip_all_tags' )->alias( 'strip_tags' );
		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			static function ( $response ) {
				return $response['code'];
			}
		);
		Functions\when( 'wp_remote_retrieve_body' )->alias(
			static function ( $response ) {
				return $response['body'];
			}
		);
		Functions\expect( 'wp_remote_get' )->never();
		Functions\when( 'wp_safe_remote_get' )->alias(
			static function ( $url ) use ( $responses ) {
				return $responses[ $url ] ?? new WP_Error( 'http_request_failed' );
			}
		);
	}

	/** @test */
	public function it_fetches_the_page_with_the_redirect_checking_client(): void {
		$page = 'https://example.com/article';
		$html = '<meta property="og:title" content="Hello">'
			. '<meta property="og:image" content="https://example.com/a.png">'
			. '<meta property="og:image:width" content="1200">'
			. '<meta property="og:image:height" content="630">';
		$this->stub_fetch( array( $page => array( 'code' => 200, 'body' => $html ) ) );

		$result = $this->rest->get_preview( new WP_REST_Request( array( 'url' => $page ) ) );

		$this->assertInstanceOf( WP_REST_Response::class, $result );
		$this->assertSame( 'Hello', $result->get_data()['title'] );
		$this->assertSame( 1200, $result->get_data()['imageWidth'] );
	}

	/** @test */
	public function it_returns_an_error_when_the_safe_client_refuses_the_url(): void {
		// wp_safe_remote_get() returns a WP_Error when a redirect hop lands on a
		// private address; the stub models that by having no response for the URL.
		$this->stub_fetch( array() );

		$result = $this->rest->get_preview( new WP_REST_Request( array( 'url' => 'https://example.com/redirects-inward' ) ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'fetch_error', $result->get_error_code() );
	}

	/** @test */
	public function it_probes_og_image_dimensions_with_the_redirect_checking_client(): void {
		$page  = 'https://example.com/article';
		$image = 'http://93.184.216.34/a.png';
		$html  = '<meta property="og:title" content="Hello">'
			. '<meta property="og:image" content="' . $image . '">';
		// A 3x2 PNG: signature, then an IHDR chunk carrying width and height.
		$png = "\x89PNG\r\n\x1a\n" . pack( 'N', 13 ) . 'IHDR' . pack( 'NN', 3, 2 ) . "\x08\x02\x00\x00\x00" . pack( 'N', 0 );
		$this->stub_fetch(
			array(
				$page  => array( 'code' => 200, 'body' => $html ),
				$image => array( 'code' => 206, 'body' => $png ),
			)
		);

		$result = $this->rest->get_preview( new WP_REST_Request( array( 'url' => $page ) ) );

		$this->assertSame( 3, $result->get_data()['imageWidth'] );
		$this->assertSame( 2, $result->get_data()['imageHeight'] );
	}
}
