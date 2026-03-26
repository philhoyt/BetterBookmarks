<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- short name intentional.
/**
 * Core plugin class.
 *
 * @package BetterBookmarks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Better_Bookmarks
 */
class Better_Bookmarks {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 1 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		( new Better_Bookmarks_Rest() )->init();
	}

	/**
	 * Register the link-card block.
	 */
	public function register_block(): void {
		register_block_type( BETTER_BOOKMARKS_PATH . 'build/blocks/link-card/' );
	}

	/**
	 * Register the Better Bookmarks block category.
	 *
	 * @param array $categories Existing block categories.
	 * @return array
	 */
	public function register_block_category( array $categories ): array {
		foreach ( $categories as $category ) {
			if ( 'better-bookmarks' === $category['slug'] ) {
				return $categories;
			}
		}

		array_unshift(
			$categories,
			array(
				'slug'  => 'better-bookmarks',
				'title' => __( 'Better Bookmarks', 'better-bookmarks' ),
			)
		);

		return $categories;
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'better-bookmarks', false, dirname( plugin_basename( BETTER_BOOKMARKS_PATH ) ) . '/languages' );
	}
}
