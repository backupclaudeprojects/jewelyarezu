<?php
/**
 * Site widgets: logo, menu, bottom bar (dock), contact details and sketch motif.
 *
 * @package ArezooCore
 */

namespace Arezoo\Widgets;

use Elementor\Controls_Manager as CM;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
 * Logo
 * ====================================================================== */
class Logo extends Base {

	public function get_name() {
		return 'ay-logo';
	}

	public function get_title() {
		return 'لوگوی آرزو';
	}

	public function get_icon() {
		return 'eicon-site-logo';
	}

	protected function register_controls() {
		$this->sec( 's_main', 'لوگو' );
		$this->sel( 'source', 'نشان', array( 'mark' => 'نشان آرزو (رنگ‌پذیر)', 'image' => 'تصویر دلخواه', 'none' => 'بدون نشان' ), 'mark' );
		$this->media( 'image', 'تصویر لوگو', '', array( 'condition' => array( 'source' => 'image' ) ) );
		$this->sw( 'name_on', 'نمایش نام کنار نشان', 'yes' );
		$this->txt( 'name', 'نام', '', array( 'placeholder' => ay_opt( 'brand' ), 'description' => 'خالی = نام برند در «آرزو ← تنظیمات»', 'condition' => array( 'name_on' => 'yes' ) ) );
		$this->sw( 'link', 'لینک به صفحهٔ اصلی', 'yes' );
		$this->sel( 'dir', 'چیدمان', array( 'row' => 'نشان کنار نام', 'column' => 'نشان بالای نام' ), 'row', array( 'selectors' => array( '{{WRAPPER}} .ay-logo' => 'flex-direction: {{VALUE}};' ) ) );
		$this->align( 'align', '.ay-logo-wrap' );
		$this->done();
		$this->sec( 'st_main', 'ظاهر', true );
		$this->cssvar( 'size', 'ارتفاع نشان', '--ay-logo-h', 12, 240, 1, 34 );
		$this->cssvar( 'gap', 'فاصلهٔ نشان و نام', '--ay-logo-gap', 0, 60, 1, 10 );
		$this->color( 'mark_color', 'رنگ نشان', '--ay-logo-color' );
		$this->typo( 'name_t', 'تایپوگرافی نام', '.ay-logo-name' );
		$this->color( 'name_c', 'رنگ نام', array( '.ay-logo-name' => 'color' ) );
		$this->done();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$name = '' !== trim( (string) $s['name'] ) ? $s['name'] : ay_opt( 'brand' );
		$mark = '';
		if ( 'mark' === $s['source'] ) {
			$mark = '<span class="ay-logo-mark" aria-hidden="true"></span>';
		} elseif ( 'image' === $s['source'] && ! empty( $s['image']['url'] ) ) {
			$mark = '<img class="ay-logo-img" src="' . esc_url( $s['image']['url'] ) . '" alt="' . ( 'yes' === $s['name_on'] ? '' : esc_attr( $name ) ) . '">';
		}
		$inner = $mark . ( 'yes' === $s['name_on'] ? '<span class="ay-logo-name">' . esc_html( $name ) . '</span>' : '' );
		if ( '' === $inner ) {
			$this->empty_note( 'نشان یا نام را روشن کنید.' );
			return;
		}
		echo '<div class="ay-logo-wrap">';
		if ( 'yes' === $s['link'] ) {
			echo '<a class="ay-logo" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr( $name . ', home' ) . '">' . $inner . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<span class="ay-logo">' . $inner . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}
}

/* =========================================================================
 * Menu
 * ====================================================================== */
class Nav extends Base {

	public function get_name() {
		return 'ay-nav';
	}

	public function get_title() {
		return 'منوی آرزو';
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	protected function register_controls() {
		$menus = array( '' => 'فهرست جایگاه «منوی اصلی»' );
		foreach ( wp_get_nav_menus() as $m ) {
			$menus[ $m->term_id ] = $m->name;
		}
		$this->sec( 's_main', 'منو' );
		$this->sel( 'menu', 'فهرست', $menus, '', array( 'description' => 'فهرست‌ها را در «نمایش ← فهرست‌ها» می‌سازید و ویرایش می‌کنید.' ) );
		$this->sel( 'fx', 'موشن لینک‌ها', Archive::fx_options(), 'line' );
		$this->sel( 'dir', 'چیدمان', array( 'row' => 'افقی', 'column' => 'عمودی' ), 'row', array( 'selectors' => array( '{{WRAPPER}} .ay-nav ul' => 'flex-direction: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'justify', array( 'label' => 'تراز', 'type' => CM::SELECT, 'default' => 'flex-end', 'options' => array( 'flex-start' => 'چپ', 'center' => 'وسط', 'flex-end' => 'راست', 'space-between' => 'پخش در عرض' ), 'selectors' => array( '{{WRAPPER}} .ay-nav ul' => 'justify-content: {{VALUE}};' ) ) );
		$this->cssvar( 'gap', 'فاصلهٔ لینک‌ها', '--ay-nav-gap', 0, 80, 1, 34 );
		$this->done();
		$this->sec( 'st_main', 'لینک‌ها', true );
		$this->typo( 't', 'تایپوگرافی', '.ay-nav a' );
		$this->color( 'c', 'رنگ', array( '.ay-nav a' => 'color' ) );
		$this->color( 'ch', 'رنگ هنگام هاور و صفحهٔ فعلی', array( '.ay-nav a:hover, {{WRAPPER}} .ay-nav .current-menu-item > a' => 'color' ) );
		$this->color( 'fx_c', 'رنگ موشن', '--ay-fx-color' );
		$this->done();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$args = array( 'container' => false, 'depth' => 1, 'echo' => false, 'fallback_cb' => '__return_empty_string', 'items_wrap' => '<ul>%3$s</ul>', 'link_before' => '<span class="ay-fi-t">', 'link_after' => '</span>' );
		if ( ! empty( $s['menu'] ) ) {
			$args['menu'] = absint( $s['menu'] );
		} else {
			$args['theme_location'] = 'primary';
		}
		$html = wp_nav_menu( $args );
		if ( ! $html ) {
			$this->empty_note( 'فهرستی پیدا نشد. در «نمایش ← فهرست‌ها» یک فهرست بسازید یا آن را به جایگاه «منوی اصلی» بدهید.' );
			return;
		}
		$fx = isset( Archive::fx_options()[ $s['fx'] ] ) ? $s['fx'] : 'line';
		echo '<nav class="ay-nav ay-fx-' . esc_attr( $fx ) . '" aria-label="Main">' . $html . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/* =========================================================================
 * Bottom bar (dock)
 * ====================================================================== */
class Dock extends Base {

	public function get_name() {
		return 'ay-dock';
	}

	public function get_title() {
		return 'نوار پایین آرزو';
	}

	public function get_icon() {
		return 'eicon-footer';
	}

	public static function default_items() {
		$page = function ( $slug ) {
			$p = get_page_by_path( $slug );
			return $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
		};
		return array(
			array( 'label' => 'Home', 'icon' => 'home', 'link' => array( 'url' => home_url( '/' ) ) ),
			array( 'label' => 'Collections', 'icon' => 'gem', 'link' => array( 'url' => $page( 'collections' ) ) ),
			array( 'label' => 'About Me', 'icon' => 'user', 'link' => array( 'url' => $page( 'about-me' ) ) ),
			array( 'label' => 'Contact', 'icon' => 'mail', 'link' => array( 'url' => $page( 'contact' ) ) ),
		);
	}

	protected function register_controls() {
		$icons = array( '' => 'بدون آیکون' );
		foreach ( array_keys( ay_icons() ) as $k ) {
			$icons[ $k ] = $k;
		}
		$this->sec( 's_items', 'لینک‌ها' );
		$rep = new Repeater();
		$rep->add_control( 'label', array( 'label' => 'متن', 'type' => CM::TEXT, 'default' => 'Link' ) );
		$rep->add_control( 'icon', array( 'label' => 'آیکون (در موبایل نمایش داده می‌شود)', 'type' => CM::SELECT, 'options' => $icons, 'default' => 'gem' ) );
		$rep->add_control( 'link', array( 'label' => 'لینک', 'type' => CM::URL, 'dynamic' => array( 'active' => false ) ) );
		$this->add_control( 'items', array( 'label' => 'لینک‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::default_items(), 'title_field' => '{{{ label }}}' ) );
		$this->sw( 'social', 'آیکون‌های اینستاگرام، تلگرام و ایمیل', 'yes', array( 'description' => 'نشانی‌ها از «آرزو ← تنظیمات» خوانده می‌شوند.' ) );
		$this->sw( 'totop', 'دکمهٔ برگشت به بالا', 'yes' );
		$this->done();

		$this->sec( 's_behave', 'جا و رفتار' );
		$this->sel( 'pos', 'جایگاه', array( 'fixed' => 'ثابت پایین صفحه (شناور)', 'static' => 'معمولی، در جای خودش' ), 'fixed', array( 'prefix_class' => 'ay-dock-' ) );
		$this->cssvar( 'offset', 'فاصله از پایین صفحه', '--ay-dk-offset', 0, 80, 1, 14, 'px', array( 'condition' => array( 'pos' => 'fixed' ) ) );
		$this->sw( 'hide_footer', 'پنهان شدن نزدیک فوتر (در دسکتاپ)', 'yes' );
		$this->sw( 'mobile_menu', 'در موبایل منوی اصلی باشد (آیکون + متن، تمام‌عرض)', 'yes', array( 'prefix_class' => 'ay-dock-mm-' ) );
		$this->add_control( 'z', array( 'label' => 'ترتیب لایه (z-index)', 'type' => CM::NUMBER, 'min' => 1, 'max' => 9999, 'default' => 55, 'selectors' => array( '{{WRAPPER}}' => '--ay-dk-z: {{VALUE}};' ) ) );
		$this->done();

		$this->sec( 'st_glass', 'شیشه', true );
		$this->color( 'g_color', 'رنگ شیشه', '--ay-g-color' );
		$this->cssvar( 'g_op', 'پوشش رنگ', '--ay-g-op', 0, 1, 0.02, 0.76, '', array(), false );
		$this->cssvar( 'g_blur', 'تاری پشت شیشه', '--ay-g-blur', 0, 40, 1, 16, 'px', array(), false );
		$this->color( 'g_line', 'رنگ خط دور', '--ay-g-line' );
		$this->cssvar( 'g_radius', 'گردی گوشه‌ها', '--ay-dk-radius', 0, 60, 1, 999 );
		$this->cssvar( 'g_shadow', 'شدت سایه', '--ay-g-sh', 0, 1, 0.05, null, '', array(), false );
		$this->done();
		$this->sec( 'st_links', 'لینک‌ها', true );
		$this->typo( 't', 'تایپوگرافی', '.ay-dock-links a' );
		$this->color( 'c', 'رنگ', array( '.ay-dock-links a' => 'color' ) );
		$this->color( 'ch', 'رنگ هاور و صفحهٔ فعلی', array( '.ay-dock-links a:hover, {{WRAPPER}} .ay-dock-links a.is-on' => 'color' ) );
		$this->color( 'ic', 'رنگ آیکون‌ها', array( '.ay-dock-social a, {{WRAPPER}} .ay-dock-social button' => 'color' ) );
		$this->cssvar( 'gap', 'فاصلهٔ لینک‌ها', '--ay-dk-gap', 0, 60, 1, 22 );
		$this->done();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<nav class="ay-dock" aria-label="Quick links" data-ay-dock="' . ( 'yes' === $s['hide_footer'] ? '1' : '0' ) . '"><div class="ay-dock-links">';
		$here = trailingslashit( strtok( home_url( add_query_arg( null, null ) ), '?' ) );
		foreach ( (array) $s['items'] as $it ) {
			$url = ! empty( $it['link']['url'] ) ? $it['link']['url'] : '#';
			$on  = trailingslashit( $url ) === $here;
			echo '<a href="' . esc_url( $url ) . '"' . ( $on ? ' class="is-on" aria-current="page"' : '' ) . '>' . ( ! empty( $it['icon'] ) ? ay_icon( $it['icon'], 21 ) : '' ) . '<span>' . esc_html( $it['label'] ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
		$social = '';
		if ( 'yes' === $s['social'] ) {
			foreach ( ay_contact() as $key => $c ) {
				$social .= '<a href="' . esc_url( $c['url'] ) . '"' . ( 'email' === $key ? '' : ' target="_blank" rel="noopener"' ) . ' aria-label="' . esc_attr( $c['label'] ) . '">' . ay_icon( $c['icon'], 20 ) . '</a>';
			}
		}
		if ( 'yes' === $s['totop'] ) {
			$social .= '<button type="button" class="ay-totop" aria-label="Back to top">' . ay_icon( 'up', 20 ) . '</button>';
		}
		if ( $social ) {
			echo '<span class="ay-dock-sep" aria-hidden="true"></span><div class="ay-dock-social">' . $social . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</nav>';
	}
}

/* =========================================================================
 * Contact details
 * ====================================================================== */
class Contact extends Base {

	public function get_name() {
		return 'ay-contact';
	}

	public function get_title() {
		return 'راه‌های تماس آرزو';
	}

	public function get_icon() {
		return 'eicon-mail';
	}

	protected function register_controls() {
		$this->sec( 's_main', 'راه‌های تماس' );
		$this->note( 'n', 'ایمیل، اینستاگرام و تلگرام از «آرزو ← تنظیمات» خوانده می‌شوند؛ یک بار آن‌جا عوض کنید تا همه‌جای سایت عوض شود. شماره تلفن عمداً وجود ندارد.' );
		$this->sw( 'email', 'ایمیل', 'yes' );
		$this->sw( 'instagram', 'اینستاگرام', 'yes' );
		$this->sw( 'telegram', 'تلگرام', 'yes' );
		$this->sel( 'layout', 'چیدمان', array( 'cols' => 'ستونی کنار هم', 'rows' => 'ردیفی زیر هم', 'inline' => 'یک خط فشرده (برای فوتر)' ), 'cols', array( 'prefix_class' => 'ay-ctl-' ) );
		$this->sw( 'icons', 'نمایش آیکون', 'yes' );
		$this->sw( 'kinds', 'نمایش عنوان هر راه (Email, Instagram…)', 'yes' );
		$this->sw( 'actions', 'نمایش دکمهٔ Copy / Open', 'yes' );
		$this->txt( 'copy', 'متن دکمهٔ کپی', 'Copy', array( 'label_block' => false, 'condition' => array( 'actions' => 'yes' ) ) );
		$this->txt( 'copied', 'متن بعد از کپی', 'Copied', array( 'label_block' => false, 'condition' => array( 'actions' => 'yes' ) ) );
		$this->txt( 'open', 'متن دکمهٔ باز کردن', 'Open', array( 'label_block' => false, 'condition' => array( 'actions' => 'yes' ) ) );
		$this->done();
		$this->sec( 'st_main', 'ظاهر', true );
		$this->color( 'line', 'رنگ خط‌های جداکننده', '--ay-ct-line' );
		$this->cssvar( 'icon', 'اندازهٔ آیکون', '--ay-ct-icon', 12, 80, 1, 30 );
		$this->color( 'icon_c', 'رنگ آیکون', array( '.ay-ct-ic' => 'color' ) );
		$this->text_style( 'kind', 'عنوان', '.ay-ct-kind' );
		$this->text_style( 'val', 'نشانی', '.ay-ct-val', true );
		$this->text_style( 'act', 'دکمه', '.ay-ct-act', true );
		$this->cssvar( 'pad', 'فاصلهٔ داخلی هر مورد', '--ay-ct-pad', 0, 80, 1, 30 );
		$this->done();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array();
		foreach ( ay_contact() as $key => $c ) {
			if ( 'yes' === $s[ $key ] ) {
				$items[ $key ] = $c;
			}
		}
		if ( ! $items ) {
			$this->empty_note( 'هیچ راه تماسی روشن نیست یا نشانی‌ها در «آرزو ← تنظیمات» خالی‌اند.' );
			return;
		}
		echo '<ul class="ay-ct">';
		foreach ( $items as $key => $c ) {
			echo '<li class="ay-ct-item">';
			if ( 'yes' === $s['icons'] ) {
				echo '<span class="ay-ct-ic" aria-hidden="true">' . ay_icon( $c['icon'], 30 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			if ( 'yes' === $s['kinds'] ) {
				echo '<span class="ay-ct-kind">' . esc_html( $c['label'] ) . '</span>';
			}
			echo '<a class="ay-ct-val" href="' . esc_url( $c['url'] ) . '"' . ( 'email' === $key ? '' : ' target="_blank" rel="noopener"' ) . '>' . esc_html( $c['value'] ) . '</a>';
			if ( 'yes' === $s['actions'] ) {
				if ( 'email' === $key ) {
					echo '<button type="button" class="ay-ct-act" data-ay-copy="' . esc_attr( $c['value'] ) . '" data-done="' . esc_attr( $s['copied'] ) . '">' . esc_html( $s['copy'] ) . '</button>';
				} else {
					echo '<a class="ay-ct-act" href="' . esc_url( $c['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $s['open'] ) . '</a>';
				}
			}
			echo '</li>';
		}
		echo '</ul>';
	}
}

/* =========================================================================
 * Sketch motif
 * ====================================================================== */
class Sketch extends Base {

	public function get_name() {
		return 'ay-sketch';
	}

	public function get_title() {
		return 'طرح اسکچ آرزو';
	}

	public function get_icon() {
		return 'eicon-brush';
	}

	protected function register_controls() {
		$opts = ay_wallpaper_motifs();
		$opts['custom'] = 'تصویر دلخواه';
		$this->sec( 's_main', 'اسکچ' );
		$this->sel( 'motif', 'طرح', $opts, 'botanical-necklace' );
		$this->media( 'image', 'تصویر (PNG یا WebP بدون پس‌زمینه)', '', array( 'condition' => array( 'motif' => 'custom' ) ) );
		$this->sw( 'tint', 'رنگ‌پذیر (خط‌ها هم‌رنگ متن یا رنگ دلخواه)', 'yes', array( 'description' => 'روشن: فقط شکل خط‌ها از تصویر گرفته می‌شود و رنگ را شما تعیین می‌کنید. خاموش: خود تصویر با رنگ‌های خودش.' ) );
		$this->sw( 'behind', 'پشت محتوای کانتینر والد قرار بگیرد', '', array( 'prefix_class' => 'ay-sk-behind-' ) );
		$this->cssvar( 'x', 'جای افقی', '--ay-sk-x', -50, 150, 1, 50, '%', array( 'condition' => array( 'behind' => 'yes' ) ) );
		$this->cssvar( 'y', 'جای عمودی', '--ay-sk-y', -50, 150, 1, 50, '%', array( 'condition' => array( 'behind' => 'yes' ) ) );
		$this->cssvar( 'size', 'عرض', '--ay-sk-size', 40, 1600, 5, 420 );
		$this->align( 'align', '.ay-sk-wrap', 'text-align' );
		$this->sel( 'motion', 'حرکت', array( '' => 'ثابت', 'float' => 'شناور آرام', 'spin' => 'چرخش خیلی آهسته', 'breathe' => 'نفس کشیدن', 'draw' => 'ظاهر شدن مثل کشیده شدن' ), '', array( 'prefix_class' => 'ay-skw-' ) );
		$this->cssvar( 'dur', 'مدت یک دور حرکت (ثانیه)', '--ay-sk-dur', 2, 120, 1, 18, '', array( 'condition' => array( 'motion!' => '' ) ), false );
		$this->sw( 'hide_mobile', 'پنهان در موبایل', '', array( 'prefix_class' => 'ay-sk-hm-' ) );
		$this->done();
		$this->sec( 'st_main', 'ظاهر', true );
		$this->color( 'color', 'رنگ خط‌ها', '--ay-sk-color', array( 'condition' => array( 'tint' => 'yes' ) ) );
		$this->cssvar( 'op', 'میزان دیده شدن', '--ay-sk-op', 0, 1, 0.01, 0.5, '', array(), false );
		$this->cssvar( 'rot', 'چرخش', '--ay-sk-rot', -180, 180, 1, 0, 'deg', array(), false );
		$this->sw( 'flip', 'قرینهٔ افقی', '', array( 'prefix_class' => 'ay-sk-flip-' ) );
		$this->cssvar( 'blur', 'تاری', '--ay-sk-blur', 0, 12, 0.5, 0, 'px', array(), false );
		$this->done();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'custom' === $s['motif'] ) {
			$url = isset( $s['image']['url'] ) ? $s['image']['url'] : '';
			$dim = ! empty( $s['image']['id'] ) ? wp_get_attachment_metadata( absint( $s['image']['id'] ) ) : array();
		} else {
			$url = isset( ay_wallpaper_motifs()[ $s['motif'] ] ) ? ay_asset( 'img/sketch/' . $s['motif'] . '.webp' ) : '';
			$dim = array();
			$abs = AY_DIR . 'assets/img/sketch/' . $s['motif'] . '.webp';
			if ( $url && file_exists( $abs ) ) {
				$size = getimagesize( $abs );
				$dim  = $size ? array( 'width' => $size[0], 'height' => $size[1] ) : array();
			}
		}
		if ( ! $url ) {
			$this->empty_note( 'یک طرح یا تصویر انتخاب کنید.' );
			return;
		}
		$ratio = ( ! empty( $dim['width'] ) && ! empty( $dim['height'] ) ) ? $dim['width'] / $dim['height'] : 1;
		echo '<div class="ay-sk-wrap" aria-hidden="true">';
		if ( 'yes' === $s['tint'] ) {
			echo '<span class="ay-sk" style="--ay-sk-img:url(' . esc_url( $url ) . ');aspect-ratio:' . esc_attr( round( $ratio, 4 ) ) . '"></span>';
		} else {
			echo '<img class="ay-sk ay-sk-img" src="' . esc_url( $url ) . '" alt="" loading="lazy">';
		}
		echo '</div>';
	}
}
