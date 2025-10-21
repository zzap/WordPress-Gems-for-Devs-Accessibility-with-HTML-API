<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Wpgems
 */

// Load Composer autoloader if present.
$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Let WP test suite know where the Polyfills live.
if ( ! defined('WP_TESTS_PHPUNIT_POLYFILLS_PATH') ) {
    $polyfills = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH')
        ?: dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills';
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $polyfills);
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/wpgems.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
