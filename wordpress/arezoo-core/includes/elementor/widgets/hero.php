<?php
/**
 * «صحنهٔ هیرو آرزو» — a living background for a hero container.
 * Put it first inside a container and it fills that container behind the other widgets,
 * so the title, text and buttons stay ordinary, separately editable Elementor widgets.
 *
 * Four kinds of scene: one picture with moving regions, separate layers, your own video,
 * or a slideshow.
 *
 * @package ArezooCore
 */

namespace Arezoo\Widgets;

use Elementor\Controls_Manager as CM;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hero extends Base {

	public function get_name() {
		return 'ay-hero';
	}

	public function get_title() {
		return 'صحنهٔ هیرو آرزو';
	}

	public function get_icon() {
		return 'eicon-image-rollover';
	}

	public function get_script_depends(): array {
		return array( 'arezoo-hero', 'arezoo' );
	}

	/** The motion set-up made for the bundled «باغ شب» picture (all values in percent of the picture). */
	public static function night_regions() {
		$r = function ( $for, $label, $x, $y, $w, $h, $feather, $type, $amp, $speed, $px, $py, $phase, $depth ) {
			return array( 'label' => $label, 'on' => 'yes', 'for' => $for, 'x' => array( 'size' => $x, 'unit' => '%' ), 'y' => array( 'size' => $y, 'unit' => '%' ), 'w' => array( 'size' => $w, 'unit' => '%' ), 'h' => array( 'size' => $h, 'unit' => '%' ), 'feather' => array( 'size' => $feather ), 'type' => $type, 'amp' => array( 'size' => $amp ), 'speed' => array( 'size' => $speed ), 'px' => array( 'size' => $px, 'unit' => '%' ), 'py' => array( 'size' => $py, 'unit' => '%' ), 'phase' => array( 'size' => $phase ), 'depth' => array( 'size' => $depth ) );
		};
		return array(
			/* the wide picture (landscape screens) */
			$r( 'wide', 'قاصدک بزرگ و ساقه', 34, 68, 22, 60, 55, 'sway', 22, 0.55, 30, 96, 0, 0.6 ),
			$r( 'wide', 'کرک قاصدک بزرگ', 38, 49, 18, 26, 80, 'wave', 16, 1.1, 38, 49, 0, 0.3 ),
			$r( 'wide', 'قاصدک کوچک بالا', 19, 42, 9, 12, 75, 'wave', 14, 1, 19, 42, 1, 0.4 ),
			$r( 'wide', 'قاصدک کوچک پایین', 18, 67, 9, 13, 75, 'wave', 14, 0.9, 18, 67, 2, 0.4 ),
			$r( 'wide', 'دانهٔ ۱', 13, 32, 7, 9, 70, 'wave', 30, 0.8, 13, 32, 0, 1.2 ),
			$r( 'wide', 'دانهٔ ۲', 26, 40, 6, 10, 70, 'wave', 30, 0.7, 26, 40, 1.4, 1.2 ),
			$r( 'wide', 'دانهٔ ۳', 23, 52, 7, 8, 70, 'wave', 30, 0.9, 23, 52, 2.6, 1.2 ),
			$r( 'wide', 'دانهٔ ۴', 50, 48, 6, 8, 70, 'wave', 30, 0.75, 50, 48, 4, 1.2 ),
			$r( 'wide', 'دانهٔ ۵', 49, 64, 6, 6, 70, 'wave', 30, 0.85, 49, 64, 3.2, 1.2 ),
			$r( 'wide', 'شاخه و برگ‌های چپ', 48, 30, 34, 22, 60, 'sway', 20, 0.45, 60, 24, 1, 0.4 ),
			$r( 'wide', 'برگ‌های راست شاخه', 89, 30, 14, 22, 60, 'sway', 24, 0.5, 88, 24, 2, 0.4 ),
			$r( 'wide', 'غنچه‌های راست', 91, 22, 8, 8, 70, 'wave', 15, 0.9, 91, 22, 0, 0.3 ),
			$r( 'wide', 'گیاهان چپ', 8, 55, 16, 60, 60, 'sway', 14, 0.4, 6, 95, 0.6, 0.7 ),
			$r( 'wide', 'برگ‌های پایین چپ', 40, 85, 36, 26, 60, 'sway', 16, 0.4, 36, 97, 3, 0.9 ),
			$r( 'wide', 'گل و برگ‌های پایین راست', 88, 80, 24, 30, 60, 'sway', 18, 0.45, 90, 97, 1.5, 0.9 ),
			$r( 'wide', 'گردنبند', 67.5, 54, 30, 74, 35, 'sway', 8, 0.38, 70, 21, 0, 0.9 ),
			/* the 3:4 picture (upright tablets) */
			$r( 'tablet', 'تبلت · گردنبند', 65, 35, 52, 52, 35, 'sway', 7, 0.38, 66, 12, 0, 0.9 ),
			$r( 'tablet', 'تبلت · شاخهٔ بالا', 62, 11, 70, 14, 60, 'sway', 16, 0.45, 95, 12, 1, 0.4 ),
			$r( 'tablet', 'تبلت · غنچه‌ها', 90, 7, 12, 7, 70, 'wave', 15, 0.9, 90, 7, 0, 0.3 ),
			$r( 'tablet', 'تبلت · کرک قاصدک', 29, 27, 24, 16, 80, 'wave', 14, 1.1, 29, 27, 0, 0.3 ),
			$r( 'tablet', 'تبلت · ساقهٔ قاصدک', 22, 38, 30, 34, 55, 'sway', 18, 0.55, 8, 50, 0, 0.6 ),
			$r( 'tablet', 'تبلت · قاصدک کوچک ۱', 10, 18, 10, 7, 75, 'wave', 14, 1, 10, 18, 1, 0.4 ),
			$r( 'tablet', 'تبلت · قاصدک کوچک ۲', 18, 33, 9, 6, 75, 'wave', 14, 0.9, 18, 33, 2, 0.4 ),
			$r( 'tablet', 'تبلت · دانهٔ ۱', 18, 11, 8, 5, 70, 'wave', 28, 0.8, 18, 11, 0, 1.2 ),
			$r( 'tablet', 'تبلت · دانهٔ ۲', 52, 27, 8, 5, 70, 'wave', 28, 0.7, 52, 27, 1.4, 1.2 ),
			$r( 'tablet', 'تبلت · دانهٔ ۳', 41, 36, 8, 4, 70, 'wave', 28, 0.9, 41, 36, 2.6, 1.2 ),
			$r( 'tablet', 'تبلت · دانهٔ ۴', 93, 19, 8, 5, 70, 'wave', 28, 0.75, 93, 19, 4, 1.2 ),
			$r( 'tablet', 'تبلت · دانهٔ ۵', 84, 57, 7, 5, 70, 'wave', 28, 0.85, 84, 57, 3.2, 1.2 ),
			$r( 'tablet', 'تبلت · گیاهان چپ', 8, 36, 18, 44, 60, 'sway', 12, 0.4, 4, 58, 0.6, 0.7 ),
			$r( 'tablet', 'تبلت · گیاهان راست', 88, 70, 26, 60, 60, 'sway', 14, 0.42, 90, 98, 1.5, 0.9 ),
			$r( 'tablet', 'تبلت · قاصدک راست', 79, 64, 10, 7, 75, 'wave', 14, 1, 79, 64, 2, 0.4 ),
			/* the tall picture (phones) */
			$r( 'mobile', 'موبایل · گردنبند', 50, 38, 62, 54, 35, 'sway', 7, 0.38, 51, 14, 0, 0.9 ),
			$r( 'mobile', 'موبایل · شاخهٔ بالا', 50, 14, 100, 14, 60, 'sway', 14, 0.45, 98, 15, 1, 0.4 ),
			$r( 'mobile', 'موبایل · غنچه‌ها', 86, 10, 12, 6, 70, 'wave', 15, 0.9, 86, 10, 0, 0.3 ),
			$r( 'mobile', 'موبایل · کرک قاصدک چپ', 10, 39, 20, 12, 80, 'wave', 14, 1.1, 10, 39, 0, 0.3 ),
			$r( 'mobile', 'موبایل · ساقهٔ قاصدک', 8, 50, 16, 28, 55, 'sway', 16, 0.55, 2, 62, 0, 0.6 ),
			$r( 'mobile', 'موبایل · قاصدک راست', 91, 36, 12, 8, 75, 'wave', 14, 1, 91, 36, 1, 0.4 ),
			$r( 'mobile', 'موبایل · قاصدک کوچک', 21, 54, 9, 6, 75, 'wave', 14, 0.9, 21, 54, 2, 0.4 ),
			$r( 'mobile', 'موبایل · دانهٔ ۱', 36, 32, 9, 6, 70, 'wave', 28, 0.8, 36, 32, 0, 1.2 ),
			$r( 'mobile', 'موبایل · دانهٔ ۲', 16, 31, 8, 5, 70, 'wave', 28, 0.7, 16, 31, 1.4, 1.2 ),
			$r( 'mobile', 'موبایل · دانهٔ ۳', 87, 22, 8, 5, 70, 'wave', 28, 0.9, 87, 22, 2.6, 1.2 ),
			$r( 'mobile', 'موبایل · دانهٔ ۴', 82, 41, 8, 5, 70, 'wave', 28, 0.75, 82, 41, 4, 1.2 ),
			$r( 'mobile', 'موبایل · دانهٔ ۵', 22, 47, 7, 4, 70, 'wave', 28, 0.85, 22, 47, 3.2, 1.2 ),
			$r( 'mobile', 'موبایل · گیاهان چپ', 14, 62, 30, 26, 60, 'sway', 12, 0.4, 6, 74, 0.6, 0.7 ),
			$r( 'mobile', 'موبایل · گیاهان راست', 86, 56, 28, 36, 60, 'sway', 12, 0.42, 94, 72, 1.5, 0.9 ),
			$r( 'mobile', 'موبایل · گل راست', 78, 64, 22, 10, 60, 'sway', 16, 0.5, 84, 67, 2.2, 0.8 ),
		);
	}

	public static function night_sparks() {
		$out = array();
		$set = array(
			'wide'   => array( array( 64.5, 73.5 ), array( 69.5, 78.5 ), array( 75.5, 72 ), array( 61, 64 ), array( 77, 60 ), array( 66, 68 ), array( 71.5, 84 ), array( 57, 52 ), array( 81, 44 ) ),
			'tablet' => array( array( 60, 48 ), array( 69.5, 51.5 ), array( 78, 47 ), array( 52, 42 ), array( 84, 39 ), array( 63, 53 ), array( 72, 56 ), array( 46, 28 ), array( 88, 27 ) ),
			'mobile' => array( array( 42, 52 ), array( 54, 56 ), array( 65, 51 ), array( 31, 45 ), array( 72, 42 ), array( 46, 57 ), array( 58, 61 ), array( 24, 31 ), array( 77, 31 ) ),
		);
		foreach ( $set as $for => $pts ) {
			foreach ( $pts as $i => $p ) {
				$out[] = array( 'for' => $for, 'x' => array( 'size' => $p[0], 'unit' => '%' ), 'y' => array( 'size' => $p[1], 'unit' => '%' ), 'size' => array( 'size' => ( 'wide' === $for ? 1.3 : 2.2 ) - ( $i % 3 ) * ( 'wide' === $for ? 0.2 : 0.35 ) ), 'dur' => array( 'size' => 4.2 + ( $i % 4 ) * 0.6 ), 'delay' => array( 'size' => $i * 0.7 ) );
			}
		}
		return $out;
	}

	public static function night_floats() {
		$out = array();
		/* [ seed, x, y, width, path x, path y, turn, seconds, delay ] in percent of each picture; one set per picture */
		$sets = array(
			'wide'   => array( array( 'a', 36, 44, 4.5, 34, -24, 50, 34, 0 ), array( 'c', 40, 52, 4, 40, -10, -40, 42, 12 ), array( 'd', 34, 58, 5, 46, 6, 70, 38, 22 ), array( 'b', 32, 40, 5, 28, -30, -60, 46, 30 ), array( 'c', 38, 48, 3.5, 50, -18, 90, 52, 40 ) ),
			'tablet' => array( array( 'a', 30, 26, 7, 40, -22, 50, 30, 0 ), array( 'c', 33, 30, 6, 50, -8, -40, 38, 10 ), array( 'd', 26, 33, 8, 56, 10, 70, 34, 19 ), array( 'b', 82, 62, 6.5, -40, -30, -60, 40, 26 ), array( 'c', 24, 22, 5.5, 60, -14, 90, 46, 34 ) ),
			'mobile' => array( array( 'a', 12, 38, 10, 60, -18, 50, 26, 0 ), array( 'c', 16, 42, 8, 70, -6, -40, 32, 9 ), array( 'd', 90, 35, 10, -70, -16, 70, 30, 16 ), array( 'b', 22, 53, 9, 64, -24, -60, 36, 23 ), array( 'c', 86, 40, 7, -60, 8, 90, 40, 30 ) ),
		);
		foreach ( $sets as $for => $list ) {
			foreach ( $list as $f ) {
				$out[] = array( 'for' => $for, 'image' => array( 'url' => ay_asset( 'img/hero/seed-' . $f[0] . '.png' ) ), 'x' => array( 'size' => $f[1], 'unit' => '%' ), 'y' => array( 'size' => $f[2], 'unit' => '%' ), 'w' => array( 'size' => $f[3], 'unit' => '%' ), 'dx' => array( 'size' => $f[4] ), 'dy' => array( 'size' => $f[5] ), 'rot' => array( 'size' => $f[6] ), 'dur' => array( 'size' => $f[7] ), 'delay' => array( 'size' => $f[8] ), 'opacity' => array( 'size' => 0.85 ) );
			}
		}
		return $out;
	}

	/** The bundled pictures. [ family => [ [ file, width, height ] … ] ] */
	public static function families() {
		return array(
			'wide'   => array( array( 'hero2-wide.webp', 1672, 941 ) ),
			'tablet' => array( array( 'hero2-portrait.webp', 1086, 1448 ) ),
			'mobile' => array( array( 'hero2-tall.webp', 992, 1586 ) ),
		);
	}

	public static function family_default( $family ) {
		$f = self::families();
		return 'img/hero/' . $f[ $family ][0][0];
	}

	/** Earlier bundled pictures are answered with today's picture of the same kind. */
	private static function family_alias( $key ) {
		if ( false !== strpos( $key, 'night-garden' ) || preg_match( '/hero-(lg|xl)\./', $key ) ) {
			return 'hero2-wide.webp';
		}
		if ( false !== strpos( $key, 'hero-tablet-' ) ) {
			return 'hero2-portrait.webp';
		}
		if ( false !== strpos( $key, 'hero-mobile-' ) ) {
			return 'hero2-tall.webp';
		}
		return $key;
	}

	/** [ sizes, width, height ] of one picture setting; bundled pictures bring all their sizes. */
	private function variant_source( $media ) {
		$url = isset( $media['url'] ) ? (string) $media['url'] : '';
		if ( '' === $url ) {
			return null;
		}
		$id  = isset( $media['id'] ) ? absint( $media['id'] ) : 0;
		$rel = $id ? (string) get_post_meta( $id, '_ay_asset', true ) : '';
		$key = $rel ? basename( $rel ) : basename( (string) wp_parse_url( $url, PHP_URL_PATH ) );
		$key = self::family_alias( $key );
		foreach ( self::families() as $files ) {
			foreach ( $files as $f ) {
				if ( $f[0] === $key ) {
					$set = array();
					foreach ( $files as $g ) {
						$set[] = array( ay_asset( 'img/hero/' . $g[0] ), $g[1] );
					}
					return array( $set, $f[1], $f[2], true );
				}
			}
		}
		list( $w, $h ) = $this->media_dims( $media );
		return array( array( array( esc_url_raw( $url ), $w ) ), $w, $h );
	}

	private function rs( Repeater $rep, $id, $label, $min, $max, $step, $default, $unit = '' ) {
		$u = '' === $unit ? 'px' : $unit;
		$rep->add_control( $id, array( 'label' => $label, 'type' => CM::SLIDER, 'size_units' => array( $u ), 'range' => array( $u => array( 'min' => $min, 'max' => $max, 'step' => $step ) ), 'default' => array( 'unit' => $u, 'size' => $default ) ) );
	}

	protected function register_controls() {
		$regions = array( 'mode' => 'regions' );
		$layers  = array( 'mode' => 'layers' );
		$video   = array( 'mode' => 'video' );
		$slides  = array( 'mode' => 'slideshow' );

		/* ---- scene ---- */
		$this->sec( 's_scene', 'صحنه' );
		$this->sel( 'mode', 'نوع صحنه', array( 'regions' => 'یک عکس با ناحیه‌های متحرک', 'layers' => 'لایه‌های جدا (هر لایه حرکت خودش)', 'video' => 'ویدیوی خودتان', 'slideshow' => 'اسلایدشوی چند عکس' ), 'regions' );
		$this->sw( 'fill', 'پر کردن کانتینر والد، پشت بقیهٔ ویجت‌ها', 'yes', array( 'prefix_class' => 'ay-fill-', 'description' => 'روشن: این ویجت پس‌زمینهٔ کانتینر می‌شود و متن و دکمه‌ها را به‌صورت ویجت‌های عادی روی آن می‌گذارید. خاموش: یک قاب مستقل با ارتفاع دلخواه.' ) );
		$this->cssvar( 'height', 'ارتفاع قاب', '--ay-hero-h', 20, 1200, 1, 70, array( 'vh', 'px' ), array( 'condition' => array( 'fill!' => 'yes' ) ) );
		$this->cssvar( 'fill_h', 'ارتفاع صحنه داخل کانتینر', '--ay-hero-fh', 10, 1200, 1, 100, array( '%', 'vh', 'px' ), array( 'condition' => array( 'fill' => 'yes' ), 'description' => 'در موبایل می‌توانید صحنه را فقط در بخش بالایی کانتینر نشان دهید تا متن زیر آن بیاید.' ) );
		$this->media( 'image', 'عکس', ay_asset( self::family_default( 'wide' ) ), array( 'condition' => array( 'mode' => array( 'regions', 'video' ) ), 'description' => 'در حالت ویدیو، این عکس تا بارگذاری ویدیو و وقتی حرکت خاموش است نمایش داده می‌شود. بهترین اندازه: ۳۸۴۰×۲۱۶۰ پیکسل.' ) );
		$this->media( 'image_tablet', 'عکس عمودی تبلت (نسبت ۳:۴)', ay_asset( self::family_default( 'tablet' ) ), array( 'condition' => $regions, 'description' => 'اختیاری. در صفحه‌های عمودیِ پهن (آیپد، سرفیس) نمایش داده می‌شود.' ) );
		$this->media( 'image_mobile', 'عکس عمودی موبایل (بلندتر)', ay_asset( self::family_default( 'mobile' ) ), array( 'condition' => $regions, 'description' => 'اختیاری. در صفحه‌های عمودیِ باریک (گوشی) نمایش داده می‌شود.' ) );
		$safe = function ( $id, $label, $d ) use ( $regions ) {
			$this->add_control( $id, array( 'label' => $label, 'type' => CM::DIMENSIONS, 'size_units' => array( '%' ), 'allowed_dimensions' => 'all', 'default' => array( 'unit' => '%', 'top' => (string) $d[0], 'right' => (string) $d[1], 'bottom' => (string) $d[2], 'left' => (string) $d[3], 'isLinked' => false ), 'condition' => $regions ) );
		};
		$zone = function ( $id, $label, $d ) use ( $regions ) {
			$this->sel( $id, $label, array( 'left' => 'متن سمت چپِ جواهر (عرض متن خودکار محدود می‌شود)', 'right' => 'متن سمت راستِ جواهر', 'below' => 'متن زیر جواهر (عکس دقیقاً هم‌عرض صفحه)', 'none' => 'آزاد (بدون محافظت)' ), $d, array( 'condition' => $regions ) );
		};
		$this->head( 'safe_h', 'جواهر و جای متن' );
		$this->note( 'safe_n', 'برای هر عکس بنویسید جواهر از هر لبه چند درصد فاصله دارد (کادر جواهر). متن هیچ‌وقت روی این کادر نمی‌افتد: در حالت «کنار» عرض متن به اندازهٔ فضای خالیِ کنار جواهر محدود می‌شود و در حالت «زیر» متن از پایینِ جواهر شروع می‌شود. جواهر هم هرگز بریده نمی‌شود.' );
		$safe( 'safe', 'فاصلهٔ جواهر از لبه‌ها · عکس عریض', array( 12, 17, 9, 53 ) );
		$zone( 'zone', 'جای متن · عکس عریض', 'left' );
		$safe( 'safe_t', 'فاصلهٔ جواهر از لبه‌ها · عکس تبلت', array( 6, 10, 39, 41 ) );
		$zone( 'zone_t', 'جای متن · عکس تبلت', 'below' );
		$safe( 'safe_m', 'فاصلهٔ جواهر از لبه‌ها · عکس موبایل', array( 8, 20, 35, 20 ) );
		$zone( 'zone_m', 'جای متن · عکس موبایل', 'below' );
		$this->txt( 'alt', 'توضیح تصویر (برای صفحه‌خوان‌ها)', '', array( 'description' => 'اگر تصویر فقط تزئینی است خالی بگذارید.' ) );
		$this->add_responsive_control( 'focus_x', array( 'label' => 'نقطهٔ تمرکز افقی', 'type' => CM::SLIDER, 'range' => array( '%' => array( 'min' => 0, 'max' => 100 ) ), 'size_units' => array( '%' ), 'default' => array( 'unit' => '%', 'size' => 68 ), 'description' => 'وقتی قاب باریک‌تر از عکس است، این بخش از عکس در دید می‌ماند.' ) );
		$this->add_responsive_control( 'focus_y', array( 'label' => 'نقطهٔ تمرکز عمودی', 'type' => CM::SLIDER, 'range' => array( '%' => array( 'min' => 0, 'max' => 100 ) ), 'size_units' => array( '%' ), 'default' => array( 'unit' => '%', 'size' => 50 ) ) );
		$this->done();

		/* ---- motion (all modes) ---- */
		$this->sec( 's_motion', 'حرکت کلی' );
		$this->add_control( 'speed', array( 'label' => 'سرعت کلی', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 3, 'step' => 0.05 ) ), 'default' => array( 'size' => 1 ), 'description' => 'صفر یعنی تصویر کاملاً ثابت.' ) );
		$this->add_control( 'amp', array( 'label' => 'شدت کلی', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 3, 'step' => 0.05 ) ), 'default' => array( 'size' => 1 ) ) );
		$this->sw( 'mouse', 'واکنش به حرکت موس', 'yes' );
		$this->add_control( 'mouse_amt', array( 'label' => 'میزان واکنش به موس', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 3, 'step' => 0.05 ) ), 'default' => array( 'size' => 1 ), 'condition' => array( 'mouse' => 'yes' ) ) );
		$this->sw( 'motion_mobile', 'حرکت در موبایل', 'yes' );
		$this->sw( 'pause_off', 'توقف وقتی از دید خارج است', 'yes' );
		$this->done();

		/* ---- regions ---- */
		$this->sec( 's_regions', 'ناحیه‌های متحرک', false, $regions );
		$this->note( 'n_regions', 'هر ناحیه یک بیضی نرم روی عکس است که تاب می‌خورد، موج برمی‌دارد یا نفس می‌کشد. خود عکس تغییر نمی‌کند و با شدت صفر دقیقاً همان عکس است.' );
		$this->sw( 'outline', 'نمایش ناحیه‌ها در ویرایشگر', 'yes', array( 'description' => 'فقط هنگام ویرایش دیده می‌شود.' ) );
		$rep = new Repeater();
		$rep->add_control( 'label', array( 'label' => 'نام ناحیه', 'type' => CM::TEXT, 'default' => 'ناحیه' ) );
		$rep->add_control( 'for', array( 'label' => 'برای کدام عکس', 'type' => CM::SELECT, 'default' => 'wide', 'options' => array( 'wide' => 'عکس اصلی (عریض)', 'tablet' => 'عکس تبلت', 'mobile' => 'عکس موبایل' ) ) );
		$rep->add_control( 'on', array( 'label' => 'فعال', 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => 'yes' ) );
		$rep->add_control( 'type', array( 'label' => 'نوع حرکت', 'type' => CM::SELECT, 'default' => 'sway', 'options' => array( 'sway' => 'تاب خوردن دور یک نقطه', 'wave' => 'موج ریز', 'breathe' => 'نفس کشیدن' ) ) );
		$this->rs( $rep, 'x', 'مرکز افقی', 0, 100, 0.5, 50, '%' );
		$this->rs( $rep, 'y', 'مرکز عمودی', 0, 100, 0.5, 50, '%' );
		$this->rs( $rep, 'w', 'عرض ناحیه', 1, 150, 0.5, 20, '%' );
		$this->rs( $rep, 'h', 'ارتفاع ناحیه', 1, 150, 0.5, 20, '%' );
		$this->rs( $rep, 'feather', 'نرمی لبه', 1, 100, 1, 60 );
		$this->rs( $rep, 'amp', 'شدت', 0, 100, 1, 20 );
		$this->rs( $rep, 'speed', 'سرعت', 0, 3, 0.05, 0.5 );
		$this->rs( $rep, 'phase', 'جلو و عقب بودن نسبت به بقیه', 0, 6.28, 0.1, 0 );
		$this->rs( $rep, 'px', 'نقطهٔ ثابت افقی (محور تاب)', -20, 120, 0.5, 50, '%' );
		$this->rs( $rep, 'py', 'نقطهٔ ثابت عمودی (محور تاب)', -20, 120, 0.5, 100, '%' );
		$this->rs( $rep, 'depth', 'واکنش به موس', 0, 3, 0.1, 0.5 );
		$this->add_control( 'regions', array( 'label' => 'ناحیه‌ها (برای هر عکس حداکثر ۱۶)', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::night_regions(), 'title_field' => '{{{ label }}}' ) );
		$this->done();

		$this->sec( 's_sparks', 'نقطه‌های درخشش', false, $regions );
		$this->sw( 'sparks_on', 'نمایش درخشش‌ها', 'yes' );
		$rep = new Repeater();
		$rep->add_control( 'for', array( 'label' => 'برای کدام عکس', 'type' => CM::SELECT, 'default' => 'wide', 'options' => array( 'wide' => 'عکس اصلی (عریض)', 'tablet' => 'عکس تبلت', 'mobile' => 'عکس موبایل' ) ) );
		$this->rs( $rep, 'x', 'جای افقی', 0, 100, 0.1, 50, '%' );
		$this->rs( $rep, 'y', 'جای عمودی', 0, 100, 0.1, 50, '%' );
		$this->rs( $rep, 'size', 'اندازه', 0.2, 5, 0.1, 1 );
		$this->rs( $rep, 'dur', 'مدت یک چشمک (ثانیه)', 1, 20, 0.1, 5 );
		$this->rs( $rep, 'delay', 'تأخیر (ثانیه)', 0, 20, 0.1, 0 );
		$this->add_control( 'sparks', array( 'label' => 'درخشش‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::night_sparks(), 'title_field' => '{{{ for }}} · x: {{{ x.size }}} · y: {{{ y.size }}}', 'condition' => array( 'sparks_on' => 'yes' ) ) );
		$this->color( 'spark_color', 'رنگ درخشش', '--ay-spark', array( 'condition' => array( 'sparks_on' => 'yes' ) ) );
		$this->done();

		$this->sec( 's_floats', 'عناصر شناور', false, $regions );
		$this->sw( 'floats_on', 'نمایش عناصر شناور', 'yes' );
		$rep = new Repeater();
		$rep->add_control( 'for', array( 'label' => 'برای کدام عکس', 'type' => CM::SELECT, 'default' => 'all', 'options' => array( 'all' => 'همهٔ عکس‌ها (دسکتاپ، تبلت و موبایل)', 'wide' => 'فقط عکس اصلی (عریض)', 'tablet' => 'فقط عکس تبلت', 'mobile' => 'فقط عکس موبایل' ) ) );
		$rep->add_control( 'image', array( 'label' => 'تصویر (PNG بدون پس‌زمینه)', 'type' => CM::MEDIA ) );
		$this->rs( $rep, 'x', 'شروع افقی', -20, 120, 0.5, 20, '%' );
		$this->rs( $rep, 'y', 'شروع عمودی', -20, 120, 0.5, 40, '%' );
		$this->rs( $rep, 'w', 'عرض', 0.5, 40, 0.5, 6, '%' );
		$this->rs( $rep, 'dx', 'مسیر افقی (درصد عرض عکس)', -150, 150, 1, 40 );
		$this->rs( $rep, 'dy', 'مسیر عمودی (درصد عرض عکس)', -150, 150, 1, -20 );
		$this->rs( $rep, 'rot', 'چرخش در طول مسیر (درجه)', -360, 360, 5, 45 );
		$this->rs( $rep, 'dur', 'مدت (ثانیه)', 4, 120, 1, 36 );
		$this->rs( $rep, 'delay', 'تأخیر (ثانیه)', 0, 120, 1, 0 );
		$this->rs( $rep, 'opacity', 'شفافیت', 0, 1, 0.05, 0.85 );
		$this->add_control( 'floats', array( 'label' => 'عناصر شناور', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::night_floats(), 'title_field' => '{{{ for }}} · عنصر شناور', 'condition' => array( 'floats_on' => 'yes' ) ) );
		$this->done();

		/* ---- layers ---- */
		$this->sec( 's_layers', 'لایه‌ها', false, $layers );
		$this->note( 'n_layers', 'هر لایه یک تصویر بدون پس‌زمینه است. جای آن را با درصد نسبت به صحنه تعیین می‌کنید و برایش حرکت و عمق جدا می‌گذارید.' );
		$this->add_control( 'ratio', array( 'label' => 'نسبت صحنه (عرض ÷ ارتفاع)', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.4, 'max' => 3, 'step' => 0.01 ) ), 'default' => array( 'size' => 1.78 ) ) );
		$rep = new Repeater();
		$rep->add_control( 'label', array( 'label' => 'نام لایه', 'type' => CM::TEXT, 'default' => 'لایه' ) );
		$rep->add_control( 'image', array( 'label' => 'تصویر', 'type' => CM::MEDIA ) );
		$this->rs( $rep, 'x', 'جای افقی (لبهٔ چپ)', -50, 150, 0.5, 30, '%' );
		$this->rs( $rep, 'y', 'جای عمودی (لبهٔ بالا)', -50, 150, 0.5, 20, '%' );
		$this->rs( $rep, 'w', 'عرض', 1, 200, 0.5, 30, '%' );
		$rep->add_control( 'motion', array( 'label' => 'حرکت', 'type' => CM::SELECT, 'default' => 'sway', 'options' => array( '' => 'ثابت', 'sway' => 'تاب خوردن', 'bob' => 'بالا و پایین رفتن', 'float' => 'شناور چرخان', 'drift' => 'عبور از صحنه', 'pulse' => 'تپش', 'twinkle' => 'چشمک' ) ) );
		$this->rs( $rep, 'amt', 'مقدار حرکت', 0, 100, 1, 12 );
		$this->rs( $rep, 'dur', 'مدت یک دور (ثانیه)', 0.5, 120, 0.5, 8 );
		$this->rs( $rep, 'delay', 'تأخیر (ثانیه)', 0, 60, 0.5, 0 );
		$this->rs( $rep, 'ox', 'محور افقی حرکت', 0, 100, 1, 50, '%' );
		$this->rs( $rep, 'oy', 'محور عمودی حرکت', 0, 100, 1, 50, '%' );
		$this->rs( $rep, 'depth', 'عمق (واکنش به موس)', 0, 60, 1, 12 );
		$this->rs( $rep, 'opacity', 'شفافیت', 0, 1, 0.05, 1 );
		$rep->add_control( 'z', array( 'label' => 'ترتیب لایه', 'type' => CM::NUMBER, 'default' => 1, 'min' => 0, 'max' => 99 ) );
		$rep->add_control( 'hide_mobile', array( 'label' => 'پنهان در موبایل', 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'layers', array( 'label' => 'لایه‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => array(), 'title_field' => '{{{ label }}}' ) );
		$this->done();

		/* ---- video ---- */
		$this->sec( 's_video', 'ویدیو', false, $video );
		$this->add_control( 'video', array( 'label' => 'فایل ویدیو (MP4 یا WebM)', 'type' => CM::MEDIA, 'media_types' => array( 'video' ) ) );
		$this->add_control( 'video_mobile', array( 'label' => 'ویدیوی جدا برای موبایل (اختیاری)', 'type' => CM::MEDIA, 'media_types' => array( 'video' ) ) );
		$this->sw( 'v_loop', 'تکرار', 'yes' );
		$this->add_control( 'v_rate', array( 'label' => 'سرعت پخش', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.25, 'max' => 2, 'step' => 0.05 ) ), 'default' => array( 'size' => 1 ) ) );
		$this->num( 'v_start', 'شروع از ثانیهٔ', 0, 0, 3600, 0.5 );
		$this->num( 'v_end', 'پایان در ثانیهٔ (۰ = تا آخر)', 0, 0, 3600, 0.5 );
		$this->sel( 'v_fit', 'جا شدن در قاب', array( 'cover' => 'پر کردن قاب (برش لبه‌ها)', 'contain' => 'نمایش کامل' ), 'cover' );
		$this->note( 'n_video', 'ویدیو بی‌صدا و خودکار پخش می‌شود (مرورگرها پخش خودکار با صدا را اجازه نمی‌دهند).' );
		$this->done();

		/* ---- slideshow ---- */
		$this->sec( 's_slides', 'اسلایدشو', false, $slides );
		$this->add_control( 'slides', array( 'label' => 'عکس‌ها', 'type' => CM::GALLERY ) );
		$this->num( 'sl_time', 'ماندن هر عکس (ثانیه)', 5, 1, 60, 0.5 );
		$this->num( 'sl_fade', 'مدت محو شدن (ثانیه)', 1.6, 0.2, 10, 0.1 );
		$this->add_control( 'sl_ken', array( 'label' => 'زوم آرام هر عکس', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 30, 'step' => 1 ) ), 'default' => array( 'size' => 8 ) ) );
		$this->sel( 'sl_order', 'ترتیب', array( 'seq' => 'به ترتیب', 'rand' => 'تصادفی' ), 'seq' );
		$this->done();

		/* ---- style ---- */
		$this->sec( 'st_veil', 'پوشش روی تصویر', true );
		$this->sel( 'veil', 'سایهٔ خوانایی متن', array( '' => 'بدون سایه', 'left' => 'از چپ', 'right' => 'از راست', 'bottom' => 'از پایین', 'top' => 'از بالا', 'full' => 'روی کل تصویر' ), 'left', array( 'prefix_class' => 'ay-veil-' ) );
		$this->color( 'veil_color', 'رنگ سایه', '--ay-veil', array( 'condition' => array( 'veil!' => '' ) ) );
		$this->cssvar( 'veil_op', 'شدت سایه', '--ay-veil-op', 0, 1, 0.02, 0.84, '', array( 'condition' => array( 'veil!' => '' ) ), false );
		$this->cssvar( 'veil_size', 'گسترهٔ سایه', '--ay-veil-size', 10, 100, 1, 54, '%', array( 'condition' => array( 'veil!' => array( '', 'full' ) ) ) );
		$this->sw( 'fade', 'محو شدن پایین به رنگ صفحه', 'yes', array( 'prefix_class' => 'ay-fade-', 'separator' => 'before' ) );
		$this->color( 'fade_color', 'رنگ محو شدن', '--ay-fade', array( 'condition' => array( 'fade' => 'yes' ) ) );
		$this->cssvar( 'fade_size', 'ارتفاع محو شدن', '--ay-fade-size', 0, 60, 1, 18, '%', array( 'condition' => array( 'fade' => 'yes' ) ) );
		$this->done();

		$this->sec( 'st_media', 'تصویر', true );
		$this->filters( 'media_filters', 'فیلترهای تصویر', '.ay-hs-stage, {{WRAPPER}} .ay-hero-media' );
		$this->cssvar( 'media_op', 'شفافیت تصویر', '--ay-hero-op', 0, 1, 0.02, null, '', array(), false );
		$this->add_responsive_control( 'radius', array( 'label' => 'گردی گوشه‌ها', 'type' => CM::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .ay-hero' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->color( 'bg', 'رنگ پشت تصویر', array( '.ay-hero' => 'background-color' ) );
		$this->done();
	}

	private function pct( $v, $default ) {
		return ( is_array( $v ) && isset( $v['size'] ) && '' !== $v['size'] ) ? (float) $v['size'] : $default;
	}

	private function media_dims( $media ) {
		$id = isset( $media['id'] ) ? absint( $media['id'] ) : 0;
		if ( $id ) {
			$meta = wp_get_attachment_metadata( $id );
			if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
				return array( (int) $meta['width'], (int) $meta['height'] );
			}
		}
		foreach ( self::families() as $files ) {
			foreach ( $files as $f ) {
				if ( ! empty( $media['url'] ) && false !== strpos( $media['url'], $f[0] ) ) {
					return array( $f[1], $f[2] );
				}
			}
		}

		return array( 0, 0 );
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$mode = in_array( $s['mode'], array( 'regions', 'layers', 'video', 'slideshow' ), true ) ? $s['mode'] : 'regions';
		$url  = isset( $s['image']['url'] ) ? $s['image']['url'] : '';
		$edit = $this->in_editor();
		$f    = function ( $key, $def ) use ( $s ) {
			return array( $this->pct( $s[ $key ], $def ) / 100, $this->pct( isset( $s[ $key . '_tablet' ] ) ? $s[ $key . '_tablet' ] : null, -1 ) / 100, $this->pct( isset( $s[ $key . '_mobile' ] ) ? $s[ $key . '_mobile' ] : null, -1 ) / 100 );
		};
		$cfg = array(
			'mode'     => $mode,
			'alt'      => (string) $s['alt'],
			'fx'       => $f( 'focus_x', 68 ),
			'fy'       => $f( 'focus_y', 50 ),
			'speed'    => $this->pct( $s['speed'], 1 ),
			'amp'      => $this->pct( $s['amp'], 1 ),
			'mouse'    => 'yes' === $s['mouse'],
			'mouseAmt' => 0.006 * $this->pct( $s['mouse_amt'], 1 ),
			'mobile'   => 'yes' === $s['motion_mobile'],
			'pauseOff' => 'yes' === $s['pause_off'],
		);

		if ( 'regions' === $mode ) {
			if ( ! $url ) {
				$this->empty_note( 'یک عکس برای صحنهٔ هیرو انتخاب کنید.' );
				return;
			}
			$cfg['outline']  = $edit && 'yes' === $s['outline'];
			$cfg['variants'] = array();
			$factor          = array( 'sway' => 0.0005, 'wave' => 0.0001, 'breathe' => 0.0005 );
			foreach ( array( 'wide' => array( 'image', 'safe', 'zone' ), 'tablet' => array( 'image_tablet', 'safe_t', 'zone_t' ), 'mobile' => array( 'image_mobile', 'safe_m', 'zone_m' ) ) as $key => $ids ) {
				$src = $this->variant_source( isset( $s[ $ids[0] ] ) ? $s[ $ids[0] ] : array() );
				if ( ! $src ) {
					continue;
				}
				if ( 'wide' === $key ) {
					$own = empty( $src[3] );
				} elseif ( ! empty( $own ) && ! empty( $src[3] ) ) {
					continue;   /* the owner's own main picture is not mixed with the bundled tablet / phone pictures */
				}
				$sf   = isset( $s[ $ids[1] ] ) && is_array( $s[ $ids[1] ] ) ? $s[ $ids[1] ] : array();
				$safe = array();
				foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
					$safe[] = isset( $sf[ $side ] ) && is_numeric( $sf[ $side ] ) ? max( 0, min( 49, (float) $sf[ $side ] ) ) / 100 : 0;
				}
				$zn = isset( $s[ $ids[2] ] ) && in_array( $s[ $ids[2] ], array( 'left', 'right', 'below', 'none' ), true ) ? $s[ $ids[2] ] : ( 'wide' === $key ? 'left' : 'below' );
				$v  = array( 'k' => $key, 'set' => $src[0], 'w' => (int) $src[1], 'h' => (int) $src[2], 'safe' => $safe, 'zone' => $zn, 'regions' => array(), 'sparks' => array() );
				foreach ( (array) $s['regions'] as $r ) {
					if ( 'yes' !== ( isset( $r['on'] ) ? $r['on'] : 'yes' ) || ( isset( $r['for'] ) && '' !== $r['for'] ? $r['for'] : 'wide' ) !== $key || count( $v['regions'] ) >= 16 ) {
						continue;
					}
					$type           = isset( $factor[ $r['type'] ] ) ? $r['type'] : 'sway';
					$v['regions'][] = array(
						'x'     => $this->pct( $r['x'], 50 ) / 100,
						'y'     => $this->pct( $r['y'], 50 ) / 100,
						'rx'    => $this->pct( $r['w'], 20 ) / 200,
						'ry'    => $this->pct( $r['h'], 20 ) / 200,
						'f'     => $this->pct( $r['feather'], 60 ) / 100,
						'type'  => $type,
						'amp'   => $this->pct( $r['amp'], 20 ) * $factor[ $type ],
						'speed' => $this->pct( $r['speed'], 0.5 ),
						'phase' => $this->pct( $r['phase'], 0 ),
						'px'    => $this->pct( $r['px'], 50 ) / 100,
						'py'    => $this->pct( $r['py'], 100 ) / 100,
						'depth' => $this->pct( $r['depth'], 0.5 ),
					);
				}
				if ( 'yes' === $s['sparks_on'] ) {
					foreach ( (array) $s['sparks'] as $p ) {
						if ( ( isset( $p['for'] ) && '' !== $p['for'] ? $p['for'] : 'wide' ) === $key ) {
							$v['sparks'][] = array( 'x' => $this->pct( $p['x'], 50 ) / 100, 'y' => $this->pct( $p['y'], 50 ) / 100, 's' => $this->pct( $p['size'], 1 ), 'dur' => $this->pct( $p['dur'], 5 ), 'dl' => $this->pct( $p['delay'], 0 ) );
						}
					}
				}
				/* the bundled tablet / phone pictures always move, even when no region was set up for them */
				if ( 'wide' !== $key && ! empty( $src[3] ) && ( ! $v['regions'] || ( 'yes' === $s['sparks_on'] && ! $v['sparks'] ) ) ) {
					$fallback = array( 'regions' => array(), 'sparks' => array() );
					foreach ( self::night_regions() as $r ) {
						if ( $key === $r['for'] ) {
							$type                    = $r['type'];
							$fallback['regions'][] = array( 'x' => $r['x']['size'] / 100, 'y' => $r['y']['size'] / 100, 'rx' => $r['w']['size'] / 200, 'ry' => $r['h']['size'] / 200, 'f' => $r['feather']['size'] / 100, 'type' => $type, 'amp' => $r['amp']['size'] * $factor[ $type ], 'speed' => $r['speed']['size'], 'phase' => $r['phase']['size'], 'px' => $r['px']['size'] / 100, 'py' => $r['py']['size'] / 100, 'depth' => $r['depth']['size'] );
						}
					}
					foreach ( self::night_sparks() as $p ) {
						if ( $key === $p['for'] ) {
							$fallback['sparks'][] = array( 'x' => $p['x']['size'] / 100, 'y' => $p['y']['size'] / 100, 's' => $p['size']['size'], 'dur' => $p['dur']['size'], 'dl' => $p['delay']['size'] );
						}
					}
					if ( ! $v['regions'] ) {
						$v['regions'] = array_slice( $fallback['regions'], 0, 16 );
					}
					if ( 'yes' === $s['sparks_on'] && ! $v['sparks'] ) {
						$v['sparks'] = $fallback['sparks'];
					}
				}
				$cfg['variants'][] = $v;
			}
			$cfg['img'] = $url;
			$cfg['floats'] = array();
			if ( 'yes' === $s['floats_on'] ) {
				foreach ( (array) $s['floats'] as $p ) {
					if ( empty( $p['image']['url'] ) ) {
						continue;
					}
					$cfg['floats'][] = array( 'for' => isset( $p['for'] ) && in_array( $p['for'], array( 'wide', 'tablet', 'mobile' ), true ) ? $p['for'] : 'all', 'src' => esc_url_raw( $p['image']['url'] ), 'x' => $this->pct( $p['x'], 20 ), 'y' => $this->pct( $p['y'], 40 ), 'w' => $this->pct( $p['w'], 6 ), 'dx' => $this->pct( $p['dx'], 40 ), 'dy' => $this->pct( $p['dy'], -20 ), 'rot' => $this->pct( $p['rot'], 45 ), 'dur' => $this->pct( $p['dur'], 36 ), 'dl' => $this->pct( $p['delay'], 0 ), 'o' => $this->pct( $p['opacity'], 0.85 ) );
				}
			}
		} elseif ( 'layers' === $mode ) {
			$cfg['ratio']  = max( 0.2, $this->pct( $s['ratio'], 1.78 ) );
			$cfg['layers'] = array();
			foreach ( (array) $s['layers'] as $l ) {
				if ( empty( $l['image']['url'] ) ) {
					continue;
				}
				$cfg['layers'][] = array( 'src' => esc_url_raw( $l['image']['url'] ), 'x' => $this->pct( $l['x'], 30 ), 'y' => $this->pct( $l['y'], 20 ), 'w' => $this->pct( $l['w'], 30 ), 'motion' => (string) $l['motion'], 'amt' => $this->pct( $l['amt'], 12 ), 'dur' => $this->pct( $l['dur'], 8 ), 'dl' => $this->pct( $l['delay'], 0 ), 'ox' => $this->pct( $l['ox'], 50 ), 'oy' => $this->pct( $l['oy'], 50 ), 'depth' => $this->pct( $l['depth'], 12 ), 'o' => $this->pct( $l['opacity'], 1 ), 'z' => isset( $l['z'] ) ? (int) $l['z'] : 1, 'hm' => isset( $l['hide_mobile'] ) && 'yes' === $l['hide_mobile'] );
			}
			if ( ! $cfg['layers'] ) {
				$this->empty_note( 'در بخش «لایه‌ها» دست‌کم یک تصویر اضافه کنید.' );
				return;
			}
		} elseif ( 'video' === $mode ) {
			$cfg['img']   = $url;
			$cfg['video'] = array(
				'src'    => isset( $s['video']['url'] ) ? esc_url_raw( $s['video']['url'] ) : '',
				'mobile' => isset( $s['video_mobile']['url'] ) ? esc_url_raw( $s['video_mobile']['url'] ) : '',
				'loop'   => 'yes' === $s['v_loop'],
				'rate'   => $this->pct( $s['v_rate'], 1 ),
				'start'  => (float) $s['v_start'],
				'end'    => (float) $s['v_end'],
				'fit'    => 'contain' === $s['v_fit'] ? 'contain' : 'cover',
			);
			if ( ! $cfg['video']['src'] && ! $url ) {
				$this->empty_note( 'ویدیوی خود را در بخش «ویدیو» انتخاب کنید. تا آن زمان عکس جایگزین نمایش داده می‌شود.' );
				return;
			}
		} else {
			$cfg['slides'] = array();
			foreach ( (array) $s['slides'] as $g ) {
				$u = ! empty( $g['id'] ) ? wp_get_attachment_image_url( absint( $g['id'] ), 'full' ) : ( isset( $g['url'] ) ? $g['url'] : '' );
				if ( $u ) {
					$cfg['slides'][] = esc_url_raw( $u );
				}
			}
			$cfg['time'] = max( 1, (float) $s['sl_time'] );
			$cfg['fade'] = max( 0.2, (float) $s['sl_fade'] );
			$cfg['ken']  = $this->pct( $s['sl_ken'], 8 ) / 100;
			$cfg['rand'] = 'rand' === $s['sl_order'];
			if ( ! $cfg['slides'] ) {
				$this->empty_note( 'در بخش «اسلایدشو» چند عکس انتخاب کنید.' );
				return;
			}
		}

		echo '<div class="ay-hero ay-hs ay-hero-' . esc_attr( $mode ) . '" data-ay-hero="' . esc_attr( wp_json_encode( $cfg ) ) . '">';
		if ( 'regions' === $mode && ! empty( $cfg['variants'] ) ) {
			/* the picture that suits this frame is chosen before anything is downloaded */
			echo '<img class="ay-hs-img ay-hero-media"' . ( $edit ? ' src="' . esc_url( $cfg['variants'][0]['set'][0][0] ) . '"' : '' ) . ' alt="' . esc_attr( $s['alt'] ) . '" decoding="async" fetchpriority="high">';
			echo '<noscript><img class="ay-hs-img ay-hero-media" src="' . esc_url( $url ) . '" alt="' . esc_attr( $s['alt'] ) . '"></noscript>';
			echo "<script>(function(){var s=document.currentScript,r=s&&s.parentNode;if(!r)return;try{var c=JSON.parse(r.getAttribute('data-ay-hero')),V=c.variants,D=document.documentElement,a=D.clientWidth/window.innerHeight,b=0,d=1e9,i;if(a>=1){for(i=0;i<V.length;i++)if(V[i].k==='wide')b=i}else{var f=-1;for(i=0;i<V.length;i++){if(V[i].k==='wide')continue;var q=Math.abs(Math.log(a/(V[i].w/V[i].h)));if(q<d){d=q;f=i}}if(f>=0)b=f}var v=V[b],W=r.clientWidth,H=r.clientHeight||1,n=Math.max(W/v.w,H/v.h)*v.w*Math.min(window.devicePixelRatio||1,2),u=v.set[v.set.length-1][0];for(i=0;i<v.set.length;i++){if(v.set[i][1]>=n){u=v.set[i][0];break}}r.setAttribute('data-v',b);r.querySelector('img').src=u;if(v.zone==='below'){var h=r.closest('.e-con');if(h){var t=Math.round((1-v.safe[2])*W*v.h/v.w+Math.max(14,W*.03))+'px';h.style.setProperty('--padding-top',t);h.style.setProperty('--padding-block-start',t);h.classList.add('ay-hz-below')}}}catch(e){}})();</script>";
		} elseif ( in_array( $mode, array( 'regions', 'video' ), true ) && $url ) {
			echo '<img class="ay-hs-img ay-hero-media" src="' . esc_url( $url ) . '" alt="' . esc_attr( $s['alt'] ) . '" decoding="async" fetchpriority="high">';
		}
		echo '<span class="ay-hero-veil" aria-hidden="true"></span><span class="ay-hero-fade" aria-hidden="true"></span></div>';
		if ( $edit && $this->pct( $s['speed'], 1 ) <= 0 ) {
			echo '<div class="ay-empty ay-empty-float" dir="rtl">سرعت کلی صفر است؛ تصویر ثابت نمایش داده می‌شود.</div>';
		}
	}
}
