<?php
/**
 * The «آرزو» dashboard: setup, colour themes, demos, categories and settings.
 * Persian and right-to-left whatever the WordPress language is. Every state-changing
 * action checks a nonce and the user's capability.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a7aaad" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h12l4 5-10 11L2 9z"/><path d="M2 9h20M9 4 7.5 9 12 20l4.5-11L15 4"/></svg>' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	add_menu_page( 'آرزو', 'آرزو', 'manage_options', 'arezoo', 'ay_page_setup', $icon, 3 );
	add_submenu_page( 'arezoo', 'راه‌اندازی', 'راه‌اندازی', 'manage_options', 'arezoo', 'ay_page_setup' );
	add_submenu_page( 'arezoo', 'تم‌های رنگی', 'تم‌های رنگی', 'manage_options', 'arezoo-themes', 'ay_page_themes' );
	add_submenu_page( 'arezoo', 'دموها', 'دموها', 'manage_options', 'arezoo-demos', 'ay_page_demos' );
	add_submenu_page( 'arezoo', 'دسته‌بندی‌ها', 'دسته‌بندی‌ها', 'manage_categories', 'arezoo-terms', 'ay_page_terms' );
	add_submenu_page( 'arezoo', 'تنظیمات', 'تنظیمات', 'manage_options', 'arezoo-settings', 'ay_page_settings' );
}, 9 );

function ay_is_admin_screen() {
	$s = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $s ) {
		return false;
	}
	return false !== strpos( (string) $s->id, 'arezoo' ) || 'ay_work' === $s->post_type || in_array( $s->taxonomy, array( 'ay_collection', 'ay_type' ), true );
}

add_action( 'admin_enqueue_scripts', function () {
	if ( ! ay_is_admin_screen() ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'arezoo-admin', ay_asset( 'css/admin.css' ), array(), AY_VERSION );
	wp_enqueue_script( 'arezoo-admin', ay_asset( 'js/admin.js' ), array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ), AY_VERSION, true );
	wp_localize_script( 'arezoo-admin', 'AY_ADMIN', array(
		'ajax'  => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'ay_admin' ),
		't'     => array(
			'working'  => 'در حال انجام…',
			'done'     => 'انجام شد.',
			'error'    => 'انجام نشد. دوباره تلاش کنید.',
			'pick'     => 'انتخاب تصاویر گالری',
			'use'      => 'افزودن به گالری',
			'sure_del' => 'این دسته حذف شود؟ آثار داخل آن حذف نمی‌شوند.',
		),
	) );
} );

add_filter( 'admin_body_class', function ( $classes ) {
	return ay_is_admin_screen() ? $classes . ' ay-admin' : $classes;
} );

/* ------------------------------------------------------------------ shared UI */
function ay_admin_head( $title, $lede = '' ) {
	echo '<div class="wrap ay-wrap" dir="rtl"><header class="ay-top"><span class="ay-top-mark" aria-hidden="true"></span><div><h1>' . esc_html( $title ) . '</h1>' . ( $lede ? '<p>' . esc_html( $lede ) . '</p>' : '' ) . '</div></header><div class="ay-flash" id="ayFlash" role="status" aria-live="polite" hidden></div>';
}

function ay_admin_foot() {
	echo '<div class="ay-modal" id="ayModal" hidden><div class="ay-modal-box" role="dialog" aria-modal="true" aria-labelledby="ayModalT"><h2 id="ayModalT"></h2><p id="ayModalP"></p><div class="ay-modal-act"><button type="button" class="button button-primary" id="ayModalOk"></button> <button type="button" class="button" id="ayModalNo">انصراف</button></div></div></div></div>';
}

function ay_admin_tabs( $tabs, $current, $page ) {
	echo '<nav class="ay-tabs" aria-label="بخش‌ها">';
	foreach ( $tabs as $k => $label ) {
		echo '<a class="ay-tab' . ( $k === $current ? ' is-on' : '' ) . '" href="' . esc_url( admin_url( 'admin.php?page=' . $page . '&tab=' . $k ) ) . '">' . esc_html( $label ) . '</a>';
	}
	echo '</nav>';
}

/* ================================================================= راه‌اندازی */
function ay_page_setup() {
	ay_admin_head( 'راه‌اندازی آرزو', 'همه‌چیز لازم برای سایت با یک دکمه ساخته می‌شود و بعد در المنتور قابل ویرایش است.' );
	$log = get_transient( 'arezoo_build_log' );
	if ( $log ) {
		delete_transient( 'arezoo_build_log' );
		echo '<div class="ay-card ay-ok"><h2>' . ay_icon( 'star', 22 ) . '<span>سایت به‌صورت خودکار ساخته شد</span></h2><ul class="ay-log">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( (array) $log as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		echo '</ul></div>';
	}
	$built  = (int) get_option( 'arezoo_built' );
	$checks = array(
		array( 'قالب Arezoo فعال است', 'arezoo' === get_template() ),
		array( 'المنتور فعال است' . ( defined( 'ELEMENTOR_VERSION' ) ? ' (نسخهٔ ' . ELEMENTOR_VERSION . ')' : '' ), ay_elementor_ready() ),
		array( 'سایت ساخته شده است', (bool) $built ),
	);
	echo '<div class="ay-grid"><section class="ay-card"><h2>' . ay_icon( 'list', 22 ) . '<span>وضعیت</span></h2><ul class="ay-checks">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	foreach ( $checks as $c ) {
		echo '<li class="' . ( $c[1] ? 'is-ok' : 'is-no' ) . '">' . esc_html( $c[0] ) . '</li>';
	}
	echo '</ul>';
	if ( $built ) {
		echo '<p class="ay-help">آخرین ساخت: ' . esc_html( wp_date( 'Y/m/d H:i', $built ) ) . '</p>';
	}
	echo '</section>';

	echo '<section class="ay-card"><h2>' . ay_icon( 'tool', 22 ) . '<span>ساخت سایت</span></h2><p class="ay-help">این کارها انجام می‌شود: برگه‌های Home، Collections، About Me و Contact؛ قالب‌های هدر، فوتر، تک‌اثر، آرشیو و ۴۰۴؛ فهرست اصلی؛ صفحهٔ اصلی؛ پیوندهای یکتا؛ تنظیمات و فونت‌های المنتور؛ تم رنگی. چیزی که از قبل وجود دارد دست نمی‌خورد و فقط موارد جاافتاده ساخته می‌شوند.</p>';
	echo '<label class="ay-check"><input type="checkbox" id="aySamples" checked> <span>۱۰ اثر نمونه و ۳ کالکشن هم وارد شود (بعداً با یک دکمه حذف می‌شوند)</span></label>';
	echo '<p><button type="button" class="button button-primary button-hero" data-ay-act="ay_build">' . ( $built ? 'تکمیل موارد جاافتاده' : 'ساخت سایت' ) . '</button></p>';
	if ( $built ) {
		echo '<p><button type="button" class="button" data-ay-act="ay_build" data-force="1" data-confirm="چیدمان همهٔ برگه‌ها و قالب‌های آرزو به حالت اولیه برمی‌گردد. از چیدمان فعلی هر کدام یک نسخهٔ پشتیبان نگه داشته می‌شود." data-confirm-t="بازسازی از نو" data-confirm-ok="بازسازی کن">بازسازی همهٔ چیدمان‌ها از نو</button></p>';
	}
	echo '</section>';

	$n = ay_sample_count();
	echo '<section class="ay-card"><h2>' . ay_icon( 'gem', 22 ) . '<span>آثار نمونه</span></h2><p class="ay-help">عکس آثار نمونه از بستهٔ اولیه است، نه کار شما. وقتی آثار خودتان را اضافه کردید، نمونه‌ها را حذف کنید.</p><p><strong>' . (int) $n . '</strong> اثر نمونه در سایت هست.</p><p>';
	echo '<button type="button" class="button" data-ay-act="ay_samples_import">وارد کردن آثار نمونه</button> ';
	if ( $n ) {
		echo '<button type="button" class="button ay-danger" data-ay-act="ay_samples_remove" data-confirm="همهٔ آثار نمونه، عکس‌هایشان و دسته‌های خالیِ نمونه حذف می‌شوند. آثار خودتان دست نمی‌خورند." data-confirm-t="حذف آثار نمونه" data-confirm-ok="حذف کن">حذف همهٔ آثار نمونه</button>';
	}
	echo '</p></section>';

	echo '<section class="ay-card"><h2>' . ay_icon( 'arrow', 22 ) . '<span>قدم‌های بعدی</span></h2><ol class="ay-steps">';
	$links = array(
		array( 'اطلاعات تماس واقعی را وارد کنید', 'admin.php?page=arezoo-settings' ),
		array( 'تم رنگی را انتخاب کنید', 'admin.php?page=arezoo-themes' ),
		array( 'چیدمان هر بخش را از دموها انتخاب کنید', 'admin.php?page=arezoo-demos' ),
		array( 'آثار خودتان را اضافه کنید', 'edit.php?post_type=ay_work' ),
		array( 'کالکشن‌ها و نوع‌ها را مرتب کنید', 'admin.php?page=arezoo-terms' ),
	);
	foreach ( $links as $l ) {
		echo '<li><a href="' . esc_url( admin_url( $l[1] ) ) . '">' . esc_html( $l[0] ) . '</a></li>';
	}
	$home = (int) get_option( 'arezoo_page_home' );
	if ( $home ) {
		echo '<li><a href="' . esc_url( admin_url( 'post.php?post=' . $home . '&action=elementor' ) ) . '">صفحهٔ اصلی را در المنتور ویرایش کنید</a></li>';
	}
	echo '</ol></section></div>';
	ay_admin_foot();
}

/* ================================================================ تم‌های رنگی */
function ay_page_themes() {
	ay_admin_head( 'تم‌های رنگی', '۹۶ تم از دو پالت. تم فقط رنگ‌های سراسری سایت را عوض می‌کند؛ هر رنگی که دستی روی یک بخش، ویجت یا دکمه گذاشته‌اید همان می‌ماند.' );
	$active = ay_active_theme_id();
	$themes = ay_themes();
	$sets   = ay_palette_sets();
	$tab    = isset( $_GET['tab'] ) && isset( $sets[ $_GET['tab'] ] ) ? sanitize_key( $_GET['tab'] ) : $themes[ $active ]['set']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tabs   = array();
	foreach ( $sets as $k => $set ) {
		$tabs[ $k ] = $set['label'] . ' (۴۸ تم)';
	}
	ay_admin_tabs( $tabs, $tab, 'arezoo-themes' );
	echo '<div class="ay-card ay-now"><div><strong>تم فعال:</strong> ' . esc_html( $themes[ $active ]['label'] ) . ' <span class="ay-help">' . esc_html( $themes[ $active ]['desc'] ) . '</span></div><div class="ay-swatches">';
	foreach ( $sets[ $themes[ $active ]['set'] ]['colors'] as $hex ) {
		echo '<span style="background:' . esc_attr( $hex ) . '" title="' . esc_attr( $hex ) . '"></span>';
	}
	echo '</div>';
	if ( get_option( 'arezoo_theme_prev' ) && get_option( 'arezoo_theme_prev' ) !== $active ) {
		echo '<button type="button" class="button" data-ay-act="ay_theme_apply" data-id="' . esc_attr( get_option( 'arezoo_theme_prev' ) ) . '">بازگشت به تم قبلی</button>';
	}
	echo '</div>';
	foreach ( ay_theme_families() as $f ) {
		if ( $f['set'] !== $tab ) {
			continue;
		}
		echo '<h2 class="ay-fam">' . esc_html( $f['label'] ) . '</h2><div class="ay-themes">';
		foreach ( $themes as $t ) {
			if ( $t['family'] !== $f['id'] ) {
				continue;
			}
			$c = $t['colors'];
			echo '<div class="ay-theme' . ( $t['id'] === $active ? ' is-on' : '' ) . '"><div class="ay-theme-view" style="background:' . esc_attr( $c['bg'] ) . ';color:' . esc_attr( $c['ink'] ) . '"><span class="ay-tv-bar" style="background:' . esc_attr( $c['surface'] ) . ';border-color:' . esc_attr( ay_mix( $c['bg'], $c['ink'], 0.16 ) ) . '"></span><span class="ay-tv-h">Aa</span><span class="ay-tv-t" style="color:' . esc_attr( $c['muted'] ) . '">Jewelry design</span><span class="ay-tv-row"><span class="ay-tv-btn" style="background:' . esc_attr( $c['btn'] ) . ';color:' . esc_attr( $c['btnink'] ) . ';border-color:' . esc_attr( $c['btnline'] ) . '">Explore</span><span class="ay-tv-dot" style="background:' . esc_attr( $c['accent'] ) . '"></span><span class="ay-tv-card" style="background:' . esc_attr( $c['surface2'] ) . '"></span></span><span class="ay-tv-band" style="background:' . esc_attr( $c['band'] ) . '"></span></div>';
			echo '<div class="ay-theme-meta"><strong>' . esc_html( $t['label'] ) . '</strong><span>' . esc_html( $t['desc'] ) . '</span>';
			echo $t['id'] === $active ? '<span class="ay-pill">فعال</span>' : '<button type="button" class="button button-primary" data-ay-act="ay_theme_apply" data-id="' . esc_attr( $t['id'] ) . '">اعمال</button>';
			echo '</div></div>';
		}
		echo '</div>';
	}
	ay_admin_foot();
}

/* ====================================================================== دموها */
/** Small animated wireframe of a demo, drawn from a compact description (type:x,y,w,h in percent). */
function ay_demo_mini( $key ) {
	$m = array(
		'hero-1' => 'img:0,0,100,100;gl:22,5,56,9;mv:8,26,22,30;tx:8,62,34,6;tx:8,72,26,4;bt:8,82,18,8',
		'hero-2' => 'bg:0,0,100,100;gl:22,5,56,9;fl:58,12,10,46;fl:72,18,10,46;sk:4,10,26,50;tx:8,62,34,6;tx:8,72,26,4;bt:8,82,18,8',
		'hero-3' => 'img:0,0,100,100;gl:22,5,56,9;pl:44,30,12,20;tx:33,64,34,6;tx:38,74,24,4;bt:41,84,18,8',
		'hero-4' => 'img:0,0,100,100;sl:0,0,100,100;gl:22,5,56,9;tx:33,44,34,6;tx:38,54,24,4;bt:41,64,18,8',
		'hero-5' => 'img:0,0,100,100;gl:22,5,56,9;bt:41,82,18,8',
		'ar-1'   => 'tx:6,8,30,6;ln:6,20,88,1;ft:6,24,10,4;ft:20,24,10,4;ft:34,24,10,4;cd:6,36,27,50;cd:36.5,36,27,50;cd:67,36,27,50',
		'ar-2'   => 'tx:6,8,30,6;ln:6,20,88,1;ft:6,24,10,4;ft:20,24,10,4;cd:6,36,27,58;cd:36.5,36,27,40;cd:67,36,27,50',
		'ar-3'   => 'tx:6,8,30,6;ln:6,20,88,1;ft:6,24,10,4;ft:20,24,10,4;cd:6,36,14,16;tx:24,42,40,5;cd:6,56,14,16;tx:24,62,34,5;cd:6,76,14,16;tx:24,82,44,5',
		'ar-4'   => 'tx:6,8,30,6;ln:6,20,88,1;ft:6,24,10,4;ft:20,24,10,4;cd:6,36,43,54;cd:52,36,20,25;cd:74,36,20,25;cd:52,65,20,25;cd:74,65,20,25',
		'ar-5'   => 'tx:6,8,30,6;ln:6,20,88,1;ft:6,24,10,4;ft:20,24,10,4;sc:6,38,26,50;sc:35,38,26,50;sc:64,38,26,50;sc:93,38,26,50',
		'sg-1'   => 'cd:6,12,42,62;th:6,78,8,10;th:16,78,8,10;th:26,78,8,10;tx:54,14,34,8;tx:54,28,30,4;bx:54,40,19,12;bx:75,40,19,12;bx:54,56,19,12;bx:75,56,19,12',
		'sg-2'   => 'tx:6,14,34,8;tx:6,28,30,4;bx:6,40,19,12;bx:27,40,19,12;bx:6,56,19,12;bx:27,56,19,12;cd:60,12,34,62;th:52,12,6,10;th:52,24,6,10;th:52,36,6,10',
		'sg-3'   => 'tx:30,10,40,8;tx:36,22,28,4;sc:6,34,34,34;sc:43,34,34,34;sc:80,34,34,34;bx:6,74,27,14;bx:36.5,74,27,14;bx:67,74,27,14',
		'sg-4'   => 'cd:6,12,25,36;cd:33,12,25,36;cd:6,52,25,36;cd:33,52,25,36;tx:64,14,30,8;tx:64,28,26,4;bx:64,40,30,12;bx:64,56,30,12;bx:64,72,30,12',
		'sg-5'   => 'tx:6,8,36,8;cd:6,22,88,36;tx:6,64,36,4;tx:6,72,30,4;bx:52,64,20,12;bx:74,64,20,12;bx:52,80,20,12;bx:74,80,20,12',
		'ab-1'   => 'tx:6,16,36,8;tx:6,30,40,4;tx:6,38,34,4;ar:62,10,30,52;mo:8,70,10,18;mo:26,70,10,18;mo:44,70,10,18;mo:62,70,10,18',
		'ab-2'   => 'ar:8,10,30,52;tx:48,16,36,8;tx:48,30,40,4;tx:48,38,34,4;tl:8,70,2,24;tx:14,72,40,4;tx:14,84,34,4',
		'ab-3'   => 'tx:30,10,40,8;tx:26,24,48,4;cp:10,38,34,52;tx:52,46,36,7;tx:52,60,32,4;tx:52,68,28,4',
		'ab-4'   => 'tx:6,10,36,8;tx:6,24,40,4;tb:8,40,30,52;pn:30,62,10,10;tx:48,46,40,6;tx:48,58,34,4;tx:48,76,40,6;tx:48,88,30,4',
		'ab-5'   => 'tx:6,10,36,8;tx:6,24,48,4;st:0,36,100,22;tl:8,66,2,28;tx:14,68,40,4;tx:14,80,34,4;tx:14,92,30,4',
		'fq-1'   => 'tx:6,8,40,7;ln:6,24,88,1;q:6,28,88,7;q:6,40,88,7;q:6,52,88,7;ln:6,68,88,1;q:6,72,88,7;q:6,84,88,7',
		'fq-2'   => 'tx:6,8,40,7;tp:6,28,24,5;tp:6,40,20,5;tp:6,52,26,5;tp:6,64,22,5;q:38,28,56,8;q:38,42,56,8;q:38,56,56,8;q:38,70,56,8',
		'fq-3'   => 'tx:6,8,40,7;q:6,28,41,7;q:6,40,41,7;q:6,52,41,7;q:53,28,41,7;q:53,40,41,7;q:53,52,41,7;q:6,70,41,7;q:53,70,41,7',
		'fq-4'   => 'tx:6,8,40,7;gc:6,26,42,30;gc:52,26,42,30;gc:6,60,42,30;gc:52,60,42,30',
		'fq-5'   => 'tx:6,8,40,7;tp:6,26,16,5;tp:26,26,16,5;tp:46,26,16,5;tp:66,26,16,5;ln:6,34,88,1;q:6,42,70,8;q:6,56,70,8;q:6,70,70,8',
		'hd-1'   => 'bg:0,0,100,100;gl:14,14,72,18;dt:19,19,6,8;tx:28,21,16,4;tp:56,21,8,4;tp:66,21,8,4;tp:76,21,8,4;tx:10,56,44,8;tx:10,70,34,4',
		'hd-2'   => 'bg:0,0,100,100;gl:0,0,100,18;dt:6,5,6,8;tx:15,7,16,4;tp:62,7,8,4;tp:74,7,8,4;tp:86,7,8,4;tx:10,56,44,8;tx:10,70,34,4',
		'hd-3'   => 'bg:0,0,100,100;gl:28,10,44,30;dt:47,14,6,8;tx:40,24,20,4;tp:34,32,8,3;tp:46,32,8,3;tp:58,32,8,3;tx:10,60,44,8;tx:10,74,34,4',
		'hd-4'   => 'bg:0,0,100,100;dt:8,10,6,8;tx:17,12,16,4;tp:62,12,8,4;tp:74,12,8,4;tp:86,12,8,4;tx:10,56,44,8;tx:10,70,34,4',
		'hd-5'   => 'bg:0,0,100,100;gl:30,12,40,16;dt:34,16,5,8;tp:44,18,6,4;tp:52,18,6,4;tp:60,18,6,4;tx:10,56,44,8;tx:10,70,34,4',
		'ft-1'   => 'bg:0,0,100,100;dk:8,8,84,62;tx:14,16,30,8;tx:14,30,36,4;ln:14,44,72,1;tp:14,50,8,4;tp:24,50,8,4;tp:60,50,26,4;gl:30,78,40,14',
		'ft-2'   => 'bg:0,0,100,100;dk:8,30,84,22;tx:14,38,22,6;tp:44,39,12,4;tp:60,39,12,4;tx:76,39,10,4;gl:30,78,40,14',
		'ft-3'   => 'bg:0,0,100,100;dk:8,8,84,62;tx:14,14,70,22;ln:14,46,72,1;tp:14,52,8,4;tp:24,52,8,4;tp:60,52,26,4;gl:30,78,40,14',
		'ft-4'   => 'bg:0,0,100,100;dk:8,8,84,62;tx:14,16,24,8;tx:14,30,22,4;tp:46,18,12,4;tp:46,28,12,4;tp:46,38,12,4;tp:68,18,18,4;tp:68,28,18,4;tp:68,38,18,4;gl:30,78,40,14',
		'ft-5'   => 'bg:0,0,100,100;dk:8,8,84,62;dt:46,14,8,12;tx:36,30,28,6;tp:30,44,10,4;tp:45,44,10,4;tp:60,44,10,4;tx:40,56,20,3;gl:30,78,40,14',
	);
	if ( ! isset( $m[ $key ] ) ) {
		return '';
	}
	$out = '';
	foreach ( explode( ';', $m[ $key ] ) as $i => $part ) {
		list( $type, $box ) = explode( ':', $part );
		$b    = array_map( 'floatval', explode( ',', $box ) );
		$out .= '<i class="m-' . esc_attr( $type ) . '" style="left:' . $b[0] . '%;top:' . $b[1] . '%;width:' . $b[2] . '%;height:' . $b[3] . '%;--i:' . (int) $i . '"></i>';
	}
	return '<span class="ay-mini" aria-hidden="true">' . $out . '</span>';
}

function ay_page_demos() {
	$areas = ay_demo_areas();
	$tab   = isset( $_GET['tab'] ) && isset( $areas[ $_GET['tab'] ] ) ? sanitize_key( $_GET['tab'] ) : 'hero'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	ay_admin_head( 'دموها', 'برای هر بخش پنج چیدمان آماده هست. «اعمال» همان چیدمان را واقعاً روی سایت می‌گذارد و بعد در المنتور به‌صورت ویجت‌های عادی قابل ویرایش است.' );
	$tabs = array();
	foreach ( $areas as $k => $a ) {
		$tabs[ $k ] = $a['label'];
	}
	ay_admin_tabs( $tabs, $tab, 'arezoo-demos' );
	$a      = $areas[ $tab ];
	$state  = ay_demo_state();
	$cur    = isset( $state[ $tab ]['n'] ) ? (int) $state[ $tab ]['n'] : 0;
	$target = ay_demo_target( $tab );
	$edit   = $target[0] ? admin_url( 'post.php?post=' . $target[0] . '&action=elementor' ) : '';
	echo '<div class="ay-card ay-now"><div><strong>مقصد:</strong> ' . esc_html( $a['target'] );
	if ( ! $target[0] ) {
		echo ' <span class="ay-warn">هنوز ساخته نشده است؛ ابتدا از «راه‌اندازی» سایت را بسازید.</span>';
	} elseif ( $cur ) {
		echo ' — <strong>دموی فعال:</strong> ' . esc_html( $a['demos'][ $cur ][0] ) . ( ay_demo_edited( $tab ) ? ' <span class="ay-pill ay-pill-soft">بعد از اعمال، در المنتور ویرایش شده</span>' : '' );
	}
	echo '</div><div class="ay-now-act">';
	if ( $edit ) {
		echo '<a class="button" href="' . esc_url( $edit ) . '">ویرایش در المنتور</a> ';
	}
	if ( $target[0] && ay_backup_count( $target[0] ) ) {
		echo '<button type="button" class="button" data-ay-act="ay_demo_undo" data-area="' . esc_attr( $tab ) . '" data-confirm="چیدمان فعلی این بخش کنار می‌رود و چیدمان قبل از آخرین «اعمال» برمی‌گردد." data-confirm-t="بازگردانی نسخهٔ قبلی" data-confirm-ok="برگردان">بازگردانی نسخهٔ قبلی</button>';
	}
	echo '</div></div><div class="ay-demos">';
	foreach ( $a['demos'] as $n => $demo ) {
		$on = $n === $cur;
		echo '<article class="ay-demo' . ( $on ? ' is-on' : '' ) . '">' . ay_demo_mini( $demo[2] ) . '<div class="ay-demo-meta"><h3>' . (int) $n . ' · ' . esc_html( $demo[0] ) . '</h3><p>' . esc_html( $demo[1] ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $on && ! ay_demo_edited( $tab ) ) {
			echo '<span class="ay-pill">فعال</span>';
		} else {
			echo '<button type="button" class="button button-primary" ' . ( $target[0] ? '' : 'disabled ' ) . 'data-ay-act="ay_demo_apply" data-area="' . esc_attr( $tab ) . '" data-n="' . (int) $n . '" data-confirm="چیدمان فعلیِ «' . esc_attr( $a['target'] ) . '» با این دمو جایگزین می‌شود. قبل از جایگزینی، از چیدمان فعلی یک نسخهٔ پشتیبان نگه داشته می‌شود و با «بازگردانی نسخهٔ قبلی» برمی‌گردد." data-confirm-t="اعمال دموی «' . esc_attr( $demo[0] ) . '»" data-confirm-ok="اعمال کن">' . ( $on ? 'اعمال دوباره' : 'اعمال' ) . '</button>';
		}
		echo '</div></article>';
	}
	echo '</div>';
	ay_admin_foot();
}

/* ================================================================ دسته‌بندی‌ها */
function ay_page_terms() {
	ay_admin_head( 'دسته‌بندی‌ها', 'کالکشن‌ها و نوع‌ها را اضافه، حذف و تغییر نام دهید و با کشیدن، ترتیبشان را عوض کنید. همین ترتیب در فیلترهای سایت نمایش داده می‌شود.' );
	echo '<div class="ay-grid">';
	foreach ( array( 'ay_collection' => array( 'کالکشن‌ها', 'gem', 'مثلاً Ribbon' ), 'ay_type' => array( 'نوع‌ها', 'ring', 'مثلاً Earrings' ) ) as $tax => $info ) {
		echo '<section class="ay-card"><h2>' . ay_icon( $info[1], 22 ) . '<span>' . esc_html( $info[0] ) . '</span></h2><ul class="ay-terms" data-tax="' . esc_attr( $tax ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( ay_terms_ordered( $tax ) as $t ) {
			echo '<li class="ay-term" data-id="' . (int) $t->term_id . '"><span class="ay-drag" title="بکشید">⋮⋮</span><input type="text" value="' . esc_attr( $t->name ) . '" dir="ltr" aria-label="نام"><span class="ay-count">' . (int) $t->count . ' اثر</span><button type="button" class="ay-x" aria-label="حذف">×</button></li>';
		}
		echo '</ul><div class="ay-term-add"><input type="text" placeholder="' . esc_attr( $info[2] ) . '" dir="ltr" aria-label="نام تازه"><button type="button" class="button button-primary" data-ay-term-add="' . esc_attr( $tax ) . '">افزودن</button></div><p class="ay-help">تغییر نام با خارج شدن از کادر ذخیره می‌شود. ترتیب بعد از رها کردن ذخیره می‌شود.</p></section>';
	}
	echo '</div>';
	ay_admin_foot();
}

/* ==================================================================== تنظیمات */
function ay_page_settings() {
	$tab = isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( 'contact', 'wallpaper' ), true ) ? sanitize_key( $_GET['tab'] ) : 'contact'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_POST['ay_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ay_settings_nonce'] ) ), 'ay_settings' ) && current_user_can( 'manage_options' ) ) {
		if ( 'contact' === $tab ) {
			$cur = wp_parse_args( (array) get_option( 'arezoo_settings', array() ), ay_default_settings() );
			foreach ( array( 'brand', 'instagram', 'telegram' ) as $k ) {
				$cur[ $k ] = isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : '';
			}
			$cur['email']      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
			$cur['site_en']    = empty( $_POST['site_en'] ) ? 0 : 1;
			$cur['admin_wash'] = isset( $_POST['admin_wash'] ) ? ay_hex( sanitize_text_field( wp_unslash( $_POST['admin_wash'] ) ), '#99E1D9' ) : '#99E1D9';
			update_option( 'arezoo_settings', $cur );
		} else {
			$d = ay_wallpaper_defaults();
			$w = array( 'on' => empty( $_POST['on'] ) ? 0 : 1, 'mobile' => empty( $_POST['mobile'] ) ? 0 : 1 );
			foreach ( array( 'opacity' => array( 0, 100 ), 'size' => array( 120, 1600 ), 'spacing' => array( 240, 3000 ), 'rotate' => array( 0, 180 ), 'parallax' => array( 0, 60 ), 'blur' => array( 0, 20 ) ) as $k => $range ) {
				$w[ $k ] = isset( $_POST[ $k ] ) ? max( $range[0], min( $range[1], (float) $_POST[ $k ] ) ) : $d[ $k ];
			}
			$w['side']   = isset( $_POST['side'] ) && in_array( $_POST['side'], array( 'alt', 'left', 'right', 'center' ), true ) ? sanitize_key( $_POST['side'] ) : 'alt';
			$w['color']  = ! empty( $_POST['color'] ) ? ay_hex( sanitize_text_field( wp_unslash( $_POST['color'] ) ), '' ) : '';
			$w['motifs'] = isset( $_POST['motifs'] ) ? array_values( array_intersect( array_map( 'sanitize_key', (array) wp_unslash( $_POST['motifs'] ) ), array_keys( ay_wallpaper_motifs() ) ) ) : array();
			$w['custom'] = isset( $_POST['custom'] ) ? array_values( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['custom'] ) ) ) ) ) ) : array();
			$w['skip']   = isset( $_POST['skip'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['skip'] ) ) ) ) : array();
			update_option( 'arezoo_wallpaper', $w );
		}
		echo '<div class="notice notice-success is-dismissible" dir="rtl"><p>تنظیمات ذخیره شد.</p></div>';
	}
	ay_admin_head( 'تنظیمات آرزو' );
	ay_admin_tabs( array( 'contact' => 'برند و تماس', 'wallpaper' => 'پس‌زمینهٔ اسکچ' ), $tab, 'arezoo-settings' );
	echo '<form method="post" class="ay-form" action="' . esc_url( admin_url( 'admin.php?page=arezoo-settings&tab=' . $tab ) ) . '">';
	wp_nonce_field( 'ay_settings', 'ay_settings_nonce' );
	$field = function ( $id, $label, $value, $help = '', $type = 'text', $attr = '' ) {
		echo '<label class="ay-f" for="ay_' . esc_attr( $id ) . '"><span>' . esc_html( $label ) . '</span><input type="' . esc_attr( $type ) . '" id="ay_' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" dir="ltr" ' . $attr . '>' . ( $help ? '<em>' . esc_html( $help ) . '</em>' : '' ) . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	};
	if ( 'contact' === $tab ) {
		echo '<section class="ay-card"><h2>' . ay_icon( 'user', 22 ) . '<span>برند</span></h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$field( 'brand', 'نام برند', ay_opt( 'brand' ), 'کنار لوگو در هدر و فوتر نمایش داده می‌شود.' );
		echo '</section><section class="ay-card"><h2>' . ay_icon( 'mail', 22 ) . '<span>راه‌های تماس</span></h2><p class="ay-help">این نشانی‌ها یک بار این‌جا وارد می‌شوند و در صفحهٔ تماس، فوتر و نوار پایین استفاده می‌شوند. شماره تلفن عمداً وجود ندارد.</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$field( 'email', 'ایمیل', ay_opt( 'email' ), '', 'email' );
		$field( 'instagram', 'اینستاگرام', ay_opt( 'instagram' ), 'شناسه بدون @ یا نشانی کامل صفحه' );
		$field( 'telegram', 'تلگرام', ay_opt( 'telegram' ), 'شناسه بدون @ یا نشانی کامل' );
		echo '</section><section class="ay-card"><h2>' . ay_icon( 'tool', 22 ) . '<span>زبان و پیشخوان</span></h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<label class="ay-check"><input type="checkbox" name="site_en" value="1" ' . checked( (bool) ay_opt( 'site_en' ), true, false ) . '> <span><strong>سایت همیشه انگلیسی و چپ‌چین باشد</strong><br>حتی اگر زبان وردپرس فارسی باشد. پیشخوان آرزو همیشه فارسی است.</span></label>';
		$field( 'admin_wash', 'رنگ پترن آبرنگی فرم ویرایش اثر', ay_opt( 'admin_wash' ), '', 'text', 'class="ay-color"' );
		echo '</section>';
	} else {
		$w = ay_wallpaper_settings();
		echo '<section class="ay-card"><h2>' . ay_icon( 'image', 22 ) . '<span>پس‌زمینهٔ اسکچ کل سایت</span></h2><p class="ay-help">طرح‌های اسکچ با شفافیت کم پشت همهٔ صفحه‌ها پخش می‌شوند. رنگ خط‌ها خودکار است: در تم تیره روشن و در تم روشن تیره. بخش‌هایی که خودشان رنگ زمینه دارند اسکچ را می‌پوشانند.</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<label class="ay-check"><input type="checkbox" name="on" value="1" ' . checked( ! empty( $w['on'] ), true, false ) . '> <span><strong>نمایش پس‌زمینهٔ اسکچ</strong></span></label>';
		echo '<label class="ay-check"><input type="checkbox" name="mobile" value="1" ' . checked( ! empty( $w['mobile'] ), true, false ) . '> <span>در موبایل هم نمایش داده شود</span></label>';
		$range = function ( $id, $label, $value, $min, $max, $step, $unit, $help = '' ) {
			echo '<label class="ay-f ay-range" for="ay_' . esc_attr( $id ) . '"><span>' . esc_html( $label ) . ' <output>' . esc_html( $value . $unit ) . '</output></span><input type="range" id="ay_' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" data-unit="' . esc_attr( $unit ) . '">' . ( $help ? '<em>' . esc_html( $help ) . '</em>' : '' ) . '</label>';
		};
		$range( 'opacity', 'میزان دیده شدن (شفافیت)', $w['opacity'], 0, 40, 0.5, '٪', 'هرچه کمتر، محوتر. پیشنهاد: ۵ تا ۱۰' );
		$range( 'size', 'اندازهٔ هر طرح', $w['size'], 160, 1200, 10, 'px' );
		$range( 'spacing', 'فاصلهٔ طرح‌ها در طول صفحه', $w['spacing'], 300, 2400, 20, 'px', 'هرچه کمتر، طرح‌های بیشتر' );
		$range( 'rotate', 'بیشترین چرخش تصادفی', $w['rotate'], 0, 60, 1, '°' );
		$range( 'parallax', 'حرکت آرام هنگام اسکرول', $w['parallax'], 0, 40, 1, '٪', 'صفر = ثابت' );
		$range( 'blur', 'تاری', $w['blur'], 0, 10, 0.5, 'px' );
		echo '<label class="ay-f" for="ay_side"><span>جای طرح‌ها</span><select id="ay_side" name="side">';
		foreach ( array( 'alt' => 'یکی‌درمیان چپ و راست', 'left' => 'همه سمت چپ', 'right' => 'همه سمت راست', 'center' => 'وسط صفحه' ) as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $w['side'], $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select></label>';
		$field( 'color', 'رنگ دلخواه خط‌ها', $w['color'], 'خالی = خودکار (هم‌رنگ متن تم)', 'text', 'class="ay-color"' );
		echo '</section><section class="ay-card"><h2>' . ay_icon( 'gem', 22 ) . '<span>طرح‌ها</span></h2><p class="ay-help">طرح‌هایی که تیک دارند به نوبت در صفحه تکرار می‌شوند.</p><div class="ay-motifs">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( ay_wallpaper_motifs() as $k => $label ) {
			echo '<label class="ay-motif"><input type="checkbox" name="motifs[]" value="' . esc_attr( $k ) . '" ' . checked( in_array( $k, (array) $w['motifs'], true ), true, false ) . '><span class="ay-motif-pic" style="--img:url(' . esc_url( ay_asset( 'img/sketch/' . $k . '.webp' ) ) . ')"></span><span>' . esc_html( $label ) . '</span></label>';
		}
		echo '</div><h3>طرح‌های خودتان</h3><p class="ay-help">تصویر PNG یا WebP بدون پس‌زمینه. رنگ خط‌ها مهم نیست؛ فقط شکل آن‌ها استفاده می‌شود.</p><input type="hidden" id="ay_custom" name="custom" value="' . esc_attr( implode( ',', array_map( 'absint', (array) $w['custom'] ) ) ) . '"><div class="ay-ed-gallery" id="ay_custom_list">';
		foreach ( (array) $w['custom'] as $att ) {
			$src = wp_get_attachment_image_url( absint( $att ), 'thumbnail' );
			if ( $src ) {
				echo '<span class="ay-ed-thumb ay-ed-thumb-dark" data-id="' . (int) $att . '"><img src="' . esc_url( $src ) . '" alt=""><button type="button" class="ay-ed-x" aria-label="حذف">×</button></span>';
			}
		}
		echo '</div><p><button type="button" class="button" id="ay_custom_add">افزودن طرح</button></p></section>';
		echo '<section class="ay-card"><h2>' . ay_icon( 'list', 22 ) . '<span>برگه‌های بدون اسکچ</span></h2><p class="ay-help">در برگه‌هایی که تیک می‌زنید پس‌زمینهٔ اسکچ نمایش داده نمی‌شود.</p><div class="ay-skip">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( get_pages( array( 'sort_column' => 'menu_order,post_title' ) ) as $p ) {
			echo '<label class="ay-check"><input type="checkbox" name="skip[]" value="' . (int) $p->ID . '" ' . checked( in_array( (int) $p->ID, array_map( 'absint', (array) $w['skip'] ), true ), true, false ) . '> <span>' . esc_html( $p->post_title ) . '</span></label>';
		}
		echo '</div></section>';
	}
	echo '<p class="ay-save"><button type="submit" class="button button-primary button-hero">ذخیرهٔ تنظیمات</button></p></form>';
	ay_admin_foot();
}

/* ======================================================================== AJAX */
function ay_ajax_guard( $cap = 'manage_options' ) {
	check_ajax_referer( 'ay_admin', 'nonce' );
	if ( ! current_user_can( $cap ) ) {
		wp_send_json_error( array( 'message' => 'شما اجازهٔ این کار را ندارید.' ), 403 );
	}
}

function ay_ajax_result( $r, $ok ) {
	if ( is_wp_error( $r ) ) {
		wp_send_json_error( array( 'message' => $r->get_error_message() ) );
	}
	wp_send_json_success( array( 'message' => $ok, 'reload' => true ) );
}

add_action( 'wp_ajax_ay_build', function () {
	ay_ajax_guard();
	$log = ay_build_site( array( 'samples' => ! empty( $_POST['samples'] ), 'force' => ! empty( $_POST['force'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	wp_send_json_success( array( 'message' => implode( ' ', $log ), 'reload' => true ) );
} );
add_action( 'wp_ajax_ay_samples_import', function () {
	ay_ajax_guard();
	$n = ay_import_samples();
	wp_send_json_success( array( 'message' => $n ? $n . ' اثر نمونه وارد شد.' : 'آثار نمونه از قبل وجود دارند.', 'reload' => true ) );
} );
add_action( 'wp_ajax_ay_samples_remove', function () {
	ay_ajax_guard();
	$n = ay_remove_samples();
	wp_send_json_success( array( 'message' => $n . ' اثر نمونه حذف شد.', 'reload' => true ) );
} );
add_action( 'wp_ajax_ay_theme_apply', function () {
	ay_ajax_guard();
	$id = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! ay_apply_theme( $id ) ) {
		wp_send_json_error( array( 'message' => 'این تم پیدا نشد.' ) );
	}
	wp_send_json_success( array( 'message' => 'تم رنگی اعمال شد.', 'reload' => true ) );
} );
add_action( 'wp_ajax_ay_demo_apply', function () {
	ay_ajax_guard();
	$area = isset( $_POST['area'] ) ? sanitize_key( wp_unslash( $_POST['area'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$n    = isset( $_POST['n'] ) ? absint( $_POST['n'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	ay_ajax_result( ay_apply_demo( $area, $n ), 'دمو اعمال شد.' );
} );
add_action( 'wp_ajax_ay_demo_undo', function () {
	ay_ajax_guard();
	$area = isset( $_POST['area'] ) ? sanitize_key( wp_unslash( $_POST['area'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	ay_ajax_result( ay_undo_demo( $area ), 'نسخهٔ قبلی بازگردانده شد.' );
} );
add_action( 'wp_ajax_ay_terms', function () {
	ay_ajax_guard( 'manage_categories' );
	$tax = isset( $_POST['tax'] ) ? sanitize_key( wp_unslash( $_POST['tax'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! in_array( $tax, array( 'ay_collection', 'ay_type' ), true ) ) {
		wp_send_json_error( array( 'message' => 'نوع دسته‌بندی نامعتبر است.' ) );
	}
	$do   = isset( $_POST['do'] ) ? sanitize_key( wp_unslash( $_POST['do'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	switch ( $do ) {
		case 'add':
			if ( '' === $name ) {
				wp_send_json_error( array( 'message' => 'نام را بنویسید.' ) );
			}
			$t = wp_insert_term( $name, $tax );
			if ( is_wp_error( $t ) ) {
				wp_send_json_error( array( 'message' => 'دسته‌ای با این نام از قبل هست.' ) );
			}
			update_term_meta( $t['term_id'], '_ay_order', 999 );
			wp_send_json_success( array( 'message' => 'افزوده شد.', 'reload' => true ) );
			break;
		case 'rename':
			if ( '' === $name || ! term_exists( $id, $tax ) ) {
				wp_send_json_error( array( 'message' => 'نام نمی‌تواند خالی باشد.' ) );
			}
			$r = wp_update_term( $id, $tax, array( 'name' => $name ) );
			if ( is_wp_error( $r ) ) {
				wp_send_json_error( array( 'message' => 'تغییر نام انجام نشد (شاید این نام تکراری است).' ) );
			}
			wp_send_json_success( array( 'message' => 'نام ذخیره شد.' ) );
			break;
		case 'delete':
			$r = wp_delete_term( $id, $tax );
			if ( ! $r || is_wp_error( $r ) ) {
				wp_send_json_error( array( 'message' => 'حذف انجام نشد.' ) );
			}
			wp_send_json_success( array( 'message' => 'حذف شد.', 'reload' => true ) );
			break;
		case 'order':
			$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['ids'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			foreach ( $ids as $i => $tid ) {
				if ( term_exists( $tid, $tax ) ) {
					update_term_meta( $tid, '_ay_order', $i + 1 );
				}
			}
			wp_send_json_success( array( 'message' => 'ترتیب ذخیره شد.' ) );
			break;
	}
	wp_send_json_error( array( 'message' => 'درخواست نامعتبر است.' ) );
} );

/* Notice on other admin screens while the site is not built yet. */
add_action( 'admin_notices', function () {
	if ( get_option( 'arezoo_built' ) || ! current_user_can( 'manage_options' ) || ay_is_admin_screen() || ! ay_elementor_ready() ) {
		return;
	}
	echo '<div class="notice notice-info" dir="rtl"><p><strong>آرزو:</strong> سایت هنوز ساخته نشده است. <a href="' . esc_url( admin_url( 'admin.php?page=arezoo' ) ) . '">رفتن به راه‌اندازی</a></p></div>';
} );
