<?php
/**
 * Plugin Name: Better Bookmarks
 * Plugin URI:  https://github.com/philhoyt/BetterBookmarks
 * Description: A link card block that fetches Open Graph metadata and renders a rich preview card.
 * Version:     1.2.1
 * Author:      philhoyt
 * Author URI:  https://philhoyt.com/
 * Requires at least: 6.5
 * Requires PHP:      7.2
 * Tested up to:      7.1
 * License:           GPL-2.0-or-later
 * Text Domain:       better-bookmarks
 *
 * @package BetterBookmarks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BETTER_BOOKMARKS_VERSION', '1.2.1' );
define( 'BETTER_BOOKMARKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'BETTER_BOOKMARKS_URL', plugin_dir_url( __FILE__ ) );

require_once BETTER_BOOKMARKS_PATH . 'includes/class-better-bookmarks-settings.php';
require_once BETTER_BOOKMARKS_PATH . 'includes/class-better-bookmarks.php';
require_once BETTER_BOOKMARKS_PATH . 'includes/class-rest.php';
require_once BETTER_BOOKMARKS_PATH . 'lib/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$better_bookmarks_update_checker = PucFactory::buildUpdateChecker(
	'https://github.com/philhoyt/BetterBookmarks/',
	__FILE__,
	'better-bookmarks'
);

$better_bookmarks_update_checker_api = $better_bookmarks_update_checker->getVcsApi();
if ( method_exists( $better_bookmarks_update_checker_api, 'enableReleaseAssets' ) ) {
	// Only a .zip asset counts, and a release without one is skipped. The
	// default PREFER_RELEASE_ASSETS falls back to GitHub's source archive,
	// which installs as <Repo>-<tag>/ and breaks the plugin directory.
	// Without the filter the first asset wins, GitHub lists assets by name,
	// so an asset named ahead of better-bookmarks.zip would be installed (PCLZIP_ERR_BAD_FORMAT).
	$better_bookmarks_update_checker_api->enableReleaseAssets( '/\.zip$/', $better_bookmarks_update_checker_api::REQUIRE_RELEASE_ASSETS );
}

( new Better_Bookmarks_Settings() )->init();
( new Better_Bookmarks() )->init();
