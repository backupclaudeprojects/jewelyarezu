<?php
/**
 * Small helpers that compose real Elementor structures (containers + widgets) in PHP.
 * Demos and the site setup use them, so everything they create opens in Elementor as
 * ordinary, selectable, editable elements.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ay_uid() {
	return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
}

/** Reference to one of the Arezoo global colours (follows the colour theme until overridden by hand). */
function ay_g( $role ) {
	return 'globals/colors?id=ay' . $role;
}

function ay_px( $size, $unit = 'px' ) {
	return array( 'unit' => $unit, 'size' => $size, 'sizes' => array() );
}

function ay_box( $top, $right = null, $bottom = null, $left = null, $unit = 'px' ) {
	$right  = null === $right ? $top : $right;
	$bottom = null === $bottom ? $top : $bottom;
	$left   = null === $left ? $right : $left;
	return array( 'unit' => $unit, 'top' => (string) $top, 'right' => (string) $right, 'bottom' => (string) $bottom, 'left' => (string) $left, 'isLinked' => $top === $right && $right === $bottom && $bottom === $left );
}

function ay_gap( $size ) {
	return array( 'unit' => 'px', 'size' => $size, 'column' => (string) $size, 'row' => (string) $size, 'isLinked' => true );
}

/** Container. */
function ay_c( $settings = array(), $children = array(), $inner = false ) {
	$settings = wp_parse_args( $settings, array( 'content_width' => 'boxed', 'flex_direction' => 'column' ) );
	return array( 'id' => ay_uid(), 'elType' => 'container', 'settings' => $settings, 'elements' => array_values( array_filter( $children ) ), 'isInner' => (bool) $inner );
}

/** Widget. */
function ay_w( $type, $settings = array() ) {
	return array( 'id' => ay_uid(), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => array() );
}

/** Full-width band whose inner content is boxed to the site width (1180px). */
function ay_band( $children, $extra = array() ) {
	return ay_c( wp_parse_args( $extra, array(
		'content_width' => 'boxed',
		'boxed_width'   => ay_px( 1180 ),
		'flex_gap'      => ay_gap( 24 ),
		'padding'       => ay_box( 90, 24, 90, 24 ),
		'padding_mobile' => ay_box( 56, 16, 56, 16 ),
	) ), $children );
}

function ay_col( $children, $extra = array() ) {
	return ay_c( wp_parse_args( $extra, array( 'content_width' => 'full', 'flex_gap' => ay_gap( 16 ), 'padding' => ay_box( 0 ) ) ), $children, true );
}

function ay_row( $children, $extra = array() ) {
	return ay_c( wp_parse_args( $extra, array( 'content_width' => 'full', 'flex_direction' => 'row', 'flex_direction_mobile' => 'column', 'flex_gap' => ay_gap( 48 ), 'padding' => ay_box( 0 ) ) ), $children, true );
}

/* ----- ordinary Elementor widgets, pre-styled with the Arezoo type and global colours ----- */
function ay_eyebrow( $text, $extra = array() ) {
	return ay_w( 'heading', wp_parse_args( $extra, array(
		'title'                      => $text,
		'header_size'                => 'p',
		'typography_typography'      => 'custom',
		'typography_font_family'     => 'Jost',
		'typography_font_size'       => ay_px( 12 ),
		'typography_font_weight'     => '400',
		'typography_text_transform'  => 'uppercase',
		'typography_letter_spacing'  => ay_px( 3.2 ),
		'__globals__'                => array( 'title_color' => ay_g( 'muted' ) ),
	) ) );
}

function ay_heading( $text, $tag = 'h2', $size = 50, $extra = array() ) {
	return ay_w( 'heading', wp_parse_args( $extra, array(
		'title'                         => $text,
		'header_size'                   => $tag,
		'typography_typography'         => 'custom',
		'typography_font_family'        => 'Cormorant Garamond',
		'typography_font_size'          => ay_px( $size ),
		'typography_font_size_tablet'   => ay_px( round( $size * 0.82 ) ),
		'typography_font_size_mobile'   => ay_px( max( 28, round( $size * 0.64 ) ) ),
		'typography_font_weight'        => '500',
		'typography_line_height'        => ay_px( 1.08, 'em' ),
		'__globals__'                   => array( 'title_color' => ay_g( 'ink' ) ),
	) ) );
}

function ay_text( $html, $extra = array() ) {
	return ay_w( 'text-editor', wp_parse_args( $extra, array(
		'editor'                    => $html,
		'typography_typography'     => 'custom',
		'typography_font_family'    => 'Jost',
		'typography_font_size'      => ay_px( 17 ),
		'typography_font_weight'    => '300',
		'typography_line_height'    => ay_px( 1.7, 'em' ),
		'__globals__'               => array( 'text_color' => ay_g( 'muted' ) ),
	) ) );
}

/** Elementor's own Button widget; $style is "solid" or "line". */
function ay_button( $text, $url, $style = 'solid', $extra = array() ) {
	$s = array(
		'text'                       => $text,
		'link'                       => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
		'typography_typography'      => 'custom',
		'typography_font_family'     => 'Jost',
		'typography_font_size'       => ay_px( 15 ),
		'typography_font_weight'     => '400',
		'typography_letter_spacing'  => ay_px( 0.6 ),
		'border_radius'              => ay_box( 999 ),
		'text_padding'               => ay_box( 15, 28, 15, 28 ),
		'border_border'              => 'solid',
		'border_width'               => ay_box( 1 ),
		'background_background'      => 'classic',
		'selected_icon'              => array( 'value' => 'fas fa-arrow-right', 'library' => 'fa-solid' ),
		'icon_align'                 => 'row-reverse',
		'icon_indent'                => ay_px( 12 ),
	);
	if ( 'line' === $style ) {
		$s['background_color'] = 'rgba(0,0,0,0)';
		$s['__globals__']      = array( 'button_text_color' => ay_g( 'ink' ), 'border_color' => ay_g( 'ink' ), 'hover_color' => ay_g( 'btnink' ), 'button_background_hover_color' => ay_g( 'btn' ), 'button_hover_border_color' => ay_g( 'btn' ) );
	} else {
		$s['__globals__'] = array( 'button_text_color' => ay_g( 'btnink' ), 'background_color' => ay_g( 'btn' ), 'border_color' => ay_g( 'btnline' ), 'hover_color' => ay_g( 'btnink' ), 'button_background_hover_color' => ay_g( 'btn' ) );
	}
	return ay_w( 'button', array_merge( $s, $extra ) );
}

/**
 * Write an Elementor layout to a page or saved template. The content is stored the way
 * Elementor stores it, so it opens in the editor as normal editable elements.
 */
function ay_save_layout( $post_id, array $elements, $type = 'wp-page' ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return false;
	}
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', $type );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.24.0' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( array_values( $elements ) ) ) );
	if ( 'wp-page' === $type ) {
		update_post_meta( $post_id, '_wp_page_template', 'elementor_header_footer' );
	}
	ay_flush_layout_cache( $post_id );
	return true;
}

/** Drop Elementor's generated CSS and element cache for one post so the next view rebuilds them. */
function ay_flush_layout_cache( $post_id ) {
	delete_post_meta( $post_id, '_elementor_css' );
	delete_post_meta( $post_id, '_elementor_element_cache' );
	delete_post_meta( $post_id, '_elementor_page_assets' );
	if ( ay_elementor_ready() && class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
		$css = \Elementor\Core\Files\CSS\Post::create( $post_id );
		$css->delete();
	}
}

function ay_get_layout( $post_id ) {
	$raw = get_post_meta( $post_id, '_elementor_data', true );
	if ( is_string( $raw ) && '' !== $raw ) {
		$raw = json_decode( $raw, true );
	}
	return is_array( $raw ) ? $raw : array();
}

/** Find the index of the top-level element that carries a CSS ID (used to replace one section of a page). */
function ay_find_section( array $elements, $css_id ) {
	foreach ( $elements as $i => $el ) {
		if ( isset( $el['settings']['_element_id'] ) && $css_id === $el['settings']['_element_id'] ) {
			return $i;
		}
	}
	return -1;
}

/** Keep a copy of the current layout (up to five per post) before it is replaced. */
function ay_backup_layout( $post_id, $note = '' ) {
	$data = get_post_meta( $post_id, '_elementor_data', true );
	if ( ! $data ) {
		return false;
	}
	$list   = (array) get_post_meta( $post_id, '_ay_backups', true );
	$list[] = array( 'time' => time(), 'note' => $note, 'data' => is_string( $data ) ? $data : wp_json_encode( $data ) );
	$list   = array_slice( array_values( array_filter( $list ) ), -5 );
	update_post_meta( $post_id, '_ay_backups', wp_slash( $list ) );
	return true;
}

/** Put the most recent backup back. Returns false when there is none. */
function ay_restore_layout( $post_id ) {
	$list = array_values( array_filter( (array) get_post_meta( $post_id, '_ay_backups', true ) ) );
	if ( ! $list ) {
		return false;
	}
	$last = array_pop( $list );
	update_post_meta( $post_id, '_elementor_data', wp_slash( $last['data'] ) );
	update_post_meta( $post_id, '_ay_backups', wp_slash( $list ) );
	ay_flush_layout_cache( $post_id );
	return true;
}

function ay_backup_count( $post_id ) {
	return count( array_filter( (array) get_post_meta( $post_id, '_ay_backups', true ) ) );
}
