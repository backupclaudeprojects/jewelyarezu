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
		'use_collections' => 1,
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
		/* networks */
		'whatsapp'  => '<path d="M4 20l1.3-4A8 8 0 1 1 8.2 19z"/><path d="M9 8.6c0 3.2 2.9 6.4 6.3 6.4l1.2-1.4-1.9-1-.9.8c-1-.4-2.3-1.7-2.7-2.7l.8-.9-1-1.9z"/>',
		'linkedin'  => '<rect x="3.5" y="3.5" width="17" height="17" rx="3"/><path d="M8 10.5V16M8 7.6v.1M11.5 16v-5.5M11.5 13c0-1.6 1-2.6 2.3-2.6s2.2.9 2.2 2.6V16"/>',
		'pinterest' => '<circle cx="12" cy="12" r="8.5"/><path d="M10.6 20.2 12.4 13M11.4 13.6c.6.6 1.4.9 2.2.9 2.1 0 3.4-1.9 3.4-4.1 0-2.5-2.1-4.1-4.6-4.1-2.8 0-4.6 2-4.6 4.2 0 1 .4 1.9 1.1 2.3"/>',
		'behance'   => '<path d="M3 7h5a2.5 2.5 0 0 1 0 5H3zM3 12h5.6a2.6 2.6 0 0 1 0 5.2H3zM3 7v10.2"/><path d="M14 13.6h7a3.5 3.5 0 0 0-7 0 3.5 3.5 0 0 0 6.5 1.8M15 8h5"/>',
		'dribbble'  => '<circle cx="12" cy="12" r="8.5"/><path d="M7.5 5c3 3.6 5.7 8.7 7 15M3.6 10.5c5 .3 9.6-1 12.8-3.9M8 19.2c2.3-3.5 6.4-5 12.3-4"/>',
		'x'         => '<path d="M4.5 4.5 19.5 19.5M19.5 4.5l-6 6.6M10.4 13.6 4.5 19.5"/><path d="M4.5 4.5h4.2l10.8 15h-4.2z"/>',
		'facebook'  => '<path d="M14 21v-7.5h2.6l.4-3H14V8.7c0-.9.3-1.5 1.5-1.5H17V4.5c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.3-3.7 3.8v2.3H8.5v3H11V21"/>',
		'youtube'   => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10.2 9.2 4.8 2.8-4.8 2.8z"/>',
		'tiktok'    => '<path d="M13.5 3.5v11.2a3.3 3.3 0 1 1-3.3-3.3"/><path d="M13.5 3.5c.4 2.6 2.2 4.4 5 4.6"/>',
		'threads'   => '<path d="M16.6 10.4c-.5-2.6-2.3-3.9-4.6-3.9-2.9 0-4.9 2.2-4.9 5.5s2 5.5 5 5.5c2.6 0 4.4-1.4 4.4-3.5 0-2.6-2.8-3.2-5-2.9-1.6.2-2.6 1-2.6 2.1 0 1.1 1 1.8 2.3 1.8 2.1 0 3.4-1.8 3.4-4.8"/><path d="M12 21c-5 0-8.5-3.6-8.5-9S7 3 12 3c3.8 0 6.5 1.9 7.6 5.3"/>',
		'vimeo'     => '<path d="M3 8.6 4 9.8c1-.7 1.6-1 2-.7.6.5 2.2 9.4 4.6 9.4 2.6 0 7.4-6.4 7.9-9.8.4-2.4-.6-3.9-2.8-3.6-1.6.2-3 1.6-3.4 3.4 1-.6 2.4-.5 2 1.4-.4 2-2.5 4.9-3.2 4.9S9.4 8.1 8 6.3C7.1 5.2 5.3 6.5 3 8.6z"/>',
		'artstation'=> '<path d="M3 16.5 6 21h11.5l-2.6-4.5zM9.6 3h4.8l7.3 12.7-2.5 4.2zM2.3 15.5l5.7-9.8 4.2 7.3"/>',
		'etsy'      => '<path d="M8 4.5h8.5l.3 3.2M8 4.5v15M8 12h6M8 19.5h9l.5-3.2"/>',
		'globe'     => '<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17M12 3.5c2.4 2.5 3.4 5.3 3.4 8.5s-1 6-3.4 8.5c-2.4-2.5-3.4-5.3-3.4-8.5s1-6 3.4-8.5z"/>',
		'link'      => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
		'phone'     => '<path d="M5 4h3.5l1.5 4-2 1.5a10 10 0 0 0 6.5 6.5l1.5-2 4 1.5V19a1.5 1.5 0 0 1-1.6 1.5A16 16 0 0 1 3.5 5.6 1.5 1.5 0 0 1 5 4z"/>',
		'heart'     => '<path d="M12 20s-7.5-4.6-7.5-10.2A4.3 4.3 0 0 1 12 7a4.3 4.3 0 0 1 7.5 2.8C19.5 15.4 12 20 12 20z"/>',
		'bag'       => '<path d="M5 8h14l-1 12H6zM9 8V6.5a3 3 0 0 1 6 0V8"/>',
		'pen'       => '<path d="m4 20 1-4L16.5 4.5a2.1 2.1 0 0 1 3 3L8 19z"/><path d="m14.5 6.5 3 3"/>',
		'chat'      => '<path d="M4 5h16v11H9l-5 4z"/>',
		'download'  => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
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
/** Networks that can be listed (آرزو ← تنظیمات ← برند و تماس). [ key => [ label, icon, url builder pattern, placeholder ] ] */
function ay_social_networks() {
	return array(
		'email'      => array( 'Email', 'mail', 'mailto:%s', 'name@example.com' ),
		'instagram'  => array( 'Instagram', 'instagram', 'https://www.instagram.com/%s/', 'شناسه یا نشانی کامل' ),
		'telegram'   => array( 'Telegram', 'telegram', 'https://t.me/%s', 'شناسه یا نشانی کامل' ),
		'whatsapp'   => array( 'WhatsApp', 'whatsapp', 'https://wa.me/%s', 'شمارهٔ بین‌المللی بدون + یا نشانی کامل' ),
		'linkedin'   => array( 'LinkedIn', 'linkedin', 'https://www.linkedin.com/in/%s/', 'شناسه یا نشانی کامل' ),
		'pinterest'  => array( 'Pinterest', 'pinterest', 'https://www.pinterest.com/%s/', 'شناسه یا نشانی کامل' ),
		'behance'    => array( 'Behance', 'behance', 'https://www.behance.net/%s', 'شناسه یا نشانی کامل' ),
		'dribbble'   => array( 'Dribbble', 'dribbble', 'https://dribbble.com/%s', 'شناسه یا نشانی کامل' ),
		'x'          => array( 'X', 'x', 'https://x.com/%s', 'شناسه یا نشانی کامل' ),
		'facebook'   => array( 'Facebook', 'facebook', 'https://www.facebook.com/%s', 'شناسه یا نشانی کامل' ),
		'youtube'    => array( 'YouTube', 'youtube', 'https://www.youtube.com/@%s', 'شناسه یا نشانی کامل' ),
		'tiktok'     => array( 'TikTok', 'tiktok', 'https://www.tiktok.com/@%s', 'شناسه یا نشانی کامل' ),
		'threads'    => array( 'Threads', 'threads', 'https://www.threads.net/@%s', 'شناسه یا نشانی کامل' ),
		'vimeo'      => array( 'Vimeo', 'vimeo', 'https://vimeo.com/%s', 'شناسه یا نشانی کامل' ),
		'artstation' => array( 'ArtStation', 'artstation', 'https://www.artstation.com/%s', 'شناسه یا نشانی کامل' ),
		'etsy'       => array( 'Etsy', 'etsy', 'https://www.etsy.com/shop/%s', 'نام فروشگاه یا نشانی کامل' ),
		'website'    => array( 'Website', 'globe', '%s', 'https://…' ),
		'custom'     => array( 'Link', 'link', '%s', 'نشانی کامل' ),
	);
}

/** The list of networks as saved; before the first save it is built from the old e-mail / Instagram / Telegram fields. */
function ay_social_rows() {
	$rows = get_option( 'arezoo_socials', null );
	if ( is_array( $rows ) ) {
		return $rows;
	}
	$rows = array();
	foreach ( array( 'email', 'instagram', 'telegram' ) as $k ) {
		$v = trim( (string) ay_opt( $k ) );
		if ( '' !== $v ) {
			$rows[] = array( 'net' => $k, 'value' => $v, 'label' => '', 'icon' => 0, 'on' => 1 );
		}
	}
	return $rows;
}

/** One saved row as display data, or null. */
function ay_social_item( $row ) {
	$nets = ay_social_networks();
	$net  = isset( $row['net'], $nets[ $row['net'] ] ) ? $row['net'] : 'custom';
	$val  = trim( (string) ( isset( $row['value'] ) ? $row['value'] : '' ) );
	if ( '' === $val || ( isset( $row['on'] ) && ! $row['on'] ) ) {
		return null;
	}
	$full = (bool) preg_match( '#^(https?:|mailto:|tel:)#i', $val );
	if ( 'email' === $net ) {
		$em = sanitize_email( str_replace( 'mailto:', '', $val ) );
		if ( ! $em ) {
			return null;
		}
		$url  = 'mailto:' . $em;
		$show = $em;
	} elseif ( $full ) {
		$url  = $val;
		$show = preg_replace( '#^https?://(www\.)?#i', '', rtrim( $val, '/' ) );
	} else {
		$handle = ltrim( $val, '@' );
		if ( 'whatsapp' === $net ) {
			$handle = preg_replace( '/\D+/', '', $handle );
		}
		$url  = in_array( $net, array( 'website', 'custom' ), true ) ? 'https://' . ltrim( $val, '/' ) : sprintf( $nets[ $net ][2], rawurlencode( $handle ) );
		$show = in_array( $net, array( 'instagram', 'telegram', 'x', 'tiktok', 'threads', 'youtube' ), true ) ? '@' . $handle : $handle;
	}
	$label = '' !== trim( (string) ( isset( $row['label'] ) ? $row['label'] : '' ) ) ? trim( $row['label'] ) : $nets[ $net ][0];
	$img   = ! empty( $row['icon'] ) ? (string) wp_get_attachment_image_url( absint( $row['icon'] ), 'thumbnail' ) : '';
	return array( 'net' => $net, 'label' => $label, 'value' => $show, 'url' => $url, 'icon' => $nets[ $net ][1], 'img' => $img );
}

/**
 * Every network to show, in the saved order. The first e-mail, Instagram and Telegram keep their old keys,
 * so widgets that switch those three on and off keep working.
 */
function ay_contact() {
	$out  = array();
	$seen = array();
	foreach ( ay_social_rows() as $i => $row ) {
		$it = ay_social_item( $row );
		if ( ! $it ) {
			continue;
		}
		$key = in_array( $it['net'], array( 'email', 'instagram', 'telegram' ), true ) && empty( $seen[ $it['net'] ] ) ? $it['net'] : $it['net'] . '-' . $i;
		$seen[ $it['net'] ] = true;
		$out[ $key ]        = $it;
	}
	return $out;
}

/** Icon of a network or link: the owner's own picture (tinted like the line icons) or a line icon. */
function ay_social_icon( $it, $size = 20 ) {
	if ( ! empty( $it['img'] ) ) {
		return '<span class="ay-ic ay-ic-img" style="width:' . (int) $size . 'px;height:' . (int) $size . 'px;--img:url(' . esc_url( $it['img'] ) . ')" aria-hidden="true"></span>';
	}
	return ay_icon( isset( $it['icon'] ) ? $it['icon'] : 'link', $size );
}

/* ------------------------------------------------------------ collections on/off */
/** Are collections used on this site? (آرزو ← تنظیمات ← ظاهر آثار). Types always are. */
function ay_use_collections() {
	return (bool) ay_opt( 'use_collections', 1 );
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
		'collections' => ay_use_collections() ? $names( 'ay_collection' ) : array(),
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
	if ( array_filter( (array) $a['collection'] ) && ay_use_collections() ) {
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

/** The picture that stands for a work: its main image, or else the first picture of its gallery. */
function ay_work_cover_id( $id ) {
	$ids = ay_work_gallery_ids( $id );
	return $ids ? (int) $ids[0] : 0;
}

/** <img> for a work (whole piece visible, never cropped). */
function ay_work_img( $id, $size = 'large', $attr = array() ) {
	$thumb = ay_work_cover_id( $id );
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
		'density'  => 100,     // percent: how many motifs overall
		'weights'  => array(), // [ motif key or attachment id => 0..6 ] how often each design appears
		'scales'   => array(), // [ motif key or attachment id => percent ] size of each design
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
	$items = array();
	$add   = function ( $key, $url ) use ( &$items, $w ) {
		$n = isset( $w['weights'][ $key ] ) ? max( 0, min( 6, (int) $w['weights'][ $key ] ) ) : 1;
		$s = isset( $w['scales'][ $key ] ) ? max( 30, min( 300, (float) $w['scales'][ $key ] ) ) / 100 : 1;
		if ( $n > 0 && $url ) {
			$items[] = array( 'u' => $url, 's' => $s, 'n' => $n );
		}
	};
	foreach ( (array) $w['motifs'] as $m ) {
		if ( isset( ay_wallpaper_motifs()[ $m ] ) ) {
			$add( $m, ay_asset( 'img/sketch/' . $m . '.webp' ) );
		}
	}
	foreach ( (array) $w['custom'] as $att ) {
		$add( (string) absint( $att ), wp_get_attachment_image_url( absint( $att ), 'large' ) );
	}
	if ( ! $items ) {
		return null;
	}
	/* deal the designs out in turn, so one with a larger share is spread through the page rather than bunched */
	$urls = array();
	$list = array();
	for ( $round = 0; $round < 6; $round++ ) {
		foreach ( $items as $it ) {
			if ( $it['n'] > $round ) {
				$urls[] = $it['u'];
				$list[] = $it['s'];
			}
		}
	}
	return array(
		'urls'     => $urls,
		'scales'   => $list,
		'density'  => max( 10, min( 500, (float) $w['density'] ) ) / 100,
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

/* --------------------------------------------- how each work looks on the site */
/** Site-wide halo behind the pieces (آرزو ← تنظیمات ← ظاهر آثار). */
function ay_halo_defaults() {
	return array(
		'on'    => 1,
		'color' => '',    // empty = accent colour of the colour theme
		'op'    => 38,    // percent
		'size'  => 70,    // percent of the picture frame
		'blur'  => 26,    // px
		'anim'  => 'breathe',
	);
}

function ay_halo_settings() {
	return wp_parse_args( (array) get_option( 'arezoo_halo', array() ), ay_halo_defaults() );
}

/** Colours chosen for one work in its edit screen (empty = the widget's or the site's colour). */
function ay_work_look( $id ) {
	$raw = json_decode( (string) get_post_meta( $id, '_ay_look', true ), true );
	return wp_parse_args( is_array( $raw ) ? $raw : array(), array( 'halo' => '', 'halo_op' => '', 'halo_off' => 0, 'glow' => '', 'line' => '', 'wash' => '', 'wash2' => '' ) );
}

/** Should the halo be drawn behind this work? */
function ay_work_halo_on( $id ) {
	$g = ay_halo_settings();
	if ( empty( $g['on'] ) ) {
		return false;
	}
	$l = ay_work_look( $id );
	return empty( $l['halo_off'] );
}

/** The work's colours as CSS variables, for a style attribute. */
function ay_work_vars( $id ) {
	$l   = ay_work_look( $id );
	$css = '';
	foreach ( array( 'halo' => '--ay-wh', 'glow' => '--ay-cg-c', 'wash' => '--ay-wash', 'wash2' => '--ay-wash2' ) as $k => $var ) {
		if ( '' !== $l[ $k ] ) {
			$css .= $var . ':' . ay_hex( $l[ $k ] ) . ';';
		}
	}
	if ( '' !== $l['line'] ) {
		$css .= '--ay-cgl-c:' . ay_hex( $l['line'] ) . ';--ay-cgl-blend:screen;';
	}
	if ( '' !== (string) $l['halo_op'] && is_numeric( $l['halo_op'] ) ) {
		$css .= '--ay-wh-op:' . ( max( 0, min( 100, (float) $l['halo_op'] ) ) / 100 ) . ';';
	}
	return $css;
}

/** The halo layer placed behind a piece. */
function ay_work_halo( $id, $class = 'ay-wh-l' ) {
	return ay_work_halo_on( $id ) ? '<span class="' . esc_attr( $class ) . '" aria-hidden="true"></span>' : '';
}
