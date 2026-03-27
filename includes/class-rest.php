<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- short name intentional.
/**
 * REST API endpoint for fetching Open Graph preview data.
 *
 * @package BetterBookmarks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Better_Bookmarks_Rest
 */
class Better_Bookmarks_Rest {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'better-bookmarks/v1',
			'/preview',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_preview' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'url' => array(
						'required'          => true,
						'validate_callback' => function ( $url ) {
							return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
						},
						'sanitize_callback' => 'esc_url_raw',
					),
				),
			)
		);
	}

	/**
	 * Fetch Open Graph metadata for a URL.
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return WP_REST_Response
	 */
	public function get_preview( WP_REST_Request $request ): WP_REST_Response {
		$url = $request->get_param( 'url' );

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (compatible; BetterBookmarks/' . BETTER_BOOKMARKS_VERSION . '; +https://wordpress.org)',
				'sslverify'  => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response(
				array( 'error' => $response->get_error_message() ),
				400
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$data = array(
			'url'         => $url,
			'title'       => '',
			'description' => '',
			'image'       => '',
			'domain'      => $host ? preg_replace( '/^www\./', '', $host ) : '',
			'imageWidth'  => 0,
			'imageHeight' => 0,
		);

		// og:title → <title> fallback.
		if ( preg_match( '/<meta[^>]+property=["\']og:title["\'][^>]+content=["\'](.*?)["\']/i', $body, $m )
			|| preg_match( '/<meta[^>]+content=["\'](.*?)["\'][^>]+property=["\']og:title["\']/i', $body, $m ) ) {
			$data['title'] = html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		} elseif ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $body, $m ) ) {
			$data['title'] = html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}

		// og:description → <meta name="description"> fallback.
		if ( preg_match( '/<meta[^>]+property=["\']og:description["\'][^>]+content=["\'](.*?)["\']/i', $body, $m )
			|| preg_match( '/<meta[^>]+content=["\'](.*?)["\'][^>]+property=["\']og:description["\']/i', $body, $m ) ) {
			$data['description'] = html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		} elseif ( preg_match( '/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/i', $body, $m )
			|| preg_match( '/<meta[^>]+content=["\'](.*?)["\'][^>]+name=["\']description["\']/i', $body, $m ) ) {
			$data['description'] = html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}

		// og:image.
		if ( preg_match( '/<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](.*?)["\']/i', $body, $m )
			|| preg_match( '/<meta[^>]+content=["\'](.*?)["\'][^>]+property=["\']og:image["\']/i', $body, $m ) ) {
			$data['image'] = $m[1];
		}

		// og:image dimensions — try meta tags first, then probe the image.
		if ( $data['image'] ) {
			$img_w = 0;
			$img_h = 0;

			if ( preg_match( '/<meta[^>]+property=["\']og:image:width["\'][^>]+content=["\'](\d+)["\']/i', $body, $mw )
				|| preg_match( '/<meta[^>]+content=["\'](\d+)["\'][^>]+property=["\']og:image:width["\']/i', $body, $mw ) ) {
				$img_w = (int) $mw[1];
			}
			if ( preg_match( '/<meta[^>]+property=["\']og:image:height["\'][^>]+content=["\'](\d+)["\']/i', $body, $mh )
				|| preg_match( '/<meta[^>]+content=["\'](\d+)["\'][^>]+property=["\']og:image:height["\']/i', $body, $mh ) ) {
				$img_h = (int) $mh[1];
			}

			if ( ! $img_w || ! $img_h ) {
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- getimagesize() emits E_WARNING on network failure; return value is always checked.
				$size = @getimagesize( $data['image'] );
				if ( $size ) {
					$img_w = $size[0];
					$img_h = $size[1];
				}
			}

			$data['imageWidth']  = $img_w;
			$data['imageHeight'] = $img_h;
		}

		// Truncate description to a sensible length.
		if ( strlen( $data['description'] ) > 200 ) {
			$data['description'] = substr( $data['description'], 0, 197 ) . '…';
		}

		return new WP_REST_Response( $data, 200 );
	}
}
