<?php
/**
 * More abilities for every Elementor container and widget (Advanced tab): halo and glow,
 * looping and pointer motion, sticky inside a section, text reveal, picture effects,
 * light sweep and a scroll-progress line. Nothing is switched on by default.
 *
 * @package ArezooCore
 */

namespace Arezoo;

use Elementor\Controls_Manager as CM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Abilities {

	public static function init() {
		add_action( 'elementor/element/common/_section_style/after_section_end', array( __CLASS__, 'widget' ), 20 );
		add_action( 'elementor/element/container/section_layout/after_section_end', array( __CLASS__, 'container' ), 20 );
	}

	public static function widget( $el ) {
		self::halo( $el );
		self::motion( $el );
		self::sticky( $el );
		self::text( $el );
		self::picture( $el );
		self::shine( $el );
		self::progress( $el );
	}

	public static function container( $el ) {
		self::halo( $el );
		self::motion( $el );
		self::sticky( $el );
		self::shine( $el );
		self::progress( $el );
	}

	private static function sl( $el, $id, $label, $var, $min, $max, $step, $unit, $cond, $responsive = false ) {
		$units = '' === $unit ? array( 'px' ) : array( $unit );
		$args  = array(
			'label'      => $label,
			'type'       => CM::SLIDER,
			'size_units' => $units,
			'range'      => array( $units[0] => array( 'min' => $min, 'max' => $max, 'step' => $step ) ),
			'selectors'  => array( '{{WRAPPER}}' => $var . ': {{SIZE}}' . ( '' === $unit ? '' : '{{UNIT}}' ) . ';' ),
			'condition'  => $cond,
		);
		if ( $responsive ) {
			$el->add_responsive_control( $id, $args );
		} else {
			$el->add_control( $id, $args );
		}
	}

	private static function col( $el, $id, $label, $var, $cond, $desc = '' ) {
		$el->add_control( $id, array( 'label' => $label, 'type' => CM::COLOR, 'selectors' => array( '{{WRAPPER}}' => $var . ': {{VALUE}};' ), 'condition' => $cond, 'description' => $desc ) );
	}

	private static function sel( $el, $id, $label, $options, $prefix, $cond = array(), $desc = '' ) {
		$args = array( 'label' => $label, 'type' => CM::SELECT, 'default' => '', 'options' => $options, 'prefix_class' => $prefix, 'description' => $desc );
		if ( $cond ) {
			$args['condition'] = $cond;
		}
		$el->add_control( $id, $args );
	}

	private static function sw( $el, $id, $label, $prefix, $cond = array(), $desc = '' ) {
		$args = array( 'label' => $label, 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => '', 'prefix_class' => $prefix, 'description' => $desc );
		if ( $cond ) {
			$args['condition'] = $cond;
		}
		$el->add_control( $id, $args );
	}

	/* ------------------------------------------------------------ halo and glow */
	private static function halo( $el ) {
		$on = array( 'ay_halo!' => '' );
		$el->start_controls_section( 'ay_halo_section', array( 'label' => 'هاله و نور آرزو', 'tab' => CM::TAB_ADVANCED ) );
		self::sel( $el, 'ay_halo', 'هاله', array( '' => 'خاموش', 'behind' => 'هالهٔ نرم پشت المان', 'ring' => 'حلقهٔ نور', 'front' => 'نور روی المان' ), 'ay-halo-', array(), 'یک لایهٔ نور رنگی پشت (یا روی) این المان، بدون تغییر پس‌زمینهٔ خودش. برای عکس‌های بدون پس‌زمینه عالی است.' );
		self::col( $el, 'ay_halo_c1', 'رنگ', '--ay-h-c1', $on, 'خالی = رنگ تأکید تم' );
		self::col( $el, 'ay_halo_c2', 'رنگ دوم (لبهٔ بیرونی)', '--ay-h-c2', $on );
		self::sl( $el, 'ay_halo_op', 'شفافیت', '--ay-h-op', 0, 1, 0.02, '', $on );
		self::sl( $el, 'ay_halo_size', 'بیرون‌زدگی از لبه‌ها', '--ay-h-size', -40, 120, 1, '%', $on, true );
		self::sl( $el, 'ay_halo_blur', 'نرمی لبه (تاری)', '--ay-h-blur', 0, 140, 1, 'px', $on );
		self::sl( $el, 'ay_halo_x', 'جابه‌جایی افقی', '--ay-h-x', -100, 100, 1, '%', $on );
		self::sl( $el, 'ay_halo_y', 'جابه‌جایی عمودی', '--ay-h-y', -100, 100, 1, '%', $on );
		self::sel( $el, 'ay_halo_shape', 'شکل', array( '' => 'دایره / بیضی', 'box' => 'هم‌شکل المان (گوشه‌گرد)' ), 'ay-halo-shape-', $on );
		$el->add_control( 'ay_halo_blend', array( 'label' => 'حالت ترکیب', 'type' => CM::SELECT, 'default' => '', 'options' => array( '' => 'معمولی', 'screen' => 'Screen (روشن‌تر)', 'overlay' => 'Overlay', 'soft-light' => 'Soft light', 'plus-lighter' => 'Plus lighter' ), 'selectors' => array( '{{WRAPPER}}' => '--ay-h-blend: {{VALUE}};' ), 'condition' => $on ) );
		self::sel( $el, 'ay_halo_anim', 'حرکت هاله', array( '' => 'ثابت', 'breathe' => 'نفس کشیدن', 'pulse' => 'تپش نور', 'drift' => 'جابه‌جایی آرام', 'spin' => 'چرخش رنگ' ), 'ay-halo-a-', $on );
		self::sl( $el, 'ay_halo_speed', 'مدت یک دور (ثانیه)', '--ay-h-speed', 1, 40, 0.5, '', array( 'ay_halo!' => '', 'ay_halo_anim!' => '' ) );
		self::sw( $el, 'ay_halo_follow', 'هاله همراه موس حرکت کند', 'ay-halo-follow-', $on );
		self::sw( $el, 'ay_halo_hover', 'هنگام هاور پررنگ‌تر شود', 'ay-halo-hover-', $on );
		$el->end_controls_section();
	}

	/* -------------------------------------------------- loop / pointer / scroll */
	private static function motion( $el ) {
		$loop = array( 'ay_loop!' => '' );
		$ptr  = array( 'ay_ptr!' => '' );
		$el->start_controls_section( 'ay_mv_section', array( 'label' => 'حرکت پیوسته و موس آرزو', 'tab' => CM::TAB_ADVANCED ) );
		self::sel( $el, 'ay_loop', 'حرکت پیوسته', array( '' => 'خاموش', 'float' => 'شناور (بالا و پایین)', 'drift' => 'رفت و برگشت افقی', 'sway' => 'تاب خوردن', 'pulse' => 'تپش', 'zoom' => 'زوم آرام', 'spin' => 'چرخش', 'swing' => 'آونگ (آویز)' ), 'ay-loop-', array(), 'روی لایهٔ جدا اجرا می‌شود و با «تبدیل» المنتور تداخل ندارد.' );
		self::sl( $el, 'ay_loop_amp', 'مقدار حرکت', '--ay-l-amp', 0, 80, 1, '', $loop );
		self::sl( $el, 'ay_loop_dur', 'مدت (ثانیه)', '--ay-l-dur', 0.5, 60, 0.5, '', $loop );
		self::sl( $el, 'ay_loop_delay', 'تأخیر (ثانیه)', '--ay-l-delay', 0, 10, 0.1, '', $loop );
		self::sw( $el, 'ay_loop_rev', 'جهت برعکس', 'ay-loop-rev-', $loop );
		self::sel( $el, 'ay_ptr', 'واکنش به موس', array( '' => 'خاموش', 'shift' => 'جابه‌جایی پارالاکس', 'magnet' => 'کشش مغناطیسی' ), 'ay-ptr-', array(), 'فقط روی دستگاه‌های دارای موس. برای کج شدن سه‌بعدی از بخش «عمق سه‌بعدی آرزو» استفاده کنید.' );
		self::sl( $el, 'ay_ptr_k', 'قدرت', '--ay-p-k', 0, 100, 1, '', $ptr );
		self::sel( $el, 'ay_ptr_area', 'موس روی کجا دنبال شود', array( '' => 'همین المان', 'parent' => 'کانتینر والد', 'page' => 'کل صفحه' ), 'ay-ptr-area-', $ptr );
		self::sw( $el, 'ay_spar', 'پارالاکس هنگام اسکرول', 'ay-spar-' );
		self::sl( $el, 'ay_spar_k', 'مقدار جابه‌جایی (منفی = برعکس)', '--ay-s-k', -200, 200, 2, '', array( 'ay_spar' => 'yes' ) );
		$el->end_controls_section();
	}

	/* ---------------------------------------------------------- sticky in section */
	private static function sticky( $el ) {
		$on = array( 'ay_stk!' => '' );
		$el->start_controls_section( 'ay_stk_section', array( 'label' => 'چسبان در بخش آرزو', 'tab' => CM::TAB_ADVANCED ) );
		self::sel( $el, 'ay_stk', 'هنگام اسکرولِ بخشِ خودش ثابت بماند', array( '' => 'خاموش', 'all' => 'در همهٔ دستگاه‌ها', 'tablet' => 'دسکتاپ و تبلت', 'desktop' => 'فقط دسکتاپ' ), 'ay-stk-', array(), 'مثل «چسبان» المنتور پرو: المان تا انتهای کانتینر والدش روی صفحه می‌ماند. کانتینر والد باید از این المان بلندتر باشد.' );
		self::sl( $el, 'ay_stk_top', 'فاصله از بالای صفحه', '--ay-stk-top', 0, 400, 1, 'px', $on, true );
		$el->add_control( 'ay_stk_z', array( 'label' => 'ترتیب لایه (z-index)', 'type' => CM::NUMBER, 'min' => 0, 'max' => 999, 'selectors' => array( '{{WRAPPER}}' => '--ay-stk-z: {{VALUE}};' ), 'condition' => $on ) );
		$el->end_controls_section();
	}

	/* ---------------------------------------------------------------- text reveal */
	private static function text( $el ) {
		$on = array( 'ay_txt!' => '' );
		$el->start_controls_section( 'ay_txt_section', array( 'label' => 'ظاهر شدن متن آرزو', 'tab' => CM::TAB_ADVANCED ) );
		self::sel( $el, 'ay_txt', 'نوع', array( '' => 'خاموش', 'words' => 'بالا آمدن واژه‌ها', 'letters' => 'بالا آمدن حرف‌ها', 'blur' => 'واژه‌ها از تاری', 'lines' => 'پرده از زیر هر واژه', 'type' => 'ماشین تحریر', 'shimmer' => 'برق فلزی پیوسته' ), 'ay-txt-', array(), 'برای ویجت‌های تیتر و متن.' );
		self::sel( $el, 'ay_txt_start', 'شروع', array( '' => 'وقتی دیده می‌شود', 'load' => 'با باز شدن صفحه' ), 'ay-txt-start-', $on );
		self::sl( $el, 'ay_txt_delay', 'تأخیر (ثانیه)', '--ay-t-delay', 0, 5, 0.05, '', $on );
		self::sl( $el, 'ay_txt_stagger', 'فاصلهٔ زمانی واژه‌ها (میلی‌ثانیه)', '--ay-t-stagger', 5, 400, 5, '', $on );
		self::sl( $el, 'ay_txt_dur', 'مدت هر واژه (ثانیه)', '--ay-t-dur', 0.2, 3, 0.05, '', $on );
		self::col( $el, 'ay_txt_accent', 'رنگ برق / تأکید', '--ay-t-accent', $on );
		$el->end_controls_section();
	}

	/* ------------------------------------------------------------ picture effects */
	private static function picture( $el ) {
		$el->start_controls_section( 'ay_pic_section', array( 'label' => 'افکت تصویر آرزو', 'tab' => CM::TAB_ADVANCED ) );
		self::sel( $el, 'ay_pic_shape', 'شکل تصویر', array( '' => 'همان‌طور که هست', 'circle' => 'دایره', 'arch' => 'طاق', 'pill' => 'کپسول', 'diamond' => 'لوزی', 'octagon' => 'هشت‌ضلعی', 'hexagon' => 'شش‌ضلعی', 'blob' => 'شکل ارگانیک', 'slant' => 'لبهٔ اریب' ), 'ay-pic-' );
		self::sel( $el, 'ay_pic_frame', 'قاب', array( '' => 'بدون قاب', 'line' => 'خط ظریف', 'double' => 'دو خط', 'corner' => 'نشان گوشه‌ها', 'offset' => 'قاب جابه‌جا' ), 'ay-frame-' );
		self::col( $el, 'ay_pic_frame_c', 'رنگ قاب', '--ay-fr-c', array( 'ay_pic_frame!' => '' ) );
		self::sl( $el, 'ay_pic_frame_gap', 'فاصلهٔ قاب از تصویر', '--ay-fr-gap', 0, 40, 1, 'px', array( 'ay_pic_frame!' => '' ) );
		self::sl( $el, 'ay_pic_ft', 'محو شدن لبهٔ بالا', '--ay-fd-t', 0, 60, 1, '%', array() );
		self::sl( $el, 'ay_pic_fb', 'محو شدن لبهٔ پایین', '--ay-fd-b', 0, 60, 1, '%', array() );
		self::sl( $el, 'ay_pic_fl', 'محو شدن لبهٔ چپ', '--ay-fd-l', 0, 60, 1, '%', array() );
		self::sl( $el, 'ay_pic_frr', 'محو شدن لبهٔ راست', '--ay-fd-r', 0, 60, 1, '%', array() );
		self::sw( $el, 'ay_pic_fade', 'محو شدن لبه‌ها فعال باشد', 'ay-fade-edges-', array(), 'مقدار هر لبه را با لغزنده‌های بالا تعیین کنید.' );
		self::sw( $el, 'ay_pic_vig', 'محو شدن بیضی (وینیِت)', 'ay-vignette-' );
		self::sw( $el, 'ay_pic_shadow', 'سایهٔ هم‌شکل قطعه (برای PNG بدون پس‌زمینه)', 'ay-pshadow-' );
		self::sl( $el, 'ay_pic_shadow_s', 'اندازهٔ سایه', '--ay-ps', 0, 60, 1, 'px', array( 'ay_pic_shadow' => 'yes' ) );
		self::col( $el, 'ay_pic_shadow_c', 'رنگ سایهٔ قطعه', '--ay-ps-c', array( 'ay_pic_shadow' => 'yes' ) );
		self::sw( $el, 'ay_pic_reflect', 'بازتاب روی زمین', 'ay-reflect-' );
		self::sel( $el, 'ay_pic_flip', 'برگرداندن', array( '' => 'خیر', 'h' => 'افقی', 'v' => 'عمودی' ), 'ay-flip-' );
		$el->add_control( 'ay_pic_blend', array( 'label' => 'حالت ترکیب تصویر', 'type' => CM::SELECT, 'default' => '', 'options' => array( '' => 'معمولی', 'screen' => 'Screen', 'multiply' => 'Multiply', 'lighten' => 'Lighten', 'luminosity' => 'Luminosity', 'soft-light' => 'Soft light' ), 'selectors' => array( '{{WRAPPER}} img' => 'mix-blend-mode: {{VALUE}};' ) ) );
		self::sel( $el, 'ay_pic_hover', 'هاور تصویر', array( '' => 'بدون تغییر', 'zoom' => 'بزرگ‌نمایی آرام', 'lift' => 'بالا آمدن', 'color' => 'از تک‌رنگ به رنگی', 'tiltz' => 'چرخش ملایم' ), 'ay-pic-h-' );
		$el->end_controls_section();
	}

	/* ------------------------------------------------------------------ light sweep */
	private static function shine( $el ) {
		$on = array( 'ay_shine!' => '' );
		$el->start_controls_section( 'ay_shine_section', array( 'label' => 'برق و درخشش آرزو', 'tab' => CM::TAB_ADVANCED ) );
		self::sel( $el, 'ay_shine', 'عبور نور', array( '' => 'خاموش', 'hover' => 'هنگام هاور', 'loop' => 'پیوسته (با مکث)', 'view' => 'یک بار، وقتی دیده می‌شود' ), 'ay-shine-' );
		self::col( $el, 'ay_shine_c', 'رنگ نور', '--ay-sh-c', $on );
		self::sl( $el, 'ay_shine_op', 'شدت', '--ay-sh-op', 0, 1, 0.02, '', $on );
		self::sl( $el, 'ay_shine_w', 'پهنای نوار نور', '--ay-sh-w', 5, 90, 1, '%', $on );
		self::sl( $el, 'ay_shine_angle', 'زاویه', '--ay-sh-a', -60, 60, 1, 'deg', $on );
		self::sl( $el, 'ay_shine_dur', 'مدت عبور (ثانیه)', '--ay-sh-dur', 0.3, 6, 0.05, '', $on );
		self::sl( $el, 'ay_shine_every', 'تکرار هر چند ثانیه (حالت پیوسته)', '--ay-sh-every', 1, 30, 0.5, '', array( 'ay_shine' => 'loop' ) );
		self::sw( $el, 'ay_shine_rev', 'از راست به چپ', 'ay-shine-rev-', $on );
		$el->end_controls_section();
	}

	/* --------------------------------------------------------- scroll-progress line */
	private static function progress( $el ) {
		$on = array( 'ay_pb!' => '' );
		$el->start_controls_section( 'ay_pb_section', array( 'label' => 'خط پیشرفت اسکرول آرزو', 'tab' => CM::TAB_ADVANCED ) );
		self::sel( $el, 'ay_pb', 'خطی که با اسکرول پر می‌شود', array( '' => 'خاموش', 'bottom' => 'لبهٔ پایین', 'top' => 'لبهٔ بالا', 'left' => 'لبهٔ چپ', 'right' => 'لبهٔ راست' ), 'ay-pb-', array(), 'مثل خط زیر هدر که با پایین رفتن صفحه پر می‌شود.' );
		self::sel( $el, 'ay_pb_src', 'پیشرفتِ', array( '' => 'کل صفحه', 'self' => 'عبور همین المان از صفحه' ), 'ay-pb-src-', $on );
		self::sel( $el, 'ay_pb_dir', 'جهت پر شدن', array( '' => 'از ابتدا', 'end' => 'از انتها', 'center' => 'از وسط به دو طرف' ), 'ay-pb-dir-', $on );
		self::col( $el, 'ay_pb_c', 'رنگ خط', '--ay-pb-c', $on );
		self::col( $el, 'ay_pb_track', 'رنگ مسیر', '--ay-pb-track', $on );
		self::sl( $el, 'ay_pb_w', 'ضخامت', '--ay-pb-w', 1, 12, 0.5, 'px', $on );
		self::sl( $el, 'ay_pb_inset', 'فاصله از دو سر', '--ay-pb-inset', 0, 200, 1, 'px', $on );
		$el->end_controls_section();
	}
}

Abilities::init();
