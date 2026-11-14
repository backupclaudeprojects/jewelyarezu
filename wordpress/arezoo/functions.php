<?php
/**
 * Arezoo theme. The layout comes from Elementor and the Arezoo Core plugin; this theme
 * only provides the page frame, the menu location and plain fallbacks.
 *
 * @package Arezoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AREZOO_THEME_VERSION', '1.0.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo' );
	register_nav_menus( array( 'primary' => 'منوی اصلی' ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'arezoo-theme', get_template_directory_uri() . '/assets/theme.css', array(), AREZOO_THEME_VERSION );
}, 4 );

/** Print a site part made in Elementor (header, footer, single, archive, 404). False = use the plain fallback. */
function arezoo_part( $slot ) {
	return function_exists( 'ay_render_part' ) ? ay_render_part( $slot ) : false;
}

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	$missing = array();
	if ( ! did_action( 'elementor/loaded' ) ) {
		$missing[] = 'Elementor';
	}
	if ( ! defined( 'AY_VERSION' ) ) {
		$missing[] = 'Arezoo Core';
	}
	if ( $missing ) {
		echo '<div class="notice notice-warning" dir="rtl"><p><strong>قالب Arezoo:</strong> برای کار کردن قالب، این افزونه‌ها را نصب و فعال کنید: ' . esc_html( implode( ' و ', $missing ) ) . '.</p></div>';
	}
} );
