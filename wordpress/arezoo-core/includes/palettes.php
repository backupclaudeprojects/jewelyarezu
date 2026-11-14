<?php
/**
 * 96 colour themes: 2 palettes x 4 families (by page background) x 12 role combinations
 * (3 accents x 2 button styles x 2 surface treatments). Only each palette's own colours
 * and their tints / shades are used.
 *
 * Applying a theme only changes the site-wide (global) colours. Any colour set by hand
 * on a section, widget or button stays exactly as it is.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ay_palette_sets() {
	return array(
		'b' => array( 'label' => 'پالت دوم · شب', 'colors' => array( '#0B1421', '#272737', '#B5B9BC', '#283041', '#DFDFDF' ) ),
		'a' => array( 'label' => 'پالت اول · مینت', 'colors' => array( '#32292F', '#99E1D9', '#F0F7F4', '#705D56' ) ),
	);
}

function ay_color_names() {
	return array(
		'#32292F' => 'ریزن بلک',
		'#99E1D9' => 'آبی تیفانی',
		'#F0F7F4' => 'مینت کرم',
		'#705D56' => 'ونگه',
		'#0B1421' => 'سرمه‌ای شب',
		'#272737' => 'بادمجانی دودی',
		'#B5B9BC' => 'نقره‌ای',
		'#283041' => 'سرمه‌ای دودی',
		'#DFDFDF' => 'خاکستری روشن',
	);
}

function ay_theme_families() {
	$r = '#32292F';
	$t = '#99E1D9';
	$m = '#F0F7F4';
	$w = '#705D56';
	$k = '#0B1421';
	$p = '#272737';
	$s = '#B5B9BC';
	$n = '#283041';
	$l = '#DFDFDF';
	return array(
		array( 'id' => 'midnight', 'set' => 'b', 'label' => 'سرمه‌ای شب', 'bg' => $k, 'ink' => $l, 'muted' => ay_mix( $s, $k, 0.12 ), 'accents' => array( $s, $l, $p ), 'bands' => array( ay_mix( $k, '#000000', 0.4 ), $p ), 'dk' => $k, 'lt' => $l ),
		array( 'id' => 'plum', 'set' => 'b', 'label' => 'بادمجانی دودی', 'bg' => $p, 'ink' => $l, 'muted' => ay_mix( $s, $p, 0.14 ), 'accents' => array( $s, $l, $k ), 'bands' => array( $k, $n ), 'dk' => $k, 'lt' => $l ),
		array( 'id' => 'slate', 'set' => 'b', 'label' => 'سرمه‌ای دودی', 'bg' => $n, 'ink' => $l, 'muted' => ay_mix( $s, $n, 0.14 ), 'accents' => array( $s, $l, $k ), 'bands' => array( $k, $p ), 'dk' => $k, 'lt' => $l ),
		array( 'id' => 'silver', 'set' => 'b', 'label' => 'خاکستری روشن', 'bg' => $l, 'ink' => $k, 'muted' => ay_mix( $n, $l, 0.22 ), 'accents' => array( $n, $p, $s ), 'bands' => array( $k, $n ), 'dk' => $k, 'lt' => $l ),
		array( 'id' => 'mint', 'set' => 'a', 'label' => 'مینت کرم (روشن)', 'bg' => $m, 'ink' => $r, 'muted' => $w, 'accents' => array( $t, $w, $r ), 'bands' => array( $r, $w ), 'dk' => $r, 'lt' => $m ),
		array( 'id' => 'tiffany', 'set' => 'a', 'label' => 'آبی تیفانی', 'bg' => $t, 'ink' => $r, 'muted' => ay_mix( $r, $t, 0.22 ), 'accents' => array( $r, $w, $m ), 'bands' => array( $r, $w ), 'dk' => $r, 'lt' => $m ),
		array( 'id' => 'wenge', 'set' => 'a', 'label' => 'ونگه', 'bg' => $w, 'ink' => $m, 'muted' => ay_mix( $m, $w, 0.28 ), 'accents' => array( $t, $m, $r ), 'bands' => array( $r, ay_mix( $w, $r, 0.55 ) ), 'dk' => $r, 'lt' => $m ),
		array( 'id' => 'raisin', 'set' => 'a', 'label' => 'ریزن بلک (تیره)', 'bg' => $r, 'ink' => $m, 'muted' => ay_mix( $m, $r, 0.34 ), 'accents' => array( $t, $w, $m ), 'bands' => array( ay_mix( $r, '#000000', 0.32 ), $w ), 'dk' => $r, 'lt' => $m ),
	);
}

/** All 96 themes, keyed by id (e.g. "midnight-01"). */
function ay_themes() {
	static $themes = null;
	if ( null !== $themes ) {
		return $themes;
	}
	$themes = array();
	$names  = ay_color_names();
	foreach ( ay_theme_families() as $f ) {
		$dark     = ay_lum( $f['bg'] ) < 0.3;
		$readable = function ( $bg ) use ( $f ) {
			return ay_contrast( $bg, $f['dk'] ) >= ay_contrast( $bg, $f['lt'] ) ? $f['dk'] : $f['lt'];
		};
		$n = 0;
		foreach ( $f['accents'] as $acc ) {
			foreach ( array( 'ink', 'accent' ) as $btn ) {
				foreach ( array( 'soft', 'tint' ) as $si => $surf ) {
					$n++;
					$surface  = 'soft' === $surf ? ( $dark ? ay_mix( $f['bg'], $f['ink'], 0.07 ) : ay_mix( $f['bg'], '#FFFFFF', 0.55 ) ) : ay_mix( $f['bg'], $acc, $dark ? 0.2 : 0.14 );
					$surface2 = ay_mix( $f['bg'], $acc, 'soft' === $surf ? 0.24 : 0.36 );
					$btn_bg   = 'ink' === $btn ? $f['ink'] : $acc;
					$at       = $acc;
					if ( ay_contrast( $at, $f['bg'] ) < 3 ) {
						$at = ay_mix( $acc, $f['ink'], 0.58 );
					}
					if ( ay_contrast( $at, $f['bg'] ) < 3 ) {
						$at = $f['ink'];
					}
					$band     = $f['bands'][ $si ];
					$band_ink = $readable( $band );
					$id       = $f['id'] . '-' . sprintf( '%02d', $n );
					$themes[ $id ] = array(
						'id'     => $id,
						'family' => $f['id'],
						'set'    => $f['set'],
						'dark'   => $dark,
						'label'  => $f['label'] . ' ' . sprintf( '%02d', $n ),
						'desc'   => 'تأکید: ' . ( isset( $names[ $acc ] ) ? $names[ $acc ] : $acc ) . ' · دکمه: ' . ( 'ink' === $btn ? 'رنگ متن' : 'رنگ تأکید' ) . ' · سطح: ' . ( 'soft' === $surf ? 'ملایم' : 'رنگی' ),
						'colors' => array(
							'bg'         => $f['bg'],
							'surface'    => $surface,
							'surface2'   => $surface2,
							'ink'        => $f['ink'],
							'muted'      => $f['muted'],
							'accent'     => $acc,
							'accenttext' => $at,
							'btn'        => $btn_bg,
							'btnink'     => $readable( $btn_bg ),
							'btnline'    => ay_contrast( $btn_bg, $f['bg'] ) < 1.6 ? ay_mix( $btn_bg, $f['ink'], 0.4 ) : $btn_bg,
							'band'       => $band,
							'bandink'    => $band_ink,
							'bandmuted'  => ay_mix( $band_ink, $band, 0.38 ),
						),
					);
				}
			}
		}
	}
	return $themes;
}

function ay_default_theme_id() {
	return 'midnight-01';
}

function ay_active_theme_id() {
	$id     = (string) get_option( 'arezoo_theme', ay_default_theme_id() );
	$themes = ay_themes();
	return isset( $themes[ $id ] ) ? $id : ay_default_theme_id();
}

function ay_active_theme() {
	$themes = ay_themes();
	return $themes[ ay_active_theme_id() ];
}

/** Persian names of the colour roles (shown as global colours in Elementor). */
function ay_role_labels() {
	return array(
		'bg'         => 'آرزو · زمینهٔ صفحه',
		'surface'    => 'آرزو · سطح',
		'surface2'   => 'آرزو · سطح دوم',
		'ink'        => 'آرزو · متن',
		'muted'      => 'آرزو · متن کم‌رنگ',
		'accent'     => 'آرزو · تأکید',
		'accenttext' => 'آرزو · متن تأکیدی',
		'btn'        => 'آرزو · دکمه',
		'btnink'     => 'آرزو · متن دکمه',
		'btnline'    => 'آرزو · خط دور دکمه',
		'band'       => 'آرزو · نوار تیره',
		'bandink'    => 'آرزو · متن نوار',
		'bandmuted'  => 'آرزو · متن کم‌رنگ نوار',
	);
}

/**
 * The colour roles as CSS variables on <body>. Each one follows the matching Elementor
 * global colour when it exists, so editing a global colour in Site Settings also reaches
 * the Arezoo widgets.
 */
function ay_theme_css() {
	$t   = ay_active_theme();
	$css = 'body{';
	foreach ( $t['colors'] as $role => $hex ) {
		$css .= '--ay-' . $role . ':var(--e-global-color-ay' . $role . ',' . $hex . ');';
	}
	$css .= 'color-scheme:' . ( $t['dark'] ? 'dark' : 'light' ) . ';}';
	return $css;
}

add_action( 'wp_head', function () {
	echo '<style id="arezoo-theme-colors">' . ay_theme_css() . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 6 );

add_filter( 'body_class', function ( $classes ) {
	$t         = ay_active_theme();
	$classes[] = $t['dark'] ? 'ay-dark' : 'ay-light';
	$classes[] = 'ay-theme-' . sanitize_html_class( $t['id'] );
	return $classes;
} );

/**
 * Apply a theme: remember it and write its roles into the active Elementor kit as
 * global colours. Returns true on success.
 */
function ay_apply_theme( $id ) {
	$themes = ay_themes();
	if ( ! isset( $themes[ $id ] ) ) {
		return false;
	}
	$t = $themes[ $id ];
	update_option( 'arezoo_theme_prev', ay_active_theme_id() );
	update_option( 'arezoo_theme', $id );
	if ( ! ay_elementor_ready() ) {
		return true;
	}
	$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
	if ( ! $kit || ! $kit->get_id() ) {
		return true;
	}
	$settings = get_post_meta( $kit->get_id(), '_elementor_page_settings', true );
	$settings = is_array( $settings ) ? $settings : array();
	$c        = $t['colors'];

	$settings['system_colors'] = array(
		array( '_id' => 'primary', 'title' => 'Primary', 'color' => $c['ink'] ),
		array( '_id' => 'secondary', 'title' => 'Secondary', 'color' => $c['muted'] ),
		array( '_id' => 'text', 'title' => 'Text', 'color' => $c['ink'] ),
		array( '_id' => 'accent', 'title' => 'Accent', 'color' => $c['accenttext'] ),
	);
	$custom = array();
	foreach ( isset( $settings['custom_colors'] ) && is_array( $settings['custom_colors'] ) ? $settings['custom_colors'] : array() as $row ) {
		if ( isset( $row['_id'] ) && 0 !== strpos( (string) $row['_id'], 'ay' ) ) {
			$custom[] = $row;   // colours the owner added stay
		}
	}
	foreach ( ay_role_labels() as $role => $label ) {
		$custom[] = array( '_id' => 'ay' . $role, 'title' => $label, 'color' => $c[ $role ] );
	}
	$settings['custom_colors'] = $custom;

	update_post_meta( $kit->get_id(), '_elementor_page_settings', wp_slash( $settings ) );
	\Elementor\Plugin::$instance->files_manager->clear_cache();
	return true;
}
