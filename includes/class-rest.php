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
	 * Maximum response body size to read (2 MB).
	 */
	const MAX_RESPONSE_SIZE = 2 * 1024 * 1024;

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
							return filter_var( $url, FILTER_VALIDATE_URL ) !== false
								&& $this->is_safe_url( $url );
						},
						'sanitize_callback' => 'esc_url_raw',
					),
				),
			)
		);
	}

	/**
	 * Validate that a URL is safe to fetch (public http/https only).
	 *
	 * Blocks non-http/https schemes, private IP ranges, loopback,
	 * link-local (including cloud metadata endpoints at 169.254.x.x),
	 * and other reserved ranges.
	 *
	 * @param string $url The URL to validate.
	 * @return bool
	 */
	private function is_safe_url( string $url ): bool {
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
			return false;
		}

		if ( ! wp_http_validate_url( $url ) ) {
			return false;
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host ) {
			return false;
		}

		$ip = gethostbyname( $host );

		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
			return false;
		}

		return true;
	}

	/**
	 * Extract an IMDb title ID from a URL, or return an empty string.
	 *
	 * @param string $url The URL to check.
	 * @return string IMDb ID (e.g. "tt5370118") or empty string.
	 */
	private function get_imdb_id( string $url ): string {
		if ( preg_match( '#imdb\.com/title/(tt\d+)#i', $url, $m ) ) {
			return $m[1];
		}
		return '';
	}

	/**
	 * Fetch metadata from TMDb for a given IMDb ID.
	 *
	 * Returns a response-ready data array on success, or null if the key is
	 * missing, the lookup fails, or the title cannot be found.
	 *
	 * @param string $imdb_id IMDb title ID (e.g. "tt5370118").
	 * @param string $url     Original IMDb URL, used as the canonical URL in the response.
	 * @return array|null
	 */
	private function fetch_from_tmdb( string $imdb_id, string $url ): ?array {
		$api_key = Better_Bookmarks_Settings::get_tmdb_api_key();
		if ( ! $api_key ) {
			return null;
		}

		// Step 1: resolve IMDb ID → TMDb ID + media type.
		$find_response = wp_remote_get(
			add_query_arg(
				array(
					'api_key'         => $api_key,
					'external_source' => 'imdb_id',
				),
				'https://api.themoviedb.org/3/find/' . rawurlencode( $imdb_id )
			),
			array(
				'timeout'   => 10,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $find_response ) || 200 !== wp_remote_retrieve_response_code( $find_response ) ) {
			return null;
		}

		$find_data  = json_decode( wp_remote_retrieve_body( $find_response ), true );
		$tmdb_id    = null;
		$media_type = null;

		if ( ! empty( $find_data['movie_results'] ) ) {
			$tmdb_id    = $find_data['movie_results'][0]['id'];
			$media_type = 'movie';
		} elseif ( ! empty( $find_data['tv_results'] ) ) {
			$tmdb_id    = $find_data['tv_results'][0]['id'];
			$media_type = 'tv';
		}

		if ( ! $tmdb_id ) {
			return null;
		}

		// Step 2: fetch full details.
		$detail_response = wp_remote_get(
			add_query_arg(
				array( 'api_key' => $api_key ),
				'https://api.themoviedb.org/3/' . $media_type . '/' . rawurlencode( (string) $tmdb_id )
			),
			array(
				'timeout'   => 10,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $detail_response ) || 200 !== wp_remote_retrieve_response_code( $detail_response ) ) {
			return null;
		}

		$detail = json_decode( wp_remote_retrieve_body( $detail_response ), true );

		$title       = 'movie' === $media_type ? ( $detail['title'] ?? '' ) : ( $detail['name'] ?? '' );
		$description = $detail['overview'] ?? '';
		if ( strlen( $description ) > 200 ) {
			$description = substr( $description, 0, 197 ) . '…';
		}

		$poster_path = $detail['poster_path'] ?? '';
		$image       = $poster_path ? 'https://image.tmdb.org/t/p/w500' . $poster_path : '';
		// TMDb w500 posters are always 500×750 (2:3 ratio).
		$image_width  = $image ? 500 : 0;
		$image_height = $image ? 750 : 0;

		return array(
			'url'         => $url,
			'title'       => $title,
			'description' => $description,
			'image'       => $image,
			'domain'      => 'imdb.com',
			'imageWidth'  => $image_width,
			'imageHeight' => $image_height,
		);
	}

	/**
	 * Fetch Open Graph metadata for a URL.
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_preview( WP_REST_Request $request ) {
		$url = $request->get_param( 'url' );

		// For IMDb URLs, use the TMDb API when a key is configured.
		$imdb_id = $this->get_imdb_id( $url );
		if ( $imdb_id ) {
			$tmdb_data = $this->fetch_from_tmdb( $imdb_id, $url );
			if ( $tmdb_data ) {
				return new WP_REST_Response( $tmdb_data, 200 );
			}
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'             => 10,
				'limit_response_size' => self::MAX_RESPONSE_SIZE,
				'user-agent'          => 'Mozilla/5.0 (compatible; BetterBookmarks/' . BETTER_BOOKMARKS_VERSION . '; +https://wordpress.org)',
				'sslverify'           => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'fetch_error',
				$response->get_error_message(),
				array( 'status' => 400 )
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code < 200 || $status_code >= 300 ) {
			return new WP_Error(
				'http_error',
				/* translators: %d: HTTP status code */
				sprintf( __( 'Remote server returned HTTP %d.', 'better-bookmarks' ), $status_code ),
				array( 'status' => 400 )
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
				// Only probe the image if it passes the same safety checks as the
				// page URL — prevents chained SSRF via a malicious og:image value.
				if ( $this->is_safe_url( $data['image'] ) ) {
					// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- getimagesize() emits E_WARNING on network failure; return value is always checked.
					$size = @getimagesize( $data['image'] );
					if ( $size ) {
						$img_w = $size[0];
						$img_h = $size[1];
					}
				}
			}

			$data['imageWidth']  = $img_w;
			$data['imageHeight'] = $img_h;
		}

		// Truncate description to a sensible length.
		if ( strlen( $data['description'] ) > 200 ) {
			$data['description'] = substr( $data['description'], 0, 197 ) . '…';
		}

		// If we couldn't extract a title the page likely blocked the request
		// (e.g. a bot-challenge page that returned HTTP 200).
		if ( '' === $data['title'] ) {
			return new WP_Error(
				'no_metadata',
				__( 'Could not retrieve metadata. The site may be blocking automated requests.', 'better-bookmarks' ),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response( $data, 200 );
	}
}
