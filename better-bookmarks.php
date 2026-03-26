<?php
/**
 * Plugin Name: Better Bookmarks
 * Plugin URI:  https://github.com/philhoyt/better-bookmarks
 * Description: A link card block that fetches Open Graph metadata and renders a rich preview card.
 * Version:     1.0.0
 * Author:      Phil Hoyt
 * License:     GPL-2.0-or-later
 * Text Domain: better-bookmarks
 *
 * @package BetterBookmarks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BETTER_BOOKMARKS_VERSION', '1.0.0' );
define( 'BETTER_BOOKMARKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'BETTER_BOOKMARKS_URL', plugin_dir_url( __FILE__ ) );

require_once BETTER_BOOKMARKS_PATH . 'includes/class-better-bookmarks.php';
require_once BETTER_BOOKMARKS_PATH . 'includes/class-rest.php';

( new Better_Bookmarks() )->init();
