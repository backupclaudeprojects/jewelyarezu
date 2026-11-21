<?php
/**
 * Tiny stand-in for wp-cli: run PHP inside the local WordPress as the admin user.
 *   php wp.php -r 'echo AY_VERSION;'          inline code
 *   php wp.php path/to/script.php             a file
 *   add --install for the very first call (before tables exist)
 * Globals are prefixed __ on purpose: WordPress owns $wp, $args etc.
 * Env: WP_DIR (default /tmp/arezoo-wp), PORT (default 8890).
 */
$__wpdir = getenv( 'WP_DIR' ) ?: '/tmp/arezoo-wp';
$__port = getenv( 'PORT' ) ?: '8890';
$__args = array_slice( $argv, 1 );
$__inst = in_array( '--install', $__args, true );
$__args = array_values( array_diff( $__args, array( '--install' ) ) );
$_SERVER['HTTP_HOST']      = '127.0.0.1:' . $__port;
$_SERVER['SERVER_NAME']    = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI']    = $__inst ? '/' : '/wp-admin/';
if ( $__inst ) {
	define( 'WP_INSTALLING', true );
} else {
	define( 'WP_ADMIN', true );
}
require $__wpdir . '/wp-load.php';
if ( ! $__inst ) {
	require_once ABSPATH . 'wp-admin/includes/admin.php';
	wp_set_current_user( 1 );
}
if ( isset( $__args[0] ) && '-r' === $__args[0] ) {
	eval( $__args[1] ); // phpcs:ignore
} elseif ( isset( $__args[0] ) ) {
	require $__args[0];
}
