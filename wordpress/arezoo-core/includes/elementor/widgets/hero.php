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
		$r = function ( $label, $x, $y, $w, $h, $feather, $type, $amp, $speed, $px, $py, $phase, $depth ) {
			return array( 'label' => $label, 'on' => 'yes', 'x' => array( 'size' => $x, 'unit' => '%' ), 'y' => array( 'size' => $y, 'unit' => '%' ), 'w' => array( 'size' => $w, 'unit' => '%' ), 'h' => array( 'size' => $h, 'unit' => '%' ), 'feather' => array( 'size' => $feather ), 'type' => $type, 'amp' => array( 'size' => $amp ), 'speed' => array( 'size' => $speed ), 'px' => array( 'size' => $px, 'unit' => '%' ), 'py' => array( 'size' => $py, 'unit' => '%' ), 'phase' => array( 'size' => $phase ), 'depth' => array( 'size' => $depth ) );
		};
		return array(
			$r( 'قاصدک و ساقه', 21.5, 56, 34, 80, 55, 'sway', 28, 0.55, 13.5, 93, 0, 0.6 ),
			$r( 'کرک قاصدک', 24.5, 42, 24, 40, 80, 'wave', 16, 1.1, 24.5, 42, 0, 0.3 ),
			$r( 'دانهٔ بالا چپ', 10, 31, 10, 17, 70, 'wave', 30, 0.8, 10, 31, 0, 1.2 ),
			$r( 'دانهٔ چپ', 4.5, 42, 10, 18, 70, 'wave', 30, 0.7, 4.5, 42, 1.4, 1.2 ),
			$r( 'دانهٔ راست قاصدک', 39, 40.5, 8, 12, 70, 'wave', 30, 0.9, 39, 40.5, 2.6, 1.2 ),
			$r( 'دانهٔ پایین', 37.5, 58.5, 9, 11, 70, 'wave', 30, 0.75, 37.5, 58.5, 4, 1.2 ),
			$r( 'شاخهٔ چپ', 33.5, 16.5, 30, 32, 60, 'sway', 24, 0.45, 50, 12, 1, 0.4 ),
			$r( 'برگ و غنچهٔ بالا', 44.5, 5, 10, 16, 60, 'sway', 20, 0.5, 49, 10, 2.2, 0.4 ),
			$r( 'برگ‌های راست', 90, 20, 14, 28, 60, 'sway', 28, 0.5, 88, 11, 2, 0.4 ),
			$r( 'غنچه‌های راست', 90, 5, 10, 12, 70, 'wave', 15, 0.9, 90, 5, 0, 0.3 ),
			$r( 'گل پایین چپ', 24, 74, 22, 28, 60, 'sway', 24, 0.5, 22.5, 90, 0.6, 0.8 ),
			$r( 'برگ‌های پایین چپ', 39, 84, 20, 26, 60, 'sway', 20, 0.4, 31, 93, 3, 0.9 ),
			$r( 'برگ‌های پایین راست', 81, 84, 15, 26, 60, 'sway', 20, 0.42, 86, 93, 1.5, 0.9 ),
			$r( 'گل پایین راست', 92.5, 72, 15, 30, 60, 'sway', 24, 0.48, 93, 90, 2.2, 0.8 ),
			$r( 'گردنبند', 62, 50, 38, 92, 35, 'sway', 9, 0.38, 63, 8, 0, 0.9 ),
		);
	}

	public static function night_sparks() {
		$out = array();
		foreach ( array( array( 1010, 585, 1.4, 4.2, 0 ), array( 892, 738, 1.1, 5, 1.7 ), array( 1000, 745, 1, 4.6, 3.1 ), array( 1190, 765, 1.3, 5.4, 0.8 ), array( 805, 470, 0.9, 6, 2.4 ), array( 1075, 735, 1.2, 4.4, 3.8 ), array( 955, 672, 0.9, 5.2, 1.1 ), array( 1188, 660, 0.8, 6.2, 4.4 ), array( 1262, 500, 0.8, 5.6, 2.9 ), array( 760, 318, 0.6, 6.6, 0.4 ), array( 1345, 318, 0.6, 7, 3.5 ) ) as $p ) {
			$out[] = array( 'x' => array( 'size' => round( $p[0] / 16.72, 2 ), 'unit' => '%' ), 'y' => array( 'size' => round( $p[1] / 9.41, 2 ), 'unit' => '%' ), 'size' => array( 'size' => $p[2] ), 'dur' => array( 'size' => $p[3] ), 'delay' => array( 'size' => $p[4] ) );
		}
		return $out;
	}

	public static function night_floats() {
		$out = array();
		foreach ( array( array( 'a', 24, 30, 6.5, 38, -22, 50, 34, 0 ), array( 'c', 30, 40, 5.5, 45, -8, -40, 42, 12 ), array( 'd', 22, 48, 7, 52, 10, 70, 38, 22 ), array( 'b', 18, 24, 7.5, 30, -30, -60, 46, 30 ), array( 'c', 28, 36, 4.5, 60, -16, 90, 52, 40 ) ) as $f ) {
			$out[] = array( 'image' => array( 'url' => ay_asset( 'img/hero/seed-' . $f[0] . '.png' ) ), 'x' => array( 'size' => $f[1], 'unit' => '%' ), 'y' => array( 'size' => $f[2], 'unit' => '%' ), 'w' => array( 'size' => $f[3], 'unit' => '%' ), 'dx' => array( 'size' => $f[4] ), 'dy' => array( 'size' => $f[5] ), 'rot' => array( 'size' => $f[6] ), 'dur' => array( 'size' => $f[7] ), 'delay' => array( 'size' => $f[8] ), 'opacity' => array( 'size' => 0.85 ) );
		}
		return $out;
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
		$this->media( 'image', 'عکس', ay_asset( 'img/hero/night-garden.jpg' ), array( 'condition' => array( 'mode' => array( 'regions', 'video' ) ), 'description' => 'در حالت ویدیو، این عکس تا بارگذاری ویدیو و وقتی حرکت خاموش است نمایش داده می‌شود. بهترین اندازه: ۳۸۴۰×۲۱۶۰ پیکسل.' ) );
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
		$this->add_control( 'regions', array( 'label' => 'ناحیه‌ها (حداکثر ۱۶)', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::night_regions(), 'title_field' => '{{{ label }}}' ) );
		$this->done();

		$this->sec( 's_sparks', 'نقطه‌های درخشش', false, $regions );
		$this->sw( 'sparks_on', 'نمایش درخشش‌ها', 'yes' );
		$rep = new Repeater();
		$this->rs( $rep, 'x', 'جای افقی', 0, 100, 0.1, 50, '%' );
		$this->rs( $rep, 'y', 'جای عمودی', 0, 100, 0.1, 50, '%' );
		$this->rs( $rep, 'size', 'اندازه', 0.2, 5, 0.1, 1 );
		$this->rs( $rep, 'dur', 'مدت یک چشمک (ثانیه)', 1, 20, 0.1, 5 );
		$this->rs( $rep, 'delay', 'تأخیر (ثانیه)', 0, 20, 0.1, 0 );
		$this->add_control( 'sparks', array( 'label' => 'درخشش‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::night_sparks(), 'title_field' => 'x: {{{ x.size }}} · y: {{{ y.size }}}', 'condition' => array( 'sparks_on' => 'yes' ) ) );
		$this->color( 'spark_color', 'رنگ درخشش', '--ay-spark', array( 'condition' => array( 'sparks_on' => 'yes' ) ) );
		$this->done();

		$this->sec( 's_floats', 'عناصر شناور', false, $regions );
		$this->sw( 'floats_on', 'نمایش عناصر شناور', 'yes' );
		$rep = new Repeater();
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
		$this->add_control( 'floats', array( 'label' => 'عناصر شناور', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::night_floats(), 'title_field' => 'عنصر شناور', 'condition' => array( 'floats_on' => 'yes' ) ) );
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
		if ( ! empty( $media['url'] ) && false !== strpos( $media['url'], 'night-garden.jpg' ) ) {
			return array( 1672, 941 );
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
			list( $w, $h )  = $this->media_dims( $s['image'] );
			$cfg['img']     = $url;
			$cfg['w']       = $w;
			$cfg['h']       = $h;
			$cfg['outline'] = $edit && 'yes' === $s['outline'];
			$cfg['regions'] = array();
			$factor         = array( 'sway' => 0.0005, 'wave' => 0.0001, 'breathe' => 0.0005 );
			foreach ( array_slice( (array) $s['regions'], 0, 16 ) as $r ) {
				if ( 'yes' !== ( isset( $r['on'] ) ? $r['on'] : 'yes' ) ) {
					continue;
				}
				$type             = isset( $factor[ $r['type'] ] ) ? $r['type'] : 'sway';
				$cfg['regions'][] = array(
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
			$cfg['sparks'] = array();
			if ( 'yes' === $s['sparks_on'] ) {
				foreach ( (array) $s['sparks'] as $p ) {
					$cfg['sparks'][] = array( 'x' => $this->pct( $p['x'], 50 ) / 100, 'y' => $this->pct( $p['y'], 50 ) / 100, 's' => $this->pct( $p['size'], 1 ), 'dur' => $this->pct( $p['dur'], 5 ), 'dl' => $this->pct( $p['delay'], 0 ) );
				}
			}
			$cfg['floats'] = array();
			if ( 'yes' === $s['floats_on'] ) {
				foreach ( (array) $s['floats'] as $p ) {
					if ( empty( $p['image']['url'] ) ) {
						continue;
					}
					$cfg['floats'][] = array( 'src' => esc_url_raw( $p['image']['url'] ), 'x' => $this->pct( $p['x'], 20 ), 'y' => $this->pct( $p['y'], 40 ), 'w' => $this->pct( $p['w'], 6 ), 'dx' => $this->pct( $p['dx'], 40 ), 'dy' => $this->pct( $p['dy'], -20 ), 'rot' => $this->pct( $p['rot'], 45 ), 'dur' => $this->pct( $p['dur'], 36 ), 'dl' => $this->pct( $p['delay'], 0 ), 'o' => $this->pct( $p['opacity'], 0.85 ) );
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
		if ( in_array( $mode, array( 'regions', 'video' ), true ) && $url ) {
			echo '<img class="ay-hs-img ay-hero-media" src="' . esc_url( $url ) . '" alt="' . esc_attr( $s['alt'] ) . '" decoding="async" fetchpriority="high">';
		}
		echo '<span class="ay-hero-veil" aria-hidden="true"></span><span class="ay-hero-fade" aria-hidden="true"></span></div>';
		if ( $edit && $this->pct( $s['speed'], 1 ) <= 0 ) {
			echo '<div class="ay-empty ay-empty-float" dir="rtl">سرعت کلی صفر است؛ تصویر ثابت نمایش داده می‌شود.</div>';
		}
	}
}
