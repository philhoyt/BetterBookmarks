<?php
/**
 * PHPUnit bootstrap — loads Brain\Monkey stubs so WP functions are available
 * without a full WordPress install.
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Define the ABSPATH guard used by every plugin file.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
}

// The REST class puts the version in its user agent.
if ( ! defined( 'BETTER_BOOKMARKS_VERSION' ) ) {
	define( 'BETTER_BOOKMARKS_VERSION', '0.0.0-test' );
}

require_once __DIR__ . '/stubs.php';
