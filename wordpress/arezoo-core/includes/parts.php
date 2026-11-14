<?php
/**
 * Site parts: header, footer, single work, collection archive and 404 are Elementor
 * templates (Templates → Saved Templates) that this plugin shows in the right place,
 * because Elementor Free has no Theme Builder. Where an Elementor Pro Theme Builder
 * template covers the same location, the Pro template is used instead.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ay_parts() {
	return array(
		'header'  => 'هدر',
		'footer'  => 'فوتر',
		'single'  => 'صفحهٔ تک‌اثر',
		'archive' => 'آرشیو کالکشن و نوع',
		'404'     => 'صفحهٔ ۴۰۴',
	);
}

/** Template id assigned to a part (0 when none, missing or unpublished). */
function ay_part_id( $slot ) {
	$map = (array) get_option( 'arezoo_parts', array() );
	$id  = isset( $map[ $slot ] ) ? absint( $map[ $slot ] ) : 0;
	if ( $id && 'elementor_library' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
		return $id;
	}
	return 0;
}

function ay_set_part( $slot, $id ) {
	$map          = (array) get_option( 'arezoo_parts', array() );
	$map[ $slot ] = absint( $id );
	update_option( 'arezoo_parts', $map );
}

/** Does an Elementor Pro Theme Builder template already cover this location on this request? */
function ay_pro_covers( $location ) {
	if ( ! function_exists( 'elementor_theme_do_location' ) || ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
		return false;
	}
	try {
		$docs = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_documents_for_location( $location );
	} catch ( \Throwable $e ) {
		return false;
	}
	return ! empty( $docs );
}

/**
 * Print a part. Returns true when something was printed, so the theme can fall back
 * to its plain markup otherwise.
 */
function ay_render_part( $slot ) {
	static $busy = array();
	if ( ! ay_elementor_ready() ) {
		return false;
	}
	$pro = in_array( $slot, array( 'header', 'footer', 'archive' ), true ) ? $slot : 'single';
	if ( ay_pro_covers( $pro ) ) {
		return (bool) elementor_theme_do_location( $pro );
	}
	$id = ay_part_id( $slot );
	if ( ! $id || isset( $busy[ $id ] ) ) {
		return false;
	}
	$busy[ $id ] = true;
	$html        = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true );
	unset( $busy[ $id ] );
	if ( '' === trim( (string) $html ) ) {
		return false;
	}
	$tag = 'header' === $slot ? 'header' : ( 'footer' === $slot ? 'footer' : 'div' );
	echo '<' . $tag . ' class="ay-part ay-part-' . esc_attr( $slot ) . '">' . $html . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return true;
}

/** Load the CSS of the parts used on this request in the <head>, so nothing flashes unstyled. */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() || ! ay_elementor_ready() || ! class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
		return;
	}
	$slots = array( 'header', 'footer' );
	if ( is_singular( 'ay_work' ) ) {
		$slots[] = 'single';
	} elseif ( is_tax( array( 'ay_collection', 'ay_type' ) ) ) {
		$slots[] = 'archive';
	} elseif ( is_404() ) {
		$slots[] = '404';
	}
	foreach ( $slots as $slot ) {
		$id = ay_part_id( $slot );
		if ( $id ) {
			\Elementor\Core\Files\CSS\Post::create( $id )->enqueue();
		}
	}
}, 20 );

/** The term a collection / type archive page should open with (for the archive widget). */
function ay_archive_preselect() {
	$out = array( 'collection' => 0, 'type' => 0 );
	if ( is_tax( 'ay_collection' ) ) {
		$out['collection'] = (int) get_queried_object_id();
	} elseif ( is_tax( 'ay_type' ) ) {
		$out['type'] = (int) get_queried_object_id();
	}
	return $out;
}

/* A clear Persian label for the parts in the templates list. */
add_filter( 'display_post_states', function ( $states, $post ) {
	if ( 'elementor_library' === $post->post_type ) {
		foreach ( ay_parts() as $slot => $label ) {
			if ( ay_part_id( $slot ) === (int) $post->ID ) {
				$states[ 'ay_' . $slot ] = 'آرزو · ' . $label;
			}
		}
	}
	return $states;
}, 10, 2 );

/* Website language: always English, left to right, also when WordPress itself is Persian. */
add_filter( 'locale', function ( $locale ) {
	if ( ! ay_opt( 'site_en' ) || is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $locale;
	}
	if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['elementor_library'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'en_US';
	}
	return 'en_US';
}, 99 );
