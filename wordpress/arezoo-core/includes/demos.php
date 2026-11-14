<?php
/**
 * Demo library: five layouts for each major area (hero, collections archive, single work,
 * About, FAQ, header, footer). Every demo is built from real Elementor containers and
 * widgets, so after «اعمال» it opens in Elementor as ordinary editable elements.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------------- small helpers */
function ay_page_url( $slug ) {
	$p = get_page_by_path( $slug );
	return $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
}

/** Media setting ( id + url ) for a bundled picture that the setup copied into the Media Library. */
function ay_media( $rel ) {
	$id = function_exists( 'ay_asset_attachment' ) ? ay_asset_attachment( $rel, '', false ) : 0;
	return $id ? array( 'id' => $id, 'url' => wp_get_attachment_url( $id ) ) : array( 'id' => '', 'url' => ay_asset( $rel ) );
}

/** Media setting by URL only (bundled file that is never removed with the sample works). */
function ay_media_url( $rel ) {
	return array( 'id' => '', 'url' => ay_asset( $rel ) );
}

function ay_pad_top() {
	return array( 'padding' => ay_box( 160, 24, 30, 24 ), 'padding_mobile' => ay_box( 120, 16, 20, 16 ) );
}

function ay_page_head( $eyebrow, $title, $lede = '' ) {
	return ay_band( array( ay_eyebrow( $eyebrow ), ay_heading( $title, 'h1', 74 ), $lede ? ay_text( '<p>' . $lede . '</p>', array( 'typography_font_size' => ay_px( 19 ) ) ) : null ), array_merge( ay_pad_top(), array( 'flex_gap' => ay_gap( 14 ) ) ) );
}

function ay_closing( $title, $btn, $url ) {
	return ay_band( array( ay_heading( $title, 'h2', 50 ), ay_button( $btn, $url ) ), array( 'flex_gap' => ay_gap( 26 ), 'padding' => ay_box( 60, 24, 110, 24 ), 'ay_rv' => 'up' ) );
}

/* =========================================================================
 * HERO — replaces the section with CSS ID «ay-hero» on the home page
 * ====================================================================== */
function ay_demo_hero( $v = 1 ) {
	$copy = function ( $align = 'flex-start', $with_title = true, $on_picture = true ) {
		$kids = array();
		if ( $with_title ) {
			$center = 'center' === $align ? array( 'align' => 'center' ) : array();
			$light  = $on_picture ? array( 'title_color' => '#EDEAF0', '__globals__' => array() ) : array();
			$soft   = $on_picture ? array( 'title_color' => 'rgba(237,234,240,0.78)', '__globals__' => array() ) : array();
			$kids[] = ay_eyebrow( 'Arezoo Yousefi · Jewelry Design', array_merge( $center, $soft ) );
			$kids[] = ay_heading( 'Every Jewel<br><em>Begins as a Line</em>', 'h1', 68, array_merge( $center, $light ) );
			$kids[] = ay_text( '<p>Original jewelry concepts, bespoke design and collection development.</p>', array_merge( $center, $on_picture ? array( 'text_color' => 'rgba(237,234,240,0.78)', '__globals__' => array() ) : array(), array( '_element_custom_width' => ay_px( 360 ), '_element_width' => 'initial' ) ) );
		}
		$kids[] = ay_button( 'Explore Jewelry', ay_page_url( 'collections' ), 'line', $on_picture ? array( 'button_text_color' => '#EDEAF0', 'border_color' => 'rgba(237,234,240,0.8)', 'background_color' => 'rgba(11,20,33,0.35)', 'hover_color' => '#0B1421', 'button_background_hover_color' => '#DFDFDF', 'button_hover_border_color' => '#DFDFDF', '__globals__' => array(), 'align' => 'center' === $align ? 'center' : '' ) : array() );
		return ay_col( $kids, array( 'flex_gap' => ay_gap( 18 ), 'flex_align_items' => $align, 'ay_rv' => 'up' ) );
	};
	$section = function ( $hero, $content, $extra = array() ) {
		return ay_c( array_merge( array(
			'_element_id'             => 'ay-hero',
			'content_width'           => 'boxed',
			'boxed_width'             => ay_px( 1180 ),
			'min_height'              => ay_px( 92, 'vh' ),
			'min_height_tablet'       => ay_px( 70, 'vh' ),
			'min_height_mobile'       => ay_px( 0, 'px' ),
			'flex_direction'          => 'column',
			'flex_justify_content'    => 'flex-end',
			'padding'                 => ay_box( 150, 24, 110, 24 ),
			'padding_mobile'          => array( 'unit' => 'custom', 'top' => '64vh', 'right' => '16px', 'bottom' => '46px', 'left' => '16px', 'isLinked' => false ),
			'overflow'                => 'hidden',
		), $extra ), array( $hero, $content ) );
	};
	$night = array( 'mode' => 'regions', 'image' => ay_media( 'img/hero/night-garden.jpg' ), 'fill' => 'yes', 'veil' => 'left', 'fade' => 'yes', 'fill_h_mobile' => ay_px( 64, 'vh' ), 'veil_size_mobile' => ay_px( 8, '%' ), 'fade_size_mobile' => ay_px( 22, '%' ) );
	switch ( (int) $v ) {
		case 2:   /* separate layers */
			$L = function ( $label, $rel, $x, $y, $w, $motion, $amt, $dur, $depth, $extra = array() ) {
				return array_merge( array( 'label' => $label, 'image' => ay_media_url( $rel ), 'x' => ay_px( $x, '%' ), 'y' => ay_px( $y, '%' ), 'w' => ay_px( $w, '%' ), 'motion' => $motion, 'amt' => ay_px( $amt ), 'dur' => ay_px( $dur ), 'delay' => ay_px( 0 ), 'ox' => ay_px( 50, '%' ), 'oy' => ay_px( 0, '%' ), 'depth' => ay_px( $depth ), 'opacity' => ay_px( 1 ), 'z' => 1, 'hide_mobile' => '' ), $extra );
			};
			$layers = array(
				$L( 'نقش‌های مدادی', 'img/layers/sketch.webp', 0, 0, 100, '', 0, 10, 6, array( 'opacity' => ay_px( 0.5 ), 'z' => 0 ) ),
				$L( 'شاخه و شکوفه‌ها', 'img/layers/branch.webp', 0, 0, 100, 'sway', 6, 9, 14, array( 'ox' => ay_px( 100, '%' ), 'oy' => ay_px( 34, '%' ), 'z' => 1 ) ),
				$L( 'گوشوارهٔ چپ', 'img/works/emerald-kite-drop.webp', 58.4, 22, 9.4, 'sway', 12, 5.4, 20, array( 'z' => 3 ) ),
				$L( 'گوشوارهٔ راست', 'img/works/emerald-kite-drop.webp', 70.1, 25, 9.4, 'sway', 10, 6.3, 22, array( 'z' => 3, 'delay' => ay_px( 2 ) ) ),
				$L( 'مرغ مگس‌خوار', 'img/layers/bird.webp', 27.5, 4, 27.5, 'bob', 12, 5.5, 26, array( 'z' => 4, 'hide_mobile' => 'yes' ) ),
			);
			return $section( ay_w( 'ay-hero', array( 'mode' => 'layers', 'fill' => 'yes', 'ratio' => ay_px( 1.78 ), 'layers' => $layers, 'veil' => '', 'fade' => 'yes', 'bg' => 'rgba(0,0,0,0)', 'focus_x' => ay_px( 100, '%' ), 'fill_h_mobile' => ay_px( 60, 'vh' ) ) ), $copy( 'flex-start', true, false ) );
		case 3:   /* your own video */
			return $section( ay_w( 'ay-hero', array_merge( $night, array( 'mode' => 'video', 'veil' => 'bottom', 'veil_size' => ay_px( 70, '%' ) ) ) ), $copy( 'center' ), array( 'flex_align_items' => 'center' ) );
		case 4:   /* slideshow */
			$slides = array();
			foreach ( array( '07-colour-necklace', '04-rendering', '06-colour-medallions', '09-ring-render' ) as $n ) {
				$slides[] = ay_media( 'img/studio/' . $n . '.webp' );
			}
			return $section( ay_w( 'ay-hero', array( 'mode' => 'slideshow', 'fill' => 'yes', 'slides' => $slides, 'sl_time' => 5, 'sl_fade' => 1.6, 'veil' => 'full', 'veil_op' => ay_px( 0.7 ), 'fade' => 'yes', 'fill_h_mobile' => ay_px( 64, 'vh' ) ) ), $copy( 'center' ), array( 'flex_align_items' => 'center', 'flex_justify_content' => 'center' ) );
		case 5:   /* picture only, one button */
			return $section( ay_w( 'ay-hero', array_merge( $night, array( 'veil' => '', 'fill_h_mobile' => ay_px( 100, '%' ) ) ) ), $copy( 'center', false ), array( 'flex_align_items' => 'center', 'padding_mobile' => array( 'unit' => 'custom', 'top' => '70vh', 'right' => '16px', 'bottom' => '90px', 'left' => '16px', 'isLinked' => false ), 'min_height_mobile' => ay_px( 88, 'vh' ) ) );
		default:  /* the night garden picture with moving regions */
			return $section( ay_w( 'ay-hero', $night ), $copy() );
	}
}

/* =========================================================================
 * ARCHIVE — replaces the section with CSS ID «ay-archive» on the Collections page
 * ====================================================================== */
function ay_demo_archive( $v = 1 ) {
	$map = array(
		1 => array( 'layout' => 'grid', 'fx' => 'line', 'columns' => 3 ),
		2 => array( 'layout' => 'masonry', 'fx' => 'ink', 'columns' => 3 ),
		3 => array( 'layout' => 'list', 'fx' => 'space' ),
		4 => array( 'layout' => 'editorial', 'fx' => 'shimmer', 'columns' => 4 ),
		5 => array( 'layout' => 'slider', 'fx' => 'roll' ),
	);
	$s = isset( $map[ (int) $v ] ) ? $map[ (int) $v ] : $map[1];
	return ay_band( array( ay_w( 'ay-archive', $s ) ), array( '_element_id' => 'ay-archive', 'padding' => ay_box( 10, 24, 110, 24 ), 'padding_mobile' => ay_box( 6, 16, 70, 16 ) ) );
}

/* =========================================================================
 * SINGLE WORK — the whole «صفحهٔ تک‌اثر» template
 * ====================================================================== */
function ay_demo_single( $v = 1 ) {
	$crumbs = ay_w( 'ay-work-field', array( 'field' => 'crumbs', 'tag' => 'p', 'crumb_link' => array( 'url' => ay_page_url( 'collections' ) ) ) );
	$title  = ay_w( 'ay-work-field', array( 'field' => 'title', 'tag' => 'h1' ) );
	$desc   = ay_w( 'ay-work-field', array( 'field' => 'desc' ) );
	$specs  = function ( $cols = 2 ) {
		return ay_w( 'ay-work-specs', array( 'columns' => $cols ) );
	};
	$nav     = ay_w( 'ay-work-nav', array() );
	$gallery = function ( $layout = 'stage', $extra = array() ) {
		return ay_w( 'ay-work-gallery', array_merge( array( 'layout' => $layout ), $extra ) );
	};
	$related = ay_band( array( ay_eyebrow( 'More pieces' ), ay_heading( 'More from this collection', 'h2', 50 ), ay_w( 'ay-related', array() ) ), array( 'padding' => ay_box( 40, 24, 110, 24 ), 'flex_gap' => ay_gap( 18 ) ) );
	$info    = function ( $cols = 2 ) use ( $crumbs, $title, $desc, $specs, $nav ) {
		return array( $crumbs, $title, $desc, $specs( $cols ), $nav );
	};
	$half = function ( $kids, $w = 50 ) {
		return ay_col( $kids, array( 'width' => ay_px( $w, '%' ), 'width_mobile' => ay_px( 100, '%' ), 'flex_gap' => ay_gap( 18 ) ) );
	};
	$top = ay_pad_top();
	switch ( (int) $v ) {
		case 2:
			$main = ay_band( array( ay_row( array( $half( $info(), 48 ), $half( array( $gallery( 'side' ) ), 52 ) ), array( 'flex_gap' => ay_gap( 72 ), 'flex_direction_mobile' => 'column-reverse' ) ) ), $top );
			break;
		case 3:
			$main = ay_band( array( ay_col( array( $crumbs, $title, $desc ), array( 'flex_align_items' => 'center', 'flex_gap' => ay_gap( 16 ) ) ), $gallery( 'strip', array( 'sticky' => '' ) ), $specs( 3 ), $nav ), array_merge( $top, array( 'flex_gap' => ay_gap( 40 ) ) ) );
			$main['elements'][0]['elements'][1]['settings']['align'] = 'center';
			$main['elements'][0]['elements'][0]['settings']['align'] = 'center';
			$main['elements'][0]['elements'][2]['settings']['align'] = 'center';
			break;
		case 4:
			$main = ay_band( array( ay_row( array( $half( array( $gallery( 'grid', array( 'sticky' => '', 'columns' => 2 ) ) ), 58 ), $half( $info( 1 ), 42 ) ), array( 'flex_gap' => ay_gap( 64 ) ) ) ), $top );
			break;
		case 5:
			$main = ay_band( array( ay_col( array( $crumbs, $title ), array( 'flex_gap' => ay_gap( 12 ) ) ), $gallery( 'stage', array( 'sticky' => '', 'aspect' => ay_px( 1.9 ) ) ), ay_row( array( $half( array( $desc, $nav ), 46 ), $half( array( $specs( 2 ) ), 54 ) ), array( 'flex_gap' => ay_gap( 64 ) ) ) ), array_merge( $top, array( 'flex_gap' => ay_gap( 36 ) ) ) );
			break;
		default:
			$main = ay_band( array( ay_row( array( $half( array( $gallery( 'stage' ) ), 52 ), $half( $info(), 48 ) ), array( 'flex_gap' => ay_gap( 72 ) ) ) ), $top );
	}
	return array( $main, $related );
}

/* =========================================================================
 * ABOUT — the whole About page
 * ====================================================================== */
function ay_demo_about( $v = 1 ) {
	$bio = array(
		ay_eyebrow( 'About Me' ),
		ay_heading( 'Arezoo Yousefi', 'h1', 74 ),
		ay_heading( 'Jewelry Designer', 'p', 26, array( 'typography_font_style' => 'italic', 'typography_font_weight' => '400', '__globals__' => array( 'title_color' => ay_g( 'muted' ) ) ) ),
		ay_text( '<p>I specialize in jewelry design, concept development, and visual presentation. My services include original jewelry concepts, bespoke design, collection development, and digital renderings tailored to each project’s creative direction and requirements.</p><p>I collaborate with jewelry brands, manufacturers, and industry professionals on individual designs, exclusive collections, and creative development projects.</p>' ),
	);
	$photo = function ( $rel = 'img/studio/03-line-drawing.webp' ) {
		return ay_w( 'image', array( 'image' => ay_media( $rel ), 'image_size' => 'large', 'image_border_radius' => array( 'unit' => 'px', 'top' => '600', 'right' => '600', 'bottom' => '26', 'left' => '26', 'isLinked' => false ), 'ay_rv' => 'ink' ) );
	};
	$half = function ( $kids, $w = 50, $extra = array() ) {
		return ay_col( $kids, array_merge( array( 'width' => ay_px( $w, '%' ), 'width_mobile' => ay_px( 100, '%' ), 'flex_gap' => ay_gap( 16 ) ), $extra ) );
	};
	$motions = function () {
		$m    = function ( $type, $cap ) {
			return ay_col( array( ay_w( 'ay-motion', array( 'type' => $type, 'caption' => $cap, 'size' => ay_px( 170 ) ) ) ), array( 'width' => ay_px( 22, '%' ), 'width_mobile' => ay_px( 44, '%' ), 'flex_align_items' => 'center', 'ay_rv' => 'up' ) );
		};
		return ay_band( array( ay_eyebrow( 'Digital design' ), ay_heading( 'From a pencil line to a rendered jewel', 'h2', 50 ), ay_row( array( $m( 'sketch', 'Sketch' ), $m( 'facets', 'Stone cuts' ), $m( 'wire', '3D form' ), $m( 'render', 'Rendering' ) ), array( 'flex_gap' => ay_gap( 20 ), 'flex_direction_mobile' => 'row', 'flex_wrap' => 'wrap', 'flex_wrap_mobile' => 'wrap', 'padding' => ay_box( 30, 0, 0, 0 ) ) ) ), array( 'ay_sk' => 'filigree-rings', 'ay_sk_op' => ay_px( 0.06 ), 'ay_sk_x' => ay_px( 92, '%' ), 'ay_sk_size' => ay_px( 520 ) ) );
	};
	$timeline = ay_band( array( ay_eyebrow( 'The Design Process' ), ay_heading( 'From a first conversation to the final design', 'h2', 50 ), ay_w( 'ay-timeline', array() ) ), array( 'flex_gap' => ay_gap( 18 ) ) );
	$strip    = ay_c( array( 'content_width' => 'full', 'padding' => ay_box( 20, 0, 40, 0 ) ), array( ay_w( 'ay-strip', array() ) ) );
	$closing  = ay_closing( 'See the work itself', 'Explore Jewelry', ay_page_url( 'collections' ) );
	$top      = ay_pad_top();
	switch ( (int) $v ) {
		case 2:
			return array( ay_band( array( ay_row( array( $half( array( $photo( 'img/studio/11-at-work.webp' ) ), 42 ), $half( $bio, 58, array( 'flex_justify_content' => 'center' ) ) ), array( 'flex_gap' => ay_gap( 90 ), 'flex_align_items' => 'center' ) ) ), $top ), $timeline, $motions(), $closing );
		case 3:
			$center = ay_col( $bio, array( 'flex_align_items' => 'center', 'flex_gap' => ay_gap( 16 ) ) );
			foreach ( $center['elements'] as $i => $e ) {
				$center['elements'][ $i ]['settings']['align'] = 'center';
			}
			$center['elements'][3]['settings']['_element_width']        = 'initial';
			$center['elements'][3]['settings']['_element_custom_width'] = ay_px( 720 );
			return array( ay_band( array( $center ), $top ), ay_band( array( ay_row( array( $half( array( ay_w( 'ay-compare', array( 'after' => ay_media( 'img/studio/necklace-detail.webp' ) ) ) ), 46 ), $half( array( ay_eyebrow( 'Sketch to jewel' ), ay_heading( 'Every piece is drawn before it is painted', 'h2', 46 ), ay_text( '<p>Digital renderings communicate the design’s form, proportions, materials, and gemstones through detailed visual presentations.</p>' ) ), 54, array( 'flex_justify_content' => 'center' ) ) ), array( 'flex_gap' => ay_gap( 80 ), 'flex_align_items' => 'center' ) ) ) ), $timeline, $closing );
		case 4:
			return array( ay_band( array( ay_row( array( $half( $bio, 58, array( 'flex_justify_content' => 'center' ) ), $half( array( $photo() ), 42 ) ), array( 'flex_gap' => ay_gap( 90 ), 'flex_align_items' => 'center' ) ) ), $top ), ay_band( array( ay_eyebrow( 'The Design Process' ), ay_heading( 'From a first conversation to the final design', 'h2', 50 ), ay_w( 'ay-process', ay_process_settings() ) ), array( 'flex_gap' => ay_gap( 18 ) ) ), $strip, $closing );
		case 5:
			$steps = array();
			foreach ( \Arezoo\Widgets\Timeline::default_steps() as $st ) {
				$steps[] = ay_col( array( ay_heading( $st['title'], 'h3', 26 ), ay_text( '<p>' . $st['text'] . '</p>' ) ), array( 'flex_gap' => ay_gap( 6 ) ) );
			}
			return array( ay_band( array_merge( $bio, array() ), $top ), $strip, ay_band( array( ay_eyebrow( 'The Design Process' ), ay_heading( 'From a first conversation to the final design', 'h2', 50 ), ay_col( $steps, array( 'ay_tl' => 'yes', 'flex_gap' => ay_gap( 44 ), 'padding' => ay_box( 20, 0, 0, 0 ) ) ) ), array( 'flex_gap' => ay_gap( 18 ) ) ), $closing );
		default:
			return array( ay_band( array( ay_row( array( $half( $bio, 58, array( 'flex_justify_content' => 'center' ) ), $half( array( $photo() ), 42 ) ), array( 'flex_gap' => ay_gap( 90 ), 'flex_align_items' => 'center' ) ) ), $top ), $motions(), $timeline, $strip, $closing );
	}
}

/** Settings of the process widget with pictures taken from the Media Library. */
function ay_process_settings( $extra = array() ) {
	$steps = \Arezoo\Widgets\Process::default_steps();
	$files = array( '01-desk-references', '02-paper-sketches', '03-line-drawing', '04-rendering', '05-final-necklace' );
	foreach ( $steps as $i => $st ) {
		$steps[ $i ]['image'] = ay_media( 'img/studio/' . $files[ $i ] . '.webp' );
		$steps[ $i ]['focus'] = 'center';
	}
	return array_merge( array( 'steps' => $steps ), $extra );
}

/* =========================================================================
 * FAQ — replaces the section with CSS ID «ay-faq» on the Contact page
 * ====================================================================== */
function ay_demo_faq( $v = 1 ) {
	$layouts = array_keys( \Arezoo\Widgets\Faq::layouts() );
	$layout  = isset( $layouts[ (int) $v - 1 ] ) ? $layouts[ (int) $v - 1 ] : 'sidebar';
	return ay_band( array( ay_eyebrow( 'FAQ' ), ay_heading( 'Frequently asked questions', 'h2', 50 ), ay_w( 'ay-faq', array( 'layout' => $layout ) ) ), array( '_element_id' => 'ay-faq', 'flex_gap' => ay_gap( 18 ), 'ay_sk' => 4 === (int) $v ? 'floral-set' : '', 'ay_sk_op' => ay_px( 0.07 ), 'ay_sk_x' => ay_px( 88, '%' ), 'ay_sk_y' => ay_px( 30, '%' ) ) );
}

/* =========================================================================
 * HEADER — the whole header template
 * ====================================================================== */
function ay_demo_header( $v = 1 ) {
	$logo = function ( $extra = array() ) {
		return ay_w( 'ay-logo', array_merge( array( '_element_width' => 'auto' ), $extra ) );
	};
	$nav = function ( $extra = array() ) {
		return ay_w( 'ay-nav', array_merge( array( 'hide_mobile' => 'hidden-mobile', '_element_width' => 'auto' ), $extra ) );
	};
	$bar = array(
		'content_width'        => 'full',
		'flex_direction'       => 'row',
		'flex_justify_content' => 'space-between',
		'flex_align_items'     => 'center',
		'flex_gap'             => ay_gap( 20 ),
		'padding'              => ay_box( 8, 26, 8, 18 ),
		'border_radius'        => ay_box( 999 ),
		'ay_float'             => 'top',
		'ay_glass'             => 'yes',
		'ay_glass_op'          => ay_px( 0.62 ),
		'flex_justify_content_mobile' => 'center',
		'html_tag'             => 'div',
	);
	switch ( (int) $v ) {
		case 2:   /* full-width glass bar */
			return array( ay_c( array_merge( $bar, array( 'ay_float_offset' => ay_px( 0 ), 'ay_float_width' => ay_px( 1600 ), 'ay_float_side' => ay_px( 0 ), 'border_radius' => ay_box( 0 ), 'padding' => ay_box( 12, 40, 12, 40 ), 'padding_mobile' => ay_box( 10, 16, 10, 16 ) ) ), array( $logo(), $nav() ) ) );
		case 3:   /* logo above the menu, centred */
			return array( ay_c( array_merge( $bar, array( 'flex_direction' => 'column', 'flex_gap' => ay_gap( 2 ), 'border_radius' => ay_box( 30 ), 'padding' => ay_box( 12, 30, 8, 30 ), 'ay_float_width' => ay_px( 640 ) ) ), array( $logo(), $nav( array( 'justify' => 'center' ) ) ) ) );
		case 4:   /* no glass: logo and menu straight on the page, hides while scrolling down */
			return array( ay_c( array_merge( $bar, array( 'ay_glass' => '', 'ay_float_smart' => 'yes', 'ay_float_width' => ay_px( 1180 ), 'padding' => ay_box( 10, 0, 10, 0 ), 'border_radius' => ay_box( 0 ) ) ), array( $logo(), $nav( array( 'fx' => 'ink' ) ) ) ) );
		case 5:   /* compact pill: mark only */
			return array( ay_c( array_merge( $bar, array( 'ay_float_width' => ay_px( 620 ), 'padding' => ay_box( 6, 26, 6, 16 ) ) ), array( $logo( array( 'name_on' => '' ) ), $nav( array( 'fx' => 'space', 'gap' => ay_px( 26 ) ) ) ) ) );
		default:  /* glass capsule */
			return array( ay_c( $bar, array( $logo(), $nav() ) ) );
	}
}

/* =========================================================================
 * FOOTER — the whole footer template
 * ====================================================================== */
function ay_demo_footer( $v = 1 ) {
	$band  = array( 'title_color' => '', '__globals__' => array( 'title_color' => ay_g( 'bandink' ) ) );
	$logo  = function ( $size = 30, $extra = array() ) {
		return ay_w( 'ay-logo', array_merge( array( 'size' => ay_px( $size ), 'mark_color' => '', '__globals__' => array( 'mark_color' => ay_g( 'bandink' ), 'name_c' => ay_g( 'bandink' ) ), 'name_t_typography' => 'custom', 'name_t_font_family' => 'Cormorant Garamond', 'name_t_font_size' => ay_px( 40 ), 'name_t_font_size_mobile' => ay_px( 28 ), 'name_t_font_weight' => '500' ), $extra ) );
	};
	$line  = ay_text( '<p>Jewelry design, concept development and visual presentation.</p>', array( '__globals__' => array( 'text_color' => ay_g( 'bandmuted' ) ) ) );
	$nav   = function ( $extra = array() ) {
		return ay_w( 'ay-nav', array_merge( array( 'justify' => 'flex-start', '__globals__' => array( 'c' => ay_g( 'bandink' ), 'ch' => ay_g( 'bandink' ) ), '_element_width' => 'auto' ), $extra ) );
	};
	$contact = function ( $layout = 'inline', $extra = array() ) {
		return ay_w( 'ay-contact', array_merge( array( 'layout' => $layout, '__globals__' => array( 'val_c' => ay_g( 'bandink' ), 'icon_c' => ay_g( 'bandink' ), 'kind_c' => ay_g( 'bandmuted' ), 'line' => ay_g( 'bandmuted' ) ), '_element_width' => 'auto' ), $extra ) );
	};
	$copy = ay_text( '<p>© ' . gmdate( 'Y' ) . ' Arezoo Yousefi. All designs are original work.</p>', array( 'typography_font_size' => ay_px( 13 ), '__globals__' => array( 'text_color' => ay_g( 'bandmuted' ) ) ) );
	$dock = ay_w( 'ay-dock', array() );
	$card = function ( $kids, $extra = array() ) {
		return ay_c( array( 'content_width' => 'boxed', 'boxed_width' => ay_px( 1180 ), 'padding' => ay_box( 0, 24, 104, 24 ), 'padding_mobile' => ay_box( 0, 16, 96, 16 ) ), array(
			ay_c( array_merge( array( 'content_width' => 'full', 'flex_gap' => ay_gap( 34 ), 'padding' => ay_box( 60 ), 'padding_mobile' => ay_box( 30 ), 'border_radius' => ay_box( 34 ), 'background_background' => 'classic', '__globals__' => array( 'background_color' => ay_g( 'band' ) ) ), $extra ), $kids, true ),
		) );
	};
	$rule = array( 'border_border' => 'solid', 'border_width' => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ), '__globals__' => array( 'border_color' => ay_g( 'bandmuted' ) ), 'padding' => ay_box( 26, 0, 0, 0 ) );
	$row  = function ( $kids, $extra = array() ) {
		return ay_row( $kids, array_merge( array( 'flex_justify_content' => 'space-between', 'flex_align_items' => 'center', 'flex_gap' => ay_gap( 28 ), 'flex_wrap' => 'wrap' ), $extra ) );
	};
	switch ( (int) $v ) {
		case 2:   /* one slim line */
			return array( $card( array( $row( array( $logo( 26, array( 'name_t_font_size' => ay_px( 26 ) ) ), $contact(), $copy ) ) ), array( 'padding' => ay_box( 28, 40, 28, 40 ), 'border_radius' => ay_box( 999 ), 'border_radius_mobile' => ay_box( 30 ) ) ), $dock );
		case 3:   /* very large wordmark */
			return array( $card( array( ay_heading( 'Arezoo Yousefi', 'p', 150, array( '__globals__' => array( 'title_color' => ay_g( 'bandink' ) ), 'typography_font_size_tablet' => ay_px( 96 ), 'typography_font_size_mobile' => ay_px( 52 ), 'typography_line_height' => ay_px( 0.95, 'em' ), 'typography_font_style' => 'italic', 'typography_font_weight' => '400' ) ), $row( array( $nav(), $contact() ), $rule ), $copy ) ), $dock );
		case 4:   /* three columns */
			$col = function ( $kids, $w ) {
				return ay_col( $kids, array( 'width' => ay_px( $w, '%' ), 'width_mobile' => ay_px( 100, '%' ), 'flex_gap' => ay_gap( 14 ) ) );
			};
			return array( $card( array( ay_row( array( $col( array( $logo(), $line ), 40 ), $col( array( ay_eyebrow( 'Pages', array( '__globals__' => array( 'title_color' => ay_g( 'bandmuted' ) ) ) ), $nav( array( 'dir' => 'column', 'gap' => ay_px( 4 ) ) ) ), 22 ), $col( array( ay_eyebrow( 'Contact', array( '__globals__' => array( 'title_color' => ay_g( 'bandmuted' ) ) ) ), $contact( 'rows', array( 'actions' => '', 'kinds' => '', '_element_width' => '' ) ) ), 38 ) ), array( 'flex_gap' => ay_gap( 48 ) ) ), ay_col( array( $copy ), $rule ) ) ), $dock );
		case 5:   /* centred */
			$kids = array( $logo( 44, array( 'dir' => 'column', 'align' => 'center' ) ), $nav( array( 'justify' => 'center', '_element_width' => '' ) ), $contact( 'inline', array( '_element_width' => 'auto' ) ), $copy );
			$kids[3]['settings']['align'] = 'center';
			return array( $card( $kids, array( 'flex_align_items' => 'center', 'flex_gap' => ay_gap( 22 ) ) ), $dock );
		default:  /* dark card + bottom bar */
			return array( $card( array( ay_col( array( $logo(), $line ), array( 'flex_gap' => ay_gap( 12 ) ) ), $row( array( $nav(), $contact() ), $rule ), $copy ) ), $dock );
	}
}

/* =========================================================================
 * Registry
 * ====================================================================== */
/** [ area => [ label, target, note, demos => [ n => [ title, desc, mini ] ] ] ] */
function ay_demo_areas() {
	return array(
		'hero'    => array( 'label' => 'هیرو', 'target' => 'بخش هیروی صفحهٔ اصلی', 'demos' => array(
			1 => array( 'باغ شب', 'عکس شما با ناحیه‌های متحرک، متن پایین چپ', 'hero-1' ),
			2 => array( 'آبرنگ روشن', 'هیروی لایه‌ای: شاخه و شکوفه، مرغ مگس‌خوار، گوشواره‌های آویز و نقش‌های مدادی', 'hero-2' ),
			3 => array( 'ویدیوی شما', 'ویدیوی آپلودی با عکس جایگزین، متن وسط پایین', 'hero-3' ),
			4 => array( 'اسلایدشو', 'چند عکس با محو شدن نرم و زوم آرام، متن وسط', 'hero-4' ),
			5 => array( 'فقط تصویر', 'عکس کامل بدون سایه، فقط یک دکمه', 'hero-5' ),
		) ),
		'archive' => array( 'label' => 'آرشیو کالکشن‌ها', 'target' => 'بخش آرشیو صفحهٔ Collections', 'demos' => array(
			1 => array( 'شبکهٔ منظم', 'سه ستون، موشن «خط از چپ»', 'ar-1' ),
			2 => array( 'شبکهٔ ناهم‌ارتفاع', 'قاب‌های بلند و کوتاه، موشن «خط موج‌دار»', 'ar-2' ),
			3 => array( 'فهرست ردیفی', 'هر اثر یک ردیف، موشن «باز شدن حروف»', 'ar-3' ),
			4 => array( 'مجله‌ای', 'یک اثر بزرگ کنار آثار کوچک، موشن «عبور نور»', 'ar-4' ),
			5 => array( 'نوار لغزنده', 'نوار افقی قابل کشیدن، موشن «غلتیدن واژه»', 'ar-5' ),
		) ),
		'single'  => array( 'label' => 'صفحهٔ تک‌اثر', 'target' => 'قالب صفحهٔ تک‌اثر (همهٔ آثار)', 'demos' => array(
			1 => array( 'گالری چپ', 'گالری ثابت چپ، مشخصات راست', 'sg-1' ),
			2 => array( 'گالری راست', 'مشخصات چپ، گالری با تصاویر کوچک کناری', 'sg-2' ),
			3 => array( 'وسط‌چین', 'عنوان بالا، نوار تصاویر، مشخصات سه‌ستونه', 'sg-3' ),
			4 => array( 'شبکهٔ تصاویر', 'همهٔ تصاویر در شبکه، مشخصات تک‌ستونه', 'sg-4' ),
			5 => array( 'تصویر عریض', 'یک قاب عریض بالا، توضیح و مشخصات زیر آن', 'sg-5' ),
		) ),
		'about'   => array( 'label' => 'دربارهٔ من', 'target' => 'کل صفحهٔ About Me', 'demos' => array(
			1 => array( 'متن و عکس', 'متن چپ، عکس راست، موشن‌های طراحی، خط زمانی و نوار عکس', 'ab-1' ),
			2 => array( 'عکس و متن', 'عکس چپ، متن راست، خط زمانی و موشن‌ها', 'ab-2' ),
			3 => array( 'از اسکچ تا جواهر', 'معرفی وسط‌چین و مقایسهٔ اسکچ با طرح نهایی', 'ab-3' ),
			4 => array( 'فرایند اسکرولی', 'معرفی و فرایند طراحی دیجیتال با قاب تبلت', 'ab-4' ),
			5 => array( 'ساده', 'متن، نوار عکس و خط زمانی ساخته‌شده از کانتینرها', 'ab-5' ),
		) ),
		'faq'     => array( 'label' => 'سوالات متداول', 'target' => 'بخش سوالات صفحهٔ Contact', 'demos' => array(
			1 => array( 'آکاردئون ساده', 'همهٔ گروه‌ها زیر هم', 'fq-1' ),
			2 => array( 'فهرست موضوع', 'موضوع‌ها کنار، پاسخ‌ها روبه‌رو', 'fq-2' ),
			3 => array( 'دو ستونه', 'گروه‌ها در دو ستون', 'fq-3' ),
			4 => array( 'کارت‌های شیشه‌ای', 'هر گروه یک کارت شیشه‌ای', 'fq-4' ),
			5 => array( 'تب‌های افقی', 'موضوع‌ها به‌صورت تب', 'fq-5' ),
		) ),
		'header'  => array( 'label' => 'هدر', 'target' => 'قالب هدر (همهٔ صفحات)', 'demos' => array(
			1 => array( 'کپسول شیشه‌ای', 'لوگو چپ، منو راست، شناور وسط بالا', 'hd-1' ),
			2 => array( 'نوار تمام‌عرض', 'نوار شیشه‌ای چسبیده به بالا', 'hd-2' ),
			3 => array( 'لوگو بالای منو', 'لوگو و منو وسط‌چین در یک قاب', 'hd-3' ),
			4 => array( 'بدون شیشه', 'لوگو و منو مستقیم روی صفحه، هنگام اسکرول پنهان می‌شود', 'hd-4' ),
			5 => array( 'قرص کوچک', 'فقط نشان و منو در کپسول باریک', 'hd-5' ),
		) ),
		'footer'  => array( 'label' => 'فوتر', 'target' => 'قالب فوتر (همهٔ صفحات)', 'demos' => array(
			1 => array( 'کارت تیره', 'کارت شناور تیره + نوار پایین', 'ft-1' ),
			2 => array( 'یک خط', 'لوگو، تماس و کپی‌رایت در یک کپسول', 'ft-2' ),
			3 => array( 'نام بزرگ', 'نام برند خیلی بزرگ بالای لینک‌ها', 'ft-3' ),
			4 => array( 'سه ستون', 'برند، صفحات و تماس در سه ستون', 'ft-4' ),
			5 => array( 'وسط‌چین', 'همه‌چیز وسط‌چین زیر نشان', 'ft-5' ),
		) ),
	);
}

/** Build the Elementor elements of one demo. Always a list of top-level elements. */
function ay_demo_elements( $area, $n ) {
	$fn = 'ay_demo_' . $area;
	if ( ! function_exists( $fn ) ) {
		return array();
	}
	$out = call_user_func( $fn, (int) $n );
	return isset( $out['elType'] ) ? array( $out ) : $out;
}

/** Where a demo lands: [ post id, CSS ID of the section to replace or '' for the whole layout, document type ]. */
function ay_demo_target( $area ) {
	$page = function ( $slug ) {
		$id = (int) get_option( 'arezoo_page_' . $slug );
		return ( $id && 'page' === get_post_type( $id ) ) ? $id : 0;
	};
	switch ( $area ) {
		case 'hero':
			return array( $page( 'home' ), 'ay-hero', 'wp-page' );
		case 'archive':
			return array( $page( 'collections' ), 'ay-archive', 'wp-page' );
		case 'about':
			return array( $page( 'about' ), '', 'wp-page' );
		case 'faq':
			return array( $page( 'contact' ), 'ay-faq', 'wp-page' );
		case 'single':
			return array( ay_part_id( 'single' ), '', 'page' );
		case 'header':
			return array( ay_part_id( 'header' ), '', 'section' );
		case 'footer':
			return array( ay_part_id( 'footer' ), '', 'section' );
	}
	return array( 0, '', 'wp-page' );
}

function ay_demo_state() {
	return (array) get_option( 'arezoo_demos', array() );
}

/**
 * Apply a demo for real: the destination layout is rewritten with the demo's Elementor
 * elements (after a backup), so the editor and the live site both show it.
 *
 * @return true|WP_Error
 */
function ay_apply_demo( $area, $n ) {
	$areas = ay_demo_areas();
	$n     = (int) $n;
	if ( ! isset( $areas[ $area ]['demos'][ $n ] ) ) {
		return new WP_Error( 'ay_demo', 'این دمو پیدا نشد.' );
	}
	if ( ! ay_elementor_ready() || ! ay_load_widgets() ) {
		return new WP_Error( 'ay_demo', 'المنتور فعال نیست.' );
	}
	list( $post_id, $css_id, $type ) = ay_demo_target( $area );
	if ( ! $post_id ) {
		return new WP_Error( 'ay_demo', 'مقصد این دمو هنوز ساخته نشده است. ابتدا از «آرزو ← راه‌اندازی» سایت را بسازید.' );
	}
	$new = ay_demo_elements( $area, $n );
	if ( ! $new ) {
		return new WP_Error( 'ay_demo', 'این دمو محتوایی ندارد.' );
	}
	$current = ay_get_layout( $post_id );
	if ( $current ) {
		ay_backup_layout( $post_id, $area . ':' . $n );
	}
	if ( '' === $css_id ) {
		$layout = $new;
	} else {
		$i = ay_find_section( $current, $css_id );
		if ( $i >= 0 ) {
			array_splice( $current, $i, 1, $new );
			$layout = $current;
		} else {
			$layout = 'ay-hero' === $css_id ? array_merge( $new, $current ) : array_merge( $current, $new );
		}
	}
	ay_save_layout( $post_id, $layout, $type );
	$state          = ay_demo_state();
	$state[ $area ] = array( 'n' => $n, 'hash' => md5( (string) get_post_meta( $post_id, '_elementor_data', true ) ), 'time' => time() );
	update_option( 'arezoo_demos', $state );
	\Elementor\Plugin::$instance->files_manager->clear_cache();
	return true;
}

/** Undo the last «اعمال» for an area (puts the previous layout back). */
function ay_undo_demo( $area ) {
	list( $post_id ) = ay_demo_target( $area );
	if ( ! $post_id || ! ay_restore_layout( $post_id ) ) {
		return new WP_Error( 'ay_demo', 'نسخهٔ قبلی‌ای برای بازگردانی وجود ندارد.' );
	}
	$state = ay_demo_state();
	unset( $state[ $area ] );
	update_option( 'arezoo_demos', $state );
	if ( ay_elementor_ready() ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	return true;
}

/** Was the applied demo edited by hand since «اعمال»? */
function ay_demo_edited( $area ) {
	$state = ay_demo_state();
	if ( empty( $state[ $area ]['hash'] ) ) {
		return false;
	}
	list( $post_id ) = ay_demo_target( $area );
	return $post_id && md5( (string) get_post_meta( $post_id, '_elementor_data', true ) ) !== $state[ $area ]['hash'];
}
