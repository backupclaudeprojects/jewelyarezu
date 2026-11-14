<?php
/**
 * Elementor integration: widget category, widgets, shared controls, fonts and assets.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once AY_DIR . 'includes/elementor/motions.php';

/**
 * Load the widget classes. Elementor only makes its Widget_Base class available once it
 * has initialised, so this runs from Elementor's own hooks (or after «elementor/init»).
 */
function ay_load_widgets() {
	if ( ! class_exists( '\Elementor\Widget_Base', false ) ) {
		return false;
	}
	require_once AY_DIR . 'includes/elementor/base.php';
	foreach ( array( 'hero', 'portfolio', 'work', 'site', 'content', 'studio' ) as $file ) {
		require_once AY_DIR . 'includes/elementor/widgets/' . $file . '.php';
	}
	return true;
}
require_once AY_DIR . 'includes/elementor/extensions.php';

add_action( 'elementor/elements/categories_registered', function ( $manager ) {
	$manager->add_category( 'arezoo', array( 'title' => 'آرزو', 'icon' => 'eicon-star' ) );
} );

add_action( 'elementor/widgets/register', function ( $manager ) {
	if ( ! ay_load_widgets() ) {
		return;
	}
	foreach ( array( 'Hero', 'Hscroll', 'Stack', 'Archive', 'Related', 'Work_Gallery', 'Work_Field', 'Work_Specs', 'Work_Nav', 'Logo', 'Nav', 'Dock', 'Contact', 'Sketch', 'Faq', 'Timeline', 'Motion', 'Process', 'Compare', 'Strip' ) as $class ) {
		$fqcn = '\\Arezoo\\Widgets\\' . $class;
		$manager->register( new $fqcn() );
	}
} );

/* ------------------------------------------------------------------ assets */
function ay_register_assets() {
	wp_register_style( 'arezoo', ay_asset( 'css/arezoo.css' ), array(), AY_VERSION );
	wp_register_script( 'arezoo-hero', ay_asset( 'js/hero-scene.js' ), array(), AY_VERSION, true );
	wp_register_script( 'arezoo', ay_asset( 'js/arezoo.js' ), array( 'arezoo-hero' ), AY_VERSION, true );
	wp_localize_script( 'arezoo', 'AREZOO', array(
		'wallpaper' => ay_wallpaper_config(),
		'editor'    => (bool) ( ay_elementor_ready() && \Elementor\Plugin::$instance->preview->is_preview_mode() ),
	) );
}
add_action( 'wp_enqueue_scripts', function () {
	ay_register_assets();
	wp_enqueue_style( 'arezoo' );
	wp_enqueue_script( 'arezoo' );
}, 5 );
add_action( 'elementor/editor/after_enqueue_styles', function () {
	wp_enqueue_style( 'arezoo-editor', ay_asset( 'css/editor.css' ), array(), AY_VERSION );
} );

/* The two site typefaces ship with the plugin, so no request goes to a font service. */
add_filter( 'elementor/fonts/groups', function ( $groups ) {
	return array_merge( array( 'arezoo' => 'آرزو (فونت‌های قالب)' ), $groups );
} );
add_filter( 'elementor/fonts/additional_fonts', function ( $fonts ) {
	$fonts['Cormorant Garamond'] = 'arezoo';
	$fonts['Jost']               = 'arezoo';
	return $fonts;
} );

/* --------------------------------------------- Elementor defaults for a new build */
/** Fonts, layout width and breakpoints-independent defaults written into the active kit once. */
function ay_apply_kit_defaults() {
	if ( ! ay_elementor_ready() ) {
		return;
	}
	$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
	if ( ! $kit || ! $kit->get_id() ) {
		return;
	}
	$s = get_post_meta( $kit->get_id(), '_elementor_page_settings', true );
	$s = is_array( $s ) ? $s : array();
	$font = function ( $id, $title, $family, $weight, $extra = array() ) {
		return array_merge( array( '_id' => $id, 'title' => $title, 'typography_typography' => 'custom', 'typography_font_family' => $family, 'typography_font_weight' => $weight ), $extra );
	};
	$s['system_typography'] = array(
		$font( 'primary', 'Primary', 'Cormorant Garamond', '500' ),
		$font( 'secondary', 'Secondary', 'Cormorant Garamond', '400', array( 'typography_font_style' => 'italic' ) ),
		$font( 'text', 'Text', 'Jost', '300' ),
		$font( 'accent', 'Accent', 'Jost', '500' ),
	);
	$s['container_width']        = array( 'unit' => 'px', 'size' => 1180, 'sizes' => array() );
	$s['container_padding']      = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true );
	$s['space_between_widgets']  = array( 'unit' => 'px', 'size' => 20, 'column' => '20', 'row' => '20', 'isLinked' => true );
	$s['page_title_selector']    = 'h1.entry-title';
	$s['body_typography_typography']  = 'custom';
	$s['body_typography_font_family'] = 'Jost';
	$s['body_typography_font_weight'] = '300';
	$s['body_typography_font_size']   = array( 'unit' => 'px', 'size' => 17, 'sizes' => array() );
	$s['body_typography_line_height'] = array( 'unit' => 'em', 'size' => 1.7, 'sizes' => array() );
	update_post_meta( $kit->get_id(), '_elementor_page_settings', wp_slash( $s ) );
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
