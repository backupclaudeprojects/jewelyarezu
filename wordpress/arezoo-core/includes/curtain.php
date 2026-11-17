<?php
/**
 * Page curtain: a frosted-glass transition around normal page loads, plus the site-wide
 * effects (cursor light, reading bar, grain, click sparkles, magnetic buttons…).
 * Both are set in «آرزو ← پرده و افکت‌ها»; everything is optional.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------------ settings */
function ay_curtain_styles() {
	return array(
		'frost'    => array( 'مات شدن صفحه', 'پرده‌ای کشیده نمی‌شود؛ خودِ صفحه شیشه‌ای مات می‌شود، لوگو واضح می‌آید و می‌رود و صفحهٔ بعد از مات درمی‌آید.' ),
		'mist'     => array( 'مه رنگی', 'مثل «مات شدن صفحه» با هاله‌های رنگی آرام پشت شیشه.' ),
		'rise'     => array( 'شیشه از پایین', 'یک صفحهٔ شیشه‌ای از پایین بالا می‌آید و از بالا بیرون می‌رود.' ),
		'fall'     => array( 'شیشه از بالا', 'صفحهٔ شیشه‌ای از بالا پایین می‌آید و از پایین بیرون می‌رود.' ),
		'side'     => array( 'شیشهٔ کشویی', 'صفحهٔ شیشه‌ای از چپ می‌آید و از راست بیرون می‌رود.' ),
		'doors'    => array( 'دو لنگهٔ شیشه‌ای', 'دو لنگه از چپ و راست بسته و دوباره باز می‌شوند.' ),
		'gate'     => array( 'بالا و پایین', 'دو نیمهٔ شیشه‌ای از بالا و پایین به هم می‌رسند.' ),
		'blinds'   => array( 'کرکرهٔ شیشه‌ای', 'شش نوار عمودی شیشه‌ای یکی پس از دیگری.' ),
		'stairs'   => array( 'پله‌ای', 'شش نوار افقی شیشه‌ای پله‌پله.' ),
		'iris'     => array( 'دایره از جای کلیک', 'دایره‌ای شیشه‌ای از جای کلیک باز می‌شود و در وسط جمع می‌شود.' ),
		'diagonal' => array( 'اریب', 'شیشه به‌صورت اریب از گوشه عبور می‌کند.' ),
		'solid'    => array( 'محو شدن ساده', 'بدون تاری؛ صفحه در رنگ پرده محو و دوباره پیدا می‌شود.' ),
	);
}

function ay_curtain_defaults() {
	return array(
		'on'         => 1,
		'style'      => 'frost',
		'tint'       => '',      // empty = page background of the colour theme
		'tint_op'    => 38,      // percent
		'blur'       => 26,      // px
		'sat'        => 130,     // percent
		'bright'     => 100,     // percent
		'dur'        => 750,     // ms, one way
		'hold'       => 420,     // ms the logo stays at least
		'ease'       => 'lux',
		'edge'       => 1,       // light line on the glass edge
		'logo'       => 1,
		'logo_id'    => 0,       // own picture; 0 = the site mark
		'logo_size'  => 110,
		'logo_color' => '',
		'logo_fx'    => 'breathe',
		'name'       => 1,
		'name_text'  => '',
		'line'       => 1,
		'line_color' => '',
		'first'      => 0,       // also when the visitor first opens the site
		'mobile'     => 1,
	);
}

function ay_curtain_eases() {
	return array(
		'lux'     => array( 'لوکس (آرام در شروع و پایان)', 'cubic-bezier(.7,0,.2,1)' ),
		'soft'    => array( 'فرود نرم', 'cubic-bezier(.16,1,.3,1)' ),
		'natural' => array( 'طبیعی', 'cubic-bezier(.22,.61,.36,1)' ),
		'linear'  => array( 'یکنواخت', 'linear' ),
	);
}

function ay_curtain_settings() {
	return wp_parse_args( (array) get_option( 'arezoo_curtain', array() ), ay_curtain_defaults() );
}

function ay_fx_defaults() {
	return array(
		'cursor'       => 0,
		'cursor_color' => '',
		'cursor_size'  => 420,
		'cursor_op'    => 16,
		'progress'     => 0,
		'progress_c'   => '',
		'progress_h'   => 2,
		'grain'        => 0,
		'grain_op'     => 6,
		'sparkle'      => 0,
		'sparkle_n'    => 6,
		'sparkle_c'    => '',
		'magnet'       => 0,
		'magnet_k'     => 30,
		'shine'        => 0,
		'selection'    => '',
		'scrollbar'    => '',
	);
}

function ay_fx_settings() {
	return wp_parse_args( (array) get_option( 'arezoo_fx', array() ), ay_fx_defaults() );
}

/* ---------------------------------------------------------------- front end */
function ay_curtain_active() {
	if ( is_admin() || is_feed() || is_embed() || wp_doing_ajax() ) {
		return false;
	}
	if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['preview_id'] ) || isset( $_GET['customize_changeset_uuid'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}
	$c = ay_curtain_settings();
	return ! empty( $c['on'] );
}

/** CSS variables + attributes of the curtain element (shared by the site and the dashboard preview). */
function ay_curtain_attrs( $c ) {
	$eases = ay_curtain_eases();
	$ease  = isset( $eases[ $c['ease'] ] ) ? $eases[ $c['ease'] ][1] : $eases['lux'][1];
	$style = isset( ay_curtain_styles()[ $c['style'] ] ) ? $c['style'] : 'frost';
	$vars  = array(
		'--cv-op'    => max( 0, min( 100, (float) $c['tint_op'] ) ) / 100,
		'--cv-blur'  => max( 0, min( 80, (float) $c['blur'] ) ) . 'px',
		'--cv-sat'   => max( 0, min( 300, (float) $c['sat'] ) ) . '%',
		'--cv-bri'   => max( 30, min( 200, (float) $c['bright'] ) ) . '%',
		'--cv-dur'   => max( 200, min( 3000, (int) $c['dur'] ) ) . 'ms',
		'--cv-ease'  => $ease,
		'--cv-logo'  => max( 30, min( 480, (int) $c['logo_size'] ) ) . 'px',
	);
	if ( $c['tint'] ) {
		$vars['--cv-tint'] = ay_hex( $c['tint'] );
	}
	if ( $c['logo_color'] ) {
		$vars['--cv-ink'] = ay_hex( $c['logo_color'] );
	}
	if ( $c['line_color'] ) {
		$vars['--cv-line'] = ay_hex( $c['line_color'] );
	}
	$css = '';
	foreach ( $vars as $k => $v ) {
		$css .= $k . ':' . $v . ';';
	}
	$fx  = in_array( $c['logo_fx'], array( 'none', 'breathe', 'spin', 'shine', 'draw' ), true ) ? $c['logo_fx'] : 'breathe';
	$cls = 'ay-cv is-idle ay-cv-fx-' . $fx . ( empty( $c['edge'] ) ? '' : ' ay-cv-edge' );
	return array( $cls, $style, $css );
}

function ay_curtain_inner( $c ) {
	$out = '';
	for ( $i = 0; $i < 6; $i++ ) {
		$out .= '<i class="ay-cv-p" style="--i:' . $i . '"></i>';
	}
	$out .= '<div class="ay-cv-c">';
	if ( ! empty( $c['logo'] ) ) {
		$src = $c['logo_id'] ? wp_get_attachment_image_url( absint( $c['logo_id'] ), 'medium' ) : '';
		$out .= $src ? '<img class="ay-cv-logo ay-cv-img" src="' . esc_url( $src ) . '" alt="" decoding="async">' : '<span class="ay-cv-logo ay-cv-mark"></span>';
	}
	if ( ! empty( $c['name'] ) ) {
		$name = '' !== trim( (string) $c['name_text'] ) ? $c['name_text'] : ay_opt( 'brand' );
		$out .= '<span class="ay-cv-name">' . esc_html( $name ) . '</span>';
	}
	if ( ! empty( $c['line'] ) ) {
		$out .= '<i class="ay-cv-line"></i>';
	}
	return $out . '</div>';
}

/* Arriving from a curtain navigation: start covered before the first paint, with a safety timer. */
add_action( 'wp_head', function () {
	if ( ! ay_curtain_active() ) {
		return;
	}
	$c = ay_curtain_settings();
	echo "<script>(function(){try{var h=document.documentElement,t=+sessionStorage.getItem('ay-cv')||0,f=" . ( empty( $c['first'] ) ? '0' : '1' ) . ",m=" . ( empty( $c['mobile'] ) ? '0' : '1' ) . ";sessionStorage.removeItem('ay-cv');if(window.matchMedia&&(matchMedia('(prefers-reduced-motion: reduce)').matches||(!m&&matchMedia('(max-width: 767px)').matches)))return;if((t&&Date.now()-t<9000)||(f&&!sessionStorage.getItem('ay-cv-seen'))){h.classList.add('ay-cv-arrive');setTimeout(function(){h.classList.remove('ay-cv-arrive')},4500)}sessionStorage.setItem('ay-cv-seen','1')}catch(e){}})();</script>\n";
}, 1 );

add_action( 'wp_body_open', function () {
	if ( ! ay_curtain_active() ) {
		return;
	}
	$c = ay_curtain_settings();
	list( $cls, $style, $css ) = ay_curtain_attrs( $c );
	echo '<div class="' . esc_attr( $cls ) . '" data-style="' . esc_attr( $style ) . '" style="' . esc_attr( $css ) . '" aria-hidden="true">' . ay_curtain_inner( $c ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 1 );

add_action( 'wp_enqueue_scripts', function () {
	$fx   = ay_fx_settings();
	$any  = false;
	$conf = array();
	foreach ( array( 'cursor', 'progress', 'grain', 'sparkle', 'magnet', 'shine' ) as $k ) {
		if ( ! empty( $fx[ $k ] ) ) {
			$any = true;
		}
	}
	$editor = isset( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ay_curtain_active() ) {
		$c = ay_curtain_settings();
		wp_enqueue_style( 'arezoo-curtain', ay_asset( 'css/curtain.css' ), array(), AY_VERSION );
		$conf['curtain'] = array( 'dur' => max( 200, min( 3000, (int) $c['dur'] ) ), 'hold' => max( 0, min( 4000, (int) $c['hold'] ) ), 'mobile' => ! empty( $c['mobile'] ) );
	}
	if ( $any && ! $editor ) {
		wp_enqueue_style( 'arezoo-curtain', ay_asset( 'css/curtain.css' ), array(), AY_VERSION );
		$conf['fx'] = array(
			'cursor'   => ! empty( $fx['cursor'] ) ? array( 'c' => $fx['cursor_color'] ? ay_hex( $fx['cursor_color'] ) : '', 's' => (int) $fx['cursor_size'], 'o' => (float) $fx['cursor_op'] / 100 ) : null,
			'progress' => ! empty( $fx['progress'] ) ? array( 'c' => $fx['progress_c'] ? ay_hex( $fx['progress_c'] ) : '', 'h' => (float) $fx['progress_h'] ) : null,
			'grain'    => ! empty( $fx['grain'] ) ? (float) $fx['grain_op'] / 100 : 0,
			'sparkle'  => ! empty( $fx['sparkle'] ) ? array( 'n' => max( 1, min( 24, (int) $fx['sparkle_n'] ) ), 'c' => $fx['sparkle_c'] ? ay_hex( $fx['sparkle_c'] ) : '' ) : null,
			'magnet'   => ! empty( $fx['magnet'] ) ? max( 0, min( 100, (float) $fx['magnet_k'] ) ) / 100 : 0,
			'shine'    => ! empty( $fx['shine'] ),
		);
	}
	if ( $conf ) {
		wp_enqueue_script( 'arezoo-curtain', ay_asset( 'js/curtain.js' ), array(), AY_VERSION, true );
		wp_localize_script( 'arezoo-curtain', 'AY_CURTAIN', $conf );
	}
	$css = '';
	if ( $fx['selection'] ) {
		$css .= '::selection{background:' . ay_hex( $fx['selection'] ) . ';color:var(--ay-bg,#fff)}';
	}
	if ( $fx['scrollbar'] ) {
		$css .= 'html{scrollbar-color:' . ay_hex( $fx['scrollbar'] ) . ' transparent}';
	}
	if ( $css ) {
		wp_register_style( 'arezoo-fx-inline', false, array(), AY_VERSION );
		wp_enqueue_style( 'arezoo-fx-inline' );
		wp_add_inline_style( 'arezoo-fx-inline', $css );
	}
}, 6 );

/* -------------------------------------------------------------------- admin */
add_action( 'admin_menu', function () {
	add_submenu_page( 'arezoo', 'پرده و افکت‌ها', 'پرده و افکت‌ها', 'manage_options', 'arezoo-curtain', 'ay_page_curtain' );
}, 10 );

add_action( 'admin_enqueue_scripts', function () {
	if ( isset( $_GET['page'] ) && 'arezoo-curtain' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_enqueue_style( 'arezoo-curtain', ay_asset( 'css/curtain.css' ), array(), AY_VERSION );
	}
} );

function ay_curtain_save() {
	$tab = isset( $_GET['tab'] ) && 'fx' === $_GET['tab'] ? 'fx' : 'curtain'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_POST['ay_curtain_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ay_curtain_nonce'] ) ), 'ay_curtain' ) || ! current_user_can( 'manage_options' ) ) {
		return false;
	}
	$num = function ( $k, $min, $max, $d ) {
		return isset( $_POST[ $k ] ) && is_numeric( $_POST[ $k ] ) ? max( $min, min( $max, (float) $_POST[ $k ] ) ) : $d; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	};
	$col = function ( $k ) {
		return ! empty( $_POST[ $k ] ) ? ay_hex( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ), '' ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	};
	$on = function ( $k ) {
		return empty( $_POST[ $k ] ) ? 0 : 1; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	};
	if ( 'curtain' === $tab ) {
		$d = ay_curtain_defaults();
		$style = isset( $_POST['style'] ) ? sanitize_key( wp_unslash( $_POST['style'] ) ) : 'frost';
		$ease  = isset( $_POST['ease'] ) ? sanitize_key( wp_unslash( $_POST['ease'] ) ) : 'lux';
		$lfx   = isset( $_POST['logo_fx'] ) ? sanitize_key( wp_unslash( $_POST['logo_fx'] ) ) : 'breathe';
		update_option( 'arezoo_curtain', array(
			'on'         => $on( 'on' ),
			'style'      => isset( ay_curtain_styles()[ $style ] ) ? $style : 'frost',
			'tint'       => $col( 'tint' ),
			'tint_op'    => $num( 'tint_op', 0, 100, $d['tint_op'] ),
			'blur'       => $num( 'blur', 0, 80, $d['blur'] ),
			'sat'        => $num( 'sat', 0, 300, $d['sat'] ),
			'bright'     => $num( 'bright', 30, 200, $d['bright'] ),
			'dur'        => $num( 'dur', 200, 3000, $d['dur'] ),
			'hold'       => $num( 'hold', 0, 4000, $d['hold'] ),
			'ease'       => isset( ay_curtain_eases()[ $ease ] ) ? $ease : 'lux',
			'edge'       => $on( 'edge' ),
			'logo'       => $on( 'logo' ),
			'logo_id'    => isset( $_POST['logo_id'] ) ? absint( $_POST['logo_id'] ) : 0,
			'logo_size'  => $num( 'logo_size', 30, 480, $d['logo_size'] ),
			'logo_color' => $col( 'logo_color' ),
			'logo_fx'    => in_array( $lfx, array( 'none', 'breathe', 'spin', 'shine', 'draw' ), true ) ? $lfx : 'breathe',
			'name'       => $on( 'name' ),
			'name_text'  => isset( $_POST['name_text'] ) ? sanitize_text_field( wp_unslash( $_POST['name_text'] ) ) : '',
			'line'       => $on( 'line' ),
			'line_color' => $col( 'line_color' ),
			'first'      => $on( 'first' ),
			'mobile'     => $on( 'mobile' ),
		) );
	} else {
		$d = ay_fx_defaults();
		update_option( 'arezoo_fx', array(
			'cursor'       => $on( 'cursor' ),
			'cursor_color' => $col( 'cursor_color' ),
			'cursor_size'  => $num( 'cursor_size', 80, 1200, $d['cursor_size'] ),
			'cursor_op'    => $num( 'cursor_op', 0, 100, $d['cursor_op'] ),
			'progress'     => $on( 'progress' ),
			'progress_c'   => $col( 'progress_c' ),
			'progress_h'   => $num( 'progress_h', 1, 12, $d['progress_h'] ),
			'grain'        => $on( 'grain' ),
			'grain_op'     => $num( 'grain_op', 0, 40, $d['grain_op'] ),
			'sparkle'      => $on( 'sparkle' ),
			'sparkle_n'    => $num( 'sparkle_n', 1, 24, $d['sparkle_n'] ),
			'sparkle_c'    => $col( 'sparkle_c' ),
			'magnet'       => $on( 'magnet' ),
			'magnet_k'     => $num( 'magnet_k', 0, 100, $d['magnet_k'] ),
			'shine'        => $on( 'shine' ),
			'selection'    => $col( 'selection' ),
			'scrollbar'    => $col( 'scrollbar' ),
		) );
	}
	return true;
}

function ay_page_curtain() {
	$tab = isset( $_GET['tab'] ) && 'fx' === $_GET['tab'] ? 'fx' : 'curtain'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ay_curtain_save() ) {
		echo '<div class="notice notice-success is-dismissible" dir="rtl"><p>تنظیمات ذخیره شد.</p></div>';
	}
	ay_admin_head( 'پرده و افکت‌ها', 'پردهٔ شیشه‌ای بین صفحه‌ها و افکت‌های کل سایت. همه اختیاری‌اند.' );
	ay_admin_tabs( array( 'curtain' => 'پردهٔ بین صفحات', 'fx' => 'افکت‌های کل سایت' ), $tab, 'arezoo-curtain' );
	echo '<form method="post" class="ay-form" action="' . esc_url( admin_url( 'admin.php?page=arezoo-curtain&tab=' . $tab ) ) . '">';
	wp_nonce_field( 'ay_curtain', 'ay_curtain_nonce' );
	$check = function ( $id, $on, $title, $help = '' ) {
		echo '<label class="ay-check"><input type="checkbox" name="' . esc_attr( $id ) . '" value="1" ' . checked( ! empty( $on ), true, false ) . '> <span><strong>' . esc_html( $title ) . '</strong>' . ( $help ? '<br>' . esc_html( $help ) : '' ) . '</span></label>';
	};
	$range = function ( $id, $label, $value, $min, $max, $step, $unit, $help = '' ) {
		echo '<label class="ay-f ay-range" for="ay_' . esc_attr( $id ) . '"><span>' . esc_html( $label ) . ' <output>' . esc_html( $value . $unit ) . '</output></span><input type="range" id="ay_' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" data-unit="' . esc_attr( $unit ) . '">' . ( $help ? '<em>' . esc_html( $help ) . '</em>' : '' ) . '</label>';
	};
	$color = function ( $id, $label, $value, $help = '' ) {
		echo '<div class="ay-f"><label for="ay_' . esc_attr( $id ) . '"><span>' . esc_html( $label ) . '</span></label><input type="text" id="ay_' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" dir="ltr" class="ay-color">' . ( $help ? '<em>' . esc_html( $help ) . '</em>' : '' ) . '</div>';
	};
	$select = function ( $id, $label, $value, $options ) {
		echo '<label class="ay-f" for="ay_' . esc_attr( $id ) . '"><span>' . esc_html( $label ) . '</span><select id="ay_' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">';
		foreach ( $options as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $value, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select></label>';
	};
	if ( 'curtain' === $tab ) {
		$c = ay_curtain_settings();
		list( $cls, $style, $css ) = ay_curtain_attrs( $c );
		echo '<section class="ay-card"><h2>' . ay_icon( 'image', 22 ) . '<span>پیش‌نمایش زنده</span></h2><p class="ay-help">هر تغییری بدهید همین‌جا پخش می‌شود. رنگ‌های واقعی سایت از تم رنگی فعال می‌آیند.</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="ay-cv-stage" id="ayCvStage" dir="ltr"><div class="ay-cv-page" data-n="1"><b>Arezoo Yousefi</b><span>Every Jewel Begins as a Line</span></div><div class="ay-cv-page ay-cv-page2" data-n="2" hidden><b>Collections</b><span>Browse every piece</span></div>';
		echo '<div class="' . esc_attr( $cls ) . '" data-style="' . esc_attr( $style ) . '" style="' . esc_attr( $css ) . '" aria-hidden="true">' . ay_curtain_inner( array_merge( $c, array( 'logo' => 1, 'name' => 1, 'line' => 1 ) ) ) . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<p><button type="button" class="button" id="ayCvPlay">پخش دوباره</button></p></section>';
		echo '<section class="ay-card"><h2>' . ay_icon( 'gem', 22 ) . '<span>دموهای پرده</span></h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$check( 'on', $c['on'], 'پردهٔ بین صفحات فعال باشد', 'با کلیک روی لینک‌های داخلی سایت پخش می‌شود. در ویرایشگر المنتور و برای کسانی که «کاهش حرکت» را در دستگاهشان روشن کرده‌اند پخش نمی‌شود.' );
		echo '<div class="ay-cv-demos">';
		foreach ( ay_curtain_styles() as $k => $v ) {
			echo '<label class="ay-cv-demo"><input type="radio" name="style" value="' . esc_attr( $k ) . '" ' . checked( $style, $k, false ) . '><span class="ay-cv-demo-box"><i class="ay-cv-mini ay-cv-mini-' . esc_attr( $k ) . '"></i><strong>' . esc_html( $v[0] ) . '</strong><em>' . esc_html( $v[1] ) . '</em></span></label>';
		}
		echo '</div></section><section class="ay-card"><h2>' . ay_icon( 'tool', 22 ) . '<span>شیشه</span></h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$color( 'tint', 'رنگ شیشهٔ مات', $c['tint'], 'خالی = رنگ زمینهٔ تم فعال' );
		$range( 'tint_op', 'پوشش رنگ شیشه', $c['tint_op'], 0, 100, 1, '٪', 'صفر = فقط تاری، بدون رنگ. ۱۰۰ = کاملاً پوشیده' );
		$range( 'blur', 'شدت مات بودن (تاری)', $c['blur'], 0, 80, 1, 'px' );
		$range( 'sat', 'غلظت رنگ پشت شیشه', $c['sat'], 0, 300, 5, '٪' );
		$range( 'bright', 'روشنایی پشت شیشه', $c['bright'], 30, 200, 5, '٪' );
		$check( 'edge', $c['edge'], 'خط نور روی لبهٔ شیشه', 'در دموهایی که شیشه حرکت می‌کند دیده می‌شود.' );
		echo '</section><section class="ay-card"><h2>' . ay_icon( 'list', 22 ) . '<span>زمان و حرکت</span></h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$range( 'dur', 'مدت بسته شدن / باز شدن', $c['dur'], 200, 3000, 10, 'ms', 'هرچه بیشتر، آرام‌تر' );
		$range( 'hold', 'کمترین مکث روی لوگو', $c['hold'], 0, 4000, 10, 'ms' );
		$opts = array();
		foreach ( ay_curtain_eases() as $k => $v ) {
			$opts[ $k ] = $v[0];
		}
		$select( 'ease', 'نوع حرکت', $c['ease'], $opts );
		$check( 'first', $c['first'], 'هنگام اولین ورود به سایت هم پخش شود', 'مثل یک پیش‌بارگذار کوتاه با لوگو.' );
		$check( 'mobile', $c['mobile'], 'در موبایل هم پخش شود' );
		echo '</section><section class="ay-card"><h2>' . ay_icon( 'user', 22 ) . '<span>لوگو و نام وسط پرده</span></h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$check( 'logo', $c['logo'], 'نمایش لوگو' );
		$src = $c['logo_id'] ? wp_get_attachment_image_url( absint( $c['logo_id'] ), 'thumbnail' ) : '';
		echo '<div class="ay-f"><span>لوگوی دلخواه</span><input type="hidden" id="ay_logo_id" name="logo_id" value="' . (int) $c['logo_id'] . '"><div class="ay-ed-gallery" id="ay_logo_list">' . ( $src ? '<span class="ay-ed-thumb ay-ed-thumb-dark" data-id="' . (int) $c['logo_id'] . '"><img src="' . esc_url( $src ) . '" alt=""><button type="button" class="ay-ed-x" aria-label="حذف">×</button></span>' : '' ) . '</div><p><button type="button" class="button" id="ay_logo_add">انتخاب تصویر</button></p><em>خالی = نشان سایت. تصویر PNG یا WebP بدون پس‌زمینه بهترین نتیجه را می‌دهد.</em></div>';
		$range( 'logo_size', 'اندازهٔ لوگو', $c['logo_size'], 30, 480, 2, 'px' );
		$color( 'logo_color', 'رنگ نشان، نام و خط', $c['logo_color'], 'خالی = رنگ متن تم. روی لوگوی دلخواه اثر ندارد.' );
		$select( 'logo_fx', 'حرکت لوگو', $c['logo_fx'], array( 'breathe' => 'نفس کشیدن آرام', 'shine' => 'عبور نور از روی لوگو', 'spin' => 'چرخش سه‌بعدی آرام', 'draw' => 'پیدا شدن از پایین به بالا', 'none' => 'ثابت' ) );
		$check( 'name', $c['name'], 'نمایش نام زیر لوگو' );
		echo '<label class="ay-f" for="ay_name_text"><span>نام</span><input type="text" id="ay_name_text" name="name_text" value="' . esc_attr( $c['name_text'] ) . '" dir="ltr"><em>خالی = نام برند</em></label>';
		$check( 'line', $c['line'], 'خط باریک متحرک زیر نام' );
		$color( 'line_color', 'رنگ خط متحرک', $c['line_color'], 'خالی = رنگ تأکید تم' );
		echo '</section>';
	} else {
		$f = ay_fx_settings();
		echo '<section class="ay-card"><h2>' . ay_icon( 'gem', 22 ) . '<span>افکت‌های کل سایت</span></h2><p class="ay-help">همه خاموش‌اند تا خودتان روشن کنید. افکت‌های موس فقط روی دستگاه‌های دارای موس اجرا می‌شوند.</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$check( 'cursor', $f['cursor'], 'هالهٔ نور نرم دنبال موس', 'یک نور ملایم که روی کل صفحه همراه موس حرکت می‌کند.' );
		$color( 'cursor_color', 'رنگ نور موس', $f['cursor_color'], 'خالی = رنگ تأکید تم' );
		$range( 'cursor_size', 'اندازهٔ نور موس', $f['cursor_size'], 80, 1200, 10, 'px' );
		$range( 'cursor_op', 'شفافیت نور موس', $f['cursor_op'], 0, 100, 1, '٪' );
		$check( 'progress', $f['progress'], 'خط پیشرفت خواندن بالای پنجره' );
		$color( 'progress_c', 'رنگ خط پیشرفت', $f['progress_c'], 'خالی = رنگ تأکید تم' );
		$range( 'progress_h', 'ضخامت خط پیشرفت', $f['progress_h'], 1, 12, 1, 'px' );
		$check( 'grain', $f['grain'], 'دانهٔ فیلم روی صفحه', 'بافت خیلی ریز که به صفحه حس چاپی می‌دهد.' );
		$range( 'grain_op', 'شدت دانه', $f['grain_op'], 0, 40, 1, '٪' );
		$check( 'sparkle', $f['sparkle'], 'جرقه‌های ریز هنگام کلیک' );
		$range( 'sparkle_n', 'تعداد جرقه در هر کلیک', $f['sparkle_n'], 1, 24, 1, '' );
		$color( 'sparkle_c', 'رنگ جرقه', $f['sparkle_c'], 'خالی = رنگ تأکید تم' );
		$check( 'magnet', $f['magnet'], 'دکمه‌های مغناطیسی', 'دکمه‌ها کمی به سمت موس کشیده می‌شوند.' );
		$range( 'magnet_k', 'قدرت مغناطیس', $f['magnet_k'], 0, 100, 1, '٪' );
		$check( 'shine', $f['shine'], 'عبور نور از روی دکمه‌ها هنگام هاور' );
		$color( 'selection', 'رنگ انتخاب متن', $f['selection'], 'خالی = پیش‌فرض مرورگر' );
		$color( 'scrollbar', 'رنگ نوار اسکرول', $f['scrollbar'], 'خالی = پیش‌فرض مرورگر' );
		echo '</section>';
	}
	echo '<p class="ay-save"><button type="submit" class="button button-primary button-hero">ذخیرهٔ تنظیمات</button></p></form>';
	ay_admin_foot();
}
