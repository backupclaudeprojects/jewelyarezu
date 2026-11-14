<?php
/**
 * Shared helpers: settings, colour maths, icons, contact details and work data.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ay_default_settings() {
	return array(
		'brand'      => 'Arezoo Yousefi',
		'email'      => 'hello@arezooyousefi.com',
		'instagram'  => 'arezooyousefi',
		'telegram'   => 'arezooyousefi',
		'site_en'    => 1,
		'admin_wash' => '#99E1D9',
	);
}

/** One setting from the «آرزو ← تنظیمات» page. */
function ay_opt( $key, $default = '' ) {
	$all = wp_parse_args( (array) get_option( 'arezoo_settings', array() ), ay_default_settings() );
	return isset( $all[ $key ] ) ? $all[ $key ] : $default;
}

function ay_asset( $rel ) {
	return AY_URL . 'assets/' . ltrim( $rel, '/' );
}

/* ------------------------------------------------------------------ colour maths */
function ay_hex( $c, $fallback = '#000000' ) {
	$c = sanitize_hex_color( $c );
	if ( ! $c ) {
		return $fallback;
	}
	if ( 4 === strlen( $c ) ) {
		$c = '#' . $c[1] . $c[1] . $c[2] . $c[2] . $c[3] . $c[3];
	}
	return strtoupper( $c );
}

function ay_rgb( $hex ) {
	$hex = ay_hex( $hex );
	return array( hexdec( substr( $hex, 1, 2 ) ), hexdec( substr( $hex, 3, 2 ) ), hexdec( substr( $hex, 5, 2 ) ) );
}

/** Mix colour $a towards $b by $t (0..1). */
function ay_mix( $a, $b, $t ) {
	$x = ay_rgb( $a );
	$y = ay_rgb( $b );
	$o = '#';
	for ( $i = 0; $i < 3; $i++ ) {
		$o .= sprintf( '%02X', max( 0, min( 255, (int) round( $x[ $i ] + ( $y[ $i ] - $x[ $i ] ) * $t ) ) ) );
	}
	return $o;
}

function ay_lum( $hex ) {
	$c = ay_rgb( $hex );
	foreach ( $c as $i => $v ) {
		$v      /= 255;
		$c[ $i ] = $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
	}
	return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
}

function ay_contrast( $a, $b ) {
	$x = ay_lum( $a );
	$y = ay_lum( $b );
	return ( max( $x, $y ) + 0.05 ) / ( min( $x, $y ) + 0.05 );
}

/* ----------------------------------------------------------------------- icons */
function ay_icons() {
	return array(
		'home'      => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9.5V20h13V9.5"/>',
		'gem'       => '<path d="M6 4h12l4 5-10 11L2 9z"/><path d="M2 9h20M9 4 7.5 9 12 20l4.5-11L15 4"/>',
		'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1-4.5 4-6.5 8-6.5s7 2 8 6.5"/>',
		'mail'      => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="m3.5 7 8.5 6.5L20.5 7"/>',
		'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r=".6" fill="currentColor"/>',
		'telegram'  => '<path d="M21.5 3.5 2.5 11l6 2.2 2.3 6.3 3.2-4 4.6 3.4z"/><path d="m8.5 13.2 8.5-6-5.2 7"/>',
		'up'        => '<path d="M12 20V5M5.5 11 12 4.5 18.5 11"/>',
		'search'    => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/>',
		'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
		'arrow'     => '<path d="M4 12h15M13 6l6 6-6 6"/>',
		'ring'      => '<circle cx="12" cy="15" r="6"/><path d="M9 6.5 12 3l3 3.5-3 3z"/>',
		'layers'    => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
		'calendar'  => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
		'star'      => '<path d="m12 3 2.7 5.6 6.1.8-4.5 4.2 1.1 6-5.4-3-5.4 3 1.1-6L3.2 9.4l6.1-.8z"/>',
		'image'     => '<rect x="3" y="4.5" width="18" height="15" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="m4 18 5.5-5 4 3.5 3-2.5 3.5 3"/>',
		'list'      => '<path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"/>',
		'tool'      => '<path d="m14.5 6.5 3-3 3 3-3 3zM4 20l9-9M11 13l-2-2"/>',
	);
}

/** Inline line icon. */
function ay_icon( $name, $size = 20 ) {
	$icons = ay_icons();
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	return '<svg class="ay-ic" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
}

/* --------------------------------------------------------------------- contact */
/** Email / Instagram / Telegram from the settings page (no phone number by design). */
function ay_contact() {
	$ig  = ltrim( trim( (string) ay_opt( 'instagram' ) ), '@' );
	$tg  = ltrim( trim( (string) ay_opt( 'telegram' ) ), '@' );
	$out = array();
	$em  = sanitize_email( (string) ay_opt( 'email' ) );
	if ( $em ) {
		$out['email'] = array( 'label' => 'Email', 'value' => $em, 'url' => 'mailto:' . $em, 'icon' => 'mail' );
	}
	if ( '' !== $ig ) {
		$url              = preg_match( '#^https?://#i', $ig ) ? $ig : 'https://www.instagram.com/' . rawurlencode( $ig ) . '/';
		$out['instagram'] = array( 'label' => 'Instagram', 'value' => preg_match( '#^https?://#i', $ig ) ? $ig : '@' . $ig, 'url' => $url, 'icon' => 'instagram' );
	}
	if ( '' !== $tg ) {
		$url             = preg_match( '#^https?://#i', $tg ) ? $tg : 'https://t.me/' . rawurlencode( $tg );
		$out['telegram'] = array( 'label' => 'Telegram', 'value' => preg_match( '#^https?://#i', $tg ) ? $tg : '@' . $tg, 'url' => $url, 'icon' => 'telegram' );
	}
	return $out;
}

/* ----------------------------------------------------------------------- works */
/** Terms of a taxonomy in the order set on the «دسته‌بندی‌ها» page. */
function ay_terms_ordered( $tax, $hide_empty = false ) {
	$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => $hide_empty ) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}
	usort( $terms, function ( $a, $b ) {
		$x = (int) get_term_meta( $a->term_id, '_ay_order', true );
		$y = (int) get_term_meta( $b->term_id, '_ay_order', true );
		return $x === $y ? strcasecmp( $a->name, $b->name ) : $x - $y;
	} );
	return $terms;
}

/** Attachment ids of a work's gallery (main image first). */
function ay_work_gallery_ids( $id ) {
	$ids   = array();
	$thumb = (int) get_post_thumbnail_id( $id );
	if ( $thumb ) {
		$ids[] = $thumb;
	}
	foreach ( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $id, '_ay_gallery', true ) ) ) ) as $g ) {
		if ( ! in_array( $g, $ids, true ) ) {
			$ids[] = $g;
		}
	}
	return $ids;
}

/** Extra detail rows ( [ [title, value], … ] ). */
function ay_work_extra( $id ) {
	$raw = json_decode( (string) get_post_meta( $id, '_ay_specs', true ), true );
	$out = array();
	foreach ( (array) $raw as $row ) {
		if ( is_array( $row ) && isset( $row[0], $row[1] ) && '' !== trim( (string) $row[0] ) ) {
			$out[] = array( (string) $row[0], (string) $row[1] );
		}
	}
	return $out;
}

function ay_work_fields() {
	return array(
		'materials' => array( 'label' => 'متریال', 'front' => 'Materials', 'icon' => 'layers', 'ph' => 'مثلاً White gold' ),
		'gemstones' => array( 'label' => 'سنگ‌ها', 'front' => 'Gemstones', 'icon' => 'gem', 'ph' => 'مثلاً Emerald, diamond' ),
		'technique' => array( 'label' => 'تکنیک', 'front' => 'Technique', 'icon' => 'tool', 'ph' => 'مثلاً Digital rendering' ),
		'year'      => array( 'label' => 'سال', 'front' => 'Year', 'icon' => 'calendar', 'ph' => 'مثلاً 2026' ),
	);
}

/** All display data of one work. */
function ay_work( $id ) {
	$p = get_post( $id );
	if ( ! $p || 'ay_work' !== $p->post_type ) {
		return null;
	}
	$names = function ( $tax ) use ( $id ) {
		$t = get_the_terms( $id, $tax );
		return ( $t && ! is_wp_error( $t ) ) ? $t : array();
	};
	$w = array(
		'id'          => (int) $id,
		'title'       => get_the_title( $id ),
		'url'         => get_permalink( $id ),
		'desc'        => apply_filters( 'the_content', $p->post_content ),
		'excerpt'     => wp_strip_all_tags( $p->post_content ),
		'thumb'       => (int) get_post_thumbnail_id( $id ),
		'collections' => $names( 'ay_collection' ),
		'types'       => $names( 'ay_type' ),
		'featured'    => '1' === (string) get_post_meta( $id, '_ay_featured', true ),
	);
	foreach ( ay_work_fields() as $k => $f ) {
		$w[ $k ] = (string) get_post_meta( $id, '_ay_' . $k, true );
	}
	return $w;
}

/** The work a single-work widget should show: the current one, or the newest as an editor preview. */
function ay_current_work_id() {
	$id = get_the_ID();
	if ( $id && 'ay_work' === get_post_type( $id ) ) {
		return (int) $id;
	}
	$q = get_queried_object();
	if ( $q instanceof WP_Post && 'ay_work' === $q->post_type ) {
		return (int) $q->ID;
	}
	static $preview = null;
	if ( null === $preview ) {
		$found   = get_posts( array( 'post_type' => 'ay_work', 'numberposts' => 1, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'fields' => 'ids' ) );
		$preview = $found ? (int) $found[0] : 0;
	}
	return $preview;
}

/** Query works for the portfolio widgets. */
function ay_query_works( $args = array() ) {
	$a = wp_parse_args( $args, array( 'collection' => array(), 'type' => array(), 'featured' => false, 'count' => -1, 'order' => 'menu', 'exclude' => array(), 'include' => array() ) );
	$q = array( 'post_type' => 'ay_work', 'post_status' => 'publish', 'posts_per_page' => (int) $a['count'] ? (int) $a['count'] : -1, 'no_found_rows' => true, 'fields' => 'ids' );
	switch ( $a['order'] ) {
		case 'new':
			$q['orderby'] = 'date';
			$q['order']   = 'DESC';
			break;
		case 'old':
			$q['orderby'] = 'date';
			$q['order']   = 'ASC';
			break;
		case 'title':
			$q['orderby'] = 'title';
			$q['order']   = 'ASC';
			break;
		case 'rand':
			$q['orderby'] = 'rand';
			break;
		default:
			$q['orderby'] = array( 'menu_order' => 'ASC', 'date' => 'DESC' );
	}
	$tax = array();
	if ( array_filter( (array) $a['collection'] ) ) {
		$tax[] = array( 'taxonomy' => 'ay_collection', 'field' => 'term_id', 'terms' => array_map( 'absint', (array) $a['collection'] ) );
	}
	if ( array_filter( (array) $a['type'] ) ) {
		$tax[] = array( 'taxonomy' => 'ay_type', 'field' => 'term_id', 'terms' => array_map( 'absint', (array) $a['type'] ) );
	}
	if ( $tax ) {
		$q['tax_query'] = $tax; // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	if ( $a['featured'] ) {
		$q['meta_key']   = '_ay_featured'; // phpcs:ignore WordPress.DB.SlowDBQuery
		$q['meta_value'] = '1'; // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	if ( array_filter( (array) $a['exclude'] ) ) {
		$q['post__not_in'] = array_map( 'absint', (array) $a['exclude'] );
	}
	if ( array_filter( (array) $a['include'] ) ) {
		$q['post__in'] = array_map( 'absint', (array) $a['include'] );
		$q['orderby']  = 'post__in';
	}
	return get_posts( $q );
}

/** <img> for a work (whole piece visible, never cropped). */
function ay_work_img( $id, $size = 'large', $attr = array() ) {
	$thumb = (int) get_post_thumbnail_id( $id );
	if ( ! $thumb ) {
		return '';
	}
	$alt = get_post_meta( $thumb, '_wp_attachment_image_alt', true );
	return wp_get_attachment_image( $thumb, $size, false, wp_parse_args( $attr, array( 'alt' => $alt ? $alt : get_the_title( $id ), 'loading' => 'lazy' ) ) );
}

/** The FAQ copy supplied by the client (kept word for word). */
function ay_faq_default() {
	static $data = null;
	if ( null === $data ) {
		$data = json_decode( (string) file_get_contents( AY_DIR . 'includes/data/faq.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$data = is_array( $data ) ? $data : array();
	}
	return $data;
}

/** Only administrators (or editors for content) may run state-changing admin actions. */
function ay_can( $cap = 'manage_options' ) {
	return current_user_can( $cap );
}

/* -------------------------------------------------- site-wide sketch wallpaper */
function ay_wallpaper_defaults() {
	return array(
		'on'       => 1,
		'opacity'  => 7,      // percent
		'size'     => 520,    // px on desktop
		'spacing'  => 760,    // px between motifs down the page
		'side'     => 'alt',  // alt | left | right | center
		'rotate'   => 12,     // max random rotation in degrees
		'parallax' => 12,     // percent of scroll
		'blur'     => 0,
		'color'    => '',     // empty = follows the text colour (light on dark themes, dark on light themes)
		'mobile'   => 1,
		'motifs'   => array( 'botanical-necklace', 'filigree-rings', 'floral-set', 'ribbon-pendant', 'butterfly-set' ),
		'custom'   => array(),
		'skip'     => array(),  // post ids without the wallpaper
	);
}

function ay_wallpaper_motifs() {
	return array(
		'botanical-necklace' => 'گردنبند گیاهی',
		'filigree-rings'     => 'حلقه‌های ملیله',
		'floral-set'         => 'ست گل‌دار',
		'ribbon-pendant'     => 'آویز روبانی',
		'butterfly-set'      => 'ست پروانه',
		'necklace-flowers'   => 'گل‌های گردنبند',
	);
}

function ay_wallpaper_settings() {
	return wp_parse_args( (array) get_option( 'arezoo_wallpaper', array() ), ay_wallpaper_defaults() );
}

/** What the front end needs to draw the wallpaper (null when it is off for this page). */
function ay_wallpaper_config() {
	$w = ay_wallpaper_settings();
	if ( empty( $w['on'] ) ) {
		return null;
	}
	$id = get_queried_object_id();
	if ( $id && in_array( (int) $id, array_map( 'absint', (array) $w['skip'] ), true ) ) {
		return null;
	}
	$urls = array();
	foreach ( (array) $w['motifs'] as $m ) {
		if ( isset( ay_wallpaper_motifs()[ $m ] ) ) {
			$urls[] = ay_asset( 'img/sketch/' . $m . '.webp' );
		}
	}
	foreach ( (array) $w['custom'] as $att ) {
		$u = wp_get_attachment_image_url( absint( $att ), 'large' );
		if ( $u ) {
			$urls[] = $u;
		}
	}
	if ( ! $urls ) {
		return null;
	}
	return array(
		'urls'     => $urls,
		'opacity'  => max( 0, min( 100, (float) $w['opacity'] ) ) / 100,
		'size'     => max( 120, (int) $w['size'] ),
		'spacing'  => max( 240, (int) $w['spacing'] ),
		'side'     => in_array( $w['side'], array( 'alt', 'left', 'right', 'center' ), true ) ? $w['side'] : 'alt',
		'rotate'   => (float) $w['rotate'],
		'parallax' => (float) $w['parallax'] / 100,
		'blur'     => (float) $w['blur'],
		'color'    => $w['color'] ? ay_hex( $w['color'] ) : '',
		'mobile'   => ! empty( $w['mobile'] ),
	);
}
