<?php
/**
 * Plugin settings page.
 *
 * @package BetterBookmarks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Better_Bookmarks_Settings
 */
class Better_Bookmarks_Settings {

	/**
	 * WordPress option key for stored settings.
	 */
	const OPTION_KEY = 'better_bookmarks_settings';

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Get the TMDb API key.
	 *
	 * Checks for a wp-config.php constant first, then falls back to the
	 * database option. Define BETTER_BOOKMARKS_TMDB_API_KEY in wp-config.php
	 * to prevent the key from being stored in the database.
	 *
	 * @return string
	 */
	public static function get_tmdb_api_key(): string {
		if ( defined( 'BETTER_BOOKMARKS_TMDB_API_KEY' ) ) {
			return (string) BETTER_BOOKMARKS_TMDB_API_KEY;
		}
		$options = get_option( self::OPTION_KEY, array() );
		return isset( $options['tmdb_api_key'] ) ? (string) $options['tmdb_api_key'] : '';
	}

	/**
	 * Add the settings page under Settings menu.
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'Better Bookmarks Settings', 'better-bookmarks' ),
			__( 'Better Bookmarks', 'better-bookmarks' ),
			'manage_options',
			'better-bookmarks',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings, sections, and fields.
	 */
	public function register_settings(): void {
		register_setting(
			'better_bookmarks_settings_group',
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		add_settings_section(
			'better_bookmarks_api_section',
			__( 'API Integrations', 'better-bookmarks' ),
			'__return_false',
			'better-bookmarks'
		);

		add_settings_field(
			'tmdb_api_key',
			__( 'TMDb API Key', 'better-bookmarks' ),
			array( $this, 'render_tmdb_api_key_field' ),
			'better-bookmarks',
			'better_bookmarks_api_section'
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param mixed $input Raw input from the form.
	 * @return array
	 */
	public function sanitize_settings( $input ): array {
		$sanitized = array();
		if ( isset( $input['tmdb_api_key'] ) ) {
			$sanitized['tmdb_api_key'] = sanitize_text_field( $input['tmdb_api_key'] );
		}
		return $sanitized;
	}

	/**
	 * Render the TMDb API key field.
	 */
	public function render_tmdb_api_key_field(): void {
		if ( defined( 'BETTER_BOOKMARKS_TMDB_API_KEY' ) ) {
			printf(
				'<input type="text" class="regular-text" value="%s" disabled />',
				esc_attr( str_repeat( '•', 20 ) )
			);
			echo '<p class="description">' . esc_html__( 'Defined in wp-config.php via BETTER_BOOKMARKS_TMDB_API_KEY — cannot be edited here.', 'better-bookmarks' ) . '</p>';
			return;
		}

		$options = get_option( self::OPTION_KEY, array() );
		$value   = isset( $options['tmdb_api_key'] ) ? $options['tmdb_api_key'] : '';

		printf(
			'<input type="text" id="tmdb_api_key" name="%s" value="%s" class="regular-text" autocomplete="off" />',
			esc_attr( self::OPTION_KEY . '[tmdb_api_key]' ),
			esc_attr( $value )
		);

		echo '<p class="description">';
		echo wp_kses(
			sprintf(
				/* translators: %s: TMDb API settings URL */
				__( 'Enter your <a href="%s" target="_blank" rel="noopener noreferrer">TMDb API key</a>. When set, IMDb links will use TMDb for richer metadata instead of scraping the page.', 'better-bookmarks' ),
				'https://www.themoviedb.org/settings/api'
			),
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
					'rel'    => array(),
				),
			)
		);
		echo '</p>';
		echo '<p class="description">' . esc_html__( 'You can also define this in wp-config.php: define( \'BETTER_BOOKMARKS_TMDB_API_KEY\', \'your-key-here\' );', 'better-bookmarks' ) . '</p>';
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'better_bookmarks_settings_group' );
				do_settings_sections( 'better-bookmarks' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
