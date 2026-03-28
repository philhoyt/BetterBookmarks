<?php
/**
 * Plugin Name: Better Bookmarks
 * Plugin URI:  https://github.com/philhoyt/BetterBookmarks
 * Description: A link card block that fetches Open Graph metadata and renders a rich preview card.
 * Version:     1.0.5
 * Author:      philhoyt
 * Author URI:  https://philhoyt.com/
 * Requires at least: 6.5
 * Requires PHP:      7.2
 * Tested up to:      7.0
 * License:           GPL-2.0-or-later
 * Text Domain:       better-bookmarks
 *
 * @package BetterBookmarks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BETTER_BOOKMARKS_VERSION', '1.0.5' );
define( 'BETTER_BOOKMARKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'BETTER_BOOKMARKS_URL', plugin_dir_url( __FILE__ ) );

require_once BETTER_BOOKMARKS_PATH . 'includes/class-better-bookmarks.php';
require_once BETTER_BOOKMARKS_PATH . 'includes/class-rest.php';
require_once BETTER_BOOKMARKS_PATH . 'lib/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$better_bookmarks_update_checker = PucFactory::buildUpdateChecker(
	'https://github.com/philhoyt/BetterBookmarks/',
	__FILE__,
	'better-bookmarks'
);
$better_bookmarks_update_checker->getVcsApi()->enableReleaseAssets();

( new Better_Bookmarks() )->init();
