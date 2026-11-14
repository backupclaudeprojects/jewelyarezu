<?php
/**
 * Abilities added to every Elementor container and widget (Advanced tab → «… آرزو»):
 * glass, optional 3D depth, entrance motion, and — on containers — floating bar,
 * sketch background and scroll timeline. Each element has its own values; nothing here
 * is switched on by default.
 *
 * @package ArezooCore
 */

namespace Arezoo;

use Elementor\Controls_Manager as CM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Extensions {

	public static function init() {
		add_action( 'elementor/element/common/_section_style/after_section_end', array( __CLASS__, 'widget_sections' ) );
		add_action( 'elementor/element/container/section_layout/after_section_end', array( __CLASS__, 'container_sections' ) );
	}

	public static function widget_sections( $el ) {
		self::glass( $el );
		self::depth( $el );
		self::reveal( $el );
	}

	public static function container_sections( $el ) {
		self::glass( $el );
		self::depth( $el );
		self::reveal( $el );
		self::float_bar( $el );
		self::sketch_bg( $el );
		self::timeline( $el );
	}

	private static function slider( $el, $id, $label, $var, $min, $max, $step, $unit, $cond, $default = null, $responsive = false ) {
		$units = '' === $unit ? array( 'px' ) : array( $unit );
		$args  = array(
			'label'      => $label,
			'type'       => CM::SLIDER,
			'size_units' => $units,
			'range'      => array( $units[0] => array( 'min' => $min, 'max' => $max, 'step' => $step ) ),
			'selectors'  => array( '{{WRAPPER}}' => $var . ': {{SIZE}}' . ( '' === $unit ? '' : '{{UNIT}}' ) . ';' ),
			'condition'  => $cond,
		);
		if ( null !== $default ) {
			$args['default'] = array( 'unit' => $units[0], 'size' => $default );
		}
		if ( $responsive ) {
			$el->add_responsive_control( $id, $args );
		} else {
			$el->add_control( $id, $args );
		}
	}

	/* ------------------------------------------------------------------ glass */
	private static function glass( $el ) {
		$on = array( 'ay_glass' => 'yes' );
		$el->start_controls_section( 'ay_glass_section', array( 'label' => 'شیشهٔ آرزو', 'tab' => CM::TAB_ADVANCED ) );
		$el->add_control( 'ay_glass', array( 'label' => 'پس‌زمینهٔ شیشه‌ای مات', 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => '', 'prefix_class' => 'ay-glass-' ) );
		$el->add_control( 'ay_glass_color', array( 'label' => 'رنگ شیشه', 'type' => CM::COLOR, 'selectors' => array( '{{WRAPPER}}' => '--ay-g-color: {{VALUE}};' ), 'condition' => $on ) );
		self::slider( $el, 'ay_glass_op', 'پوشش رنگ (۰ = کاملاً شفاف)', '--ay-g-op', 0, 1, 0.02, '', $on );
		self::slider( $el, 'ay_glass_blur', 'تاری پشت شیشه', '--ay-g-blur', 0, 40, 1, 'px', $on );
		self::slider( $el, 'ay_glass_sat', 'غلظت رنگ پشت شیشه', '--ay-g-sat', 0.5, 2.5, 0.05, '', $on );
		$el->add_control( 'ay_glass_line', array( 'label' => 'رنگ خط دور', 'type' => CM::COLOR, 'selectors' => array( '{{WRAPPER}}' => '--ay-g-line: {{VALUE}};' ), 'condition' => $on ) );
		self::slider( $el, 'ay_glass_lw', 'ضخامت خط دور', '--ay-g-lw', 0, 4, 1, 'px', $on );
		self::slider( $el, 'ay_glass_shadow', 'شدت سایه', '--ay-g-sh', 0, 1, 0.05, '', $on );
		self::slider( $el, 'ay_glass_shine', 'برق لبهٔ بالا', '--ay-g-shine', 0, 1, 0.05, '', $on );
		$el->end_controls_section();
	}

	/* --------------------------------------------------------------- 3D depth */
	private static function depth( $el ) {
		$tilt   = array( 'ay_depth' => 'tilt' );
		$raised = array( 'ay_depth' => 'raised' );
		$el->start_controls_section( 'ay_depth_section', array( 'label' => 'عمق سه‌بعدی آرزو', 'tab' => CM::TAB_ADVANCED ) );
		$el->add_control( 'ay_depth', array(
			'label'        => 'نوع افکت',
			'type'         => CM::SELECT,
			'default'      => '',
			'options'      => array( '' => 'خاموش', 'tilt' => 'کج شدن با موس، عمق و نور متحرک', 'raised' => 'ضخامت واقعی، برجسته و قابل فشردن' ),
			'prefix_class' => 'ay-depth-',
		) );
		self::slider( $el, 'ay_depth_angle', 'بیشترین زاویهٔ کج شدن', '--ay-d-angle', 0, 30, 1, '', $tilt );
		self::slider( $el, 'ay_depth_persp', 'فاصلهٔ دید (پرسپکتیو)', '--ay-d-persp', 300, 2000, 20, 'px', $tilt );
		self::slider( $el, 'ay_depth_lift', 'بیرون‌زدگی محتوا', '--ay-d-lift', 0, 120, 2, 'px', $tilt );
		self::slider( $el, 'ay_depth_glare', 'شدت نور متحرک', '--ay-d-glare', 0, 1, 0.05, '', $tilt );
		self::slider( $el, 'ay_depth_speed', 'نرمی حرکت (ثانیه)', '--ay-d-speed', 0.05, 1.5, 0.05, '', $tilt );
		self::slider( $el, 'ay_depth_shadow', 'شدت سایهٔ زیر', '--ay-d-shadow', 0, 1, 0.05, '', $tilt );
		self::slider( $el, 'ay_depth_thick', 'ضخامت لبه', '--ay-d-thick', 2, 40, 1, 'px', $raised );
		$el->add_control( 'ay_depth_edge', array( 'label' => 'رنگ لبه', 'type' => CM::COLOR, 'selectors' => array( '{{WRAPPER}}' => '--ay-d-edge: {{VALUE}};' ), 'condition' => $raised ) );
		self::slider( $el, 'ay_depth_hover', 'بالا آمدن هنگام هاور', '--ay-d-hover', 0, 20, 1, 'px', $raised );
		self::slider( $el, 'ay_depth_ground', 'شدت سایهٔ روی زمین', '--ay-d-ground', 0, 1, 0.05, '', $raised );
		$el->add_control( 'ay_depth_touch', array( 'label' => 'روی صفحه‌های لمسی هم فعال باشد', 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => '', 'prefix_class' => 'ay-depth-touch-', 'condition' => array( 'ay_depth!' => '' ) ) );
		$el->end_controls_section();
	}

	/* ---------------------------------------------------------- entrance motion */
	private static function reveal( $el ) {
		$on = array( 'ay_rv!' => '' );
		$el->start_controls_section( 'ay_rv_section', array( 'label' => 'ورود با حرکت آرزو', 'tab' => CM::TAB_ADVANCED ) );
		$el->add_control( 'ay_rv', array(
			'label'        => 'نوع ورود',
			'type'         => CM::SELECT,
			'default'      => '',
			'options'      => array( '' => 'بدون حرکت', 'up' => 'بالا آمدن نرم', 'fade' => 'محو شدن', 'wipe' => 'پرده از چپ', 'blur' => 'از تاری به وضوح', 'zoom' => 'بزرگ شدن نرم', 'tilt' => 'چرخش ملایم', 'ink' => 'پخش جوهر' ),
			'prefix_class' => 'ay-rv-',
		) );
		self::slider( $el, 'ay_rv_dur', 'مدت (ثانیه)', '--ay-rv-dur', 0.2, 3, 0.05, '', $on );
		self::slider( $el, 'ay_rv_delay', 'تأخیر (ثانیه)', '--ay-rv-delay', 0, 2, 0.05, '', $on );
		self::slider( $el, 'ay_rv_dist', 'فاصلهٔ حرکت', '--ay-rv-dist', 0, 160, 2, 'px', $on );
		$el->end_controls_section();
	}

	/* ------------------------------------------------------------- floating bar */
	private static function float_bar( $el ) {
		$on = array( 'ay_float!' => '' );
		$el->start_controls_section( 'ay_float_section', array( 'label' => 'نوار شناور آرزو', 'tab' => CM::TAB_ADVANCED ) );
		$el->add_control( 'ay_float', array(
			'label'        => 'شناور و ثابت روی صفحه',
			'type'         => CM::SELECT,
			'default'      => '',
			'options'      => array( '' => 'خاموش', 'top' => 'بالای صفحه', 'bottom' => 'پایین صفحه' ),
			'prefix_class' => 'ay-float-',
			'description'  => 'برای هدر و نوار پایین. محتوای صفحه زیر آن حرکت می‌کند.',
		) );
		self::slider( $el, 'ay_float_offset', 'فاصله از لبهٔ صفحه', '--ay-f-offset', 0, 80, 1, 'px', $on, null, true );
		self::slider( $el, 'ay_float_width', 'بیشترین عرض', '--ay-f-width', 280, 1600, 10, 'px', $on, null, true );
		self::slider( $el, 'ay_float_side', 'فاصله از کناره‌ها', '--ay-f-side', 0, 60, 1, 'px', $on, null, true );
		$el->add_control( 'ay_float_z', array( 'label' => 'ترتیب لایه (z-index)', 'type' => CM::NUMBER, 'min' => 1, 'max' => 9999, 'selectors' => array( '{{WRAPPER}}' => '--ay-f-z: {{VALUE}};' ), 'condition' => $on ) );
		$el->add_control( 'ay_float_hide', array( 'label' => 'پنهان شدن نزدیک فوتر', 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => '', 'prefix_class' => 'ay-float-hide-', 'condition' => array( 'ay_float' => 'bottom' ) ) );
		$el->add_control( 'ay_float_smart', array( 'label' => 'پنهان شدن هنگام اسکرول به پایین', 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => '', 'prefix_class' => 'ay-float-smart-', 'condition' => array( 'ay_float' => 'top' ) ) );
		$el->end_controls_section();
	}

	/* ---------------------------------------------------------- sketch background */
	private static function sketch_bg( $el ) {
		$on   = array( 'ay_sk!' => '' );
		$dict = array();
		$opts = array( '' => 'خاموش' );
		foreach ( ay_wallpaper_motifs() as $k => $label ) {
			$opts[ $k ] = $label;
			$dict[ $k ] = 'url(' . ay_asset( 'img/sketch/' . $k . '.webp' ) . ')';
		}
		$opts['custom'] = 'تصویر دلخواه';
		$dict['custom'] = 'none';
		$el->start_controls_section( 'ay_sk_section', array( 'label' => 'اسکچ پس‌زمینهٔ آرزو', 'tab' => CM::TAB_ADVANCED ) );
		$el->add_control( 'ay_sk', array( 'label' => 'طرح اسکچ', 'type' => CM::SELECT, 'default' => '', 'options' => $opts, 'prefix_class' => 'ay-skbg-', 'selectors_dictionary' => $dict, 'selectors' => array( '{{WRAPPER}}' => '--ay-sk-img: {{VALUE}};' ) ) );
		$el->add_control( 'ay_sk_custom', array( 'label' => 'تصویر (PNG یا WebP بدون پس‌زمینه)', 'type' => CM::MEDIA, 'selectors' => array( '{{WRAPPER}}' => '--ay-sk-img: url({{URL}});' ), 'condition' => array( 'ay_sk' => 'custom' ) ) );
		$el->add_control( 'ay_sk_color', array( 'label' => 'رنگ خط‌ها', 'type' => CM::COLOR, 'selectors' => array( '{{WRAPPER}}' => '--ay-sk-color: {{VALUE}};' ), 'condition' => $on, 'description' => 'خالی = هم‌رنگ متن (در تم تیره روشن، در تم روشن تیره)' ) );
		self::slider( $el, 'ay_sk_op', 'میزان دیده شدن', '--ay-sk-op', 0, 1, 0.01, '', $on );
		self::slider( $el, 'ay_sk_size', 'اندازه', '--ay-sk-size', 80, 1400, 10, 'px', $on, null, true );
		self::slider( $el, 'ay_sk_x', 'جای افقی', '--ay-sk-x', -20, 120, 1, '%', $on, null, true );
		self::slider( $el, 'ay_sk_y', 'جای عمودی', '--ay-sk-y', -20, 120, 1, '%', $on, null, true );
		self::slider( $el, 'ay_sk_rot', 'چرخش', '--ay-sk-rot', -180, 180, 1, 'deg', $on );
		self::slider( $el, 'ay_sk_blur', 'تاری', '--ay-sk-blur', 0, 12, 0.5, 'px', $on );
		$el->add_control( 'ay_sk_motion', array( 'label' => 'حرکت', 'type' => CM::SELECT, 'default' => '', 'options' => array( '' => 'ثابت', 'float' => 'شناور آرام', 'spin' => 'چرخش خیلی آهسته', 'breathe' => 'نفس کشیدن' ), 'prefix_class' => 'ay-skm-', 'condition' => $on ) );
		self::slider( $el, 'ay_sk_dur', 'مدت یک دور حرکت (ثانیه)', '--ay-sk-dur', 4, 120, 1, '', array( 'ay_sk!' => '', 'ay_sk_motion!' => '' ) );
		$el->end_controls_section();
	}

	/* ------------------------------------------------------------- scroll timeline */
	private static function timeline( $el ) {
		$on = array( 'ay_tl' => 'yes' );
		$el->start_controls_section( 'ay_tl_section', array( 'label' => 'خط زمانی آرزو', 'tab' => CM::TAB_ADVANCED ) );
		$el->add_control( 'ay_tl', array( 'label' => 'خط زمانی کنار زیرمجموعه‌ها', 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => '', 'prefix_class' => 'ay-tl-', 'description' => 'هر کانتینر داخلی یک مرحله می‌شود؛ خط با اسکرول پر و نقطهٔ هر مرحله روشن می‌شود.' ) );
		$el->add_control( 'ay_tl_line', array( 'label' => 'رنگ خط', 'type' => CM::COLOR, 'selectors' => array( '{{WRAPPER}}' => '--ay-tl-line: {{VALUE}};' ), 'condition' => $on ) );
		$el->add_control( 'ay_tl_fill', array( 'label' => 'رنگ خط پرشده', 'type' => CM::COLOR, 'selectors' => array( '{{WRAPPER}}' => '--ay-tl-fill: {{VALUE}};' ), 'condition' => $on ) );
		$el->add_control( 'ay_tl_dot', array( 'label' => 'رنگ داخل نقطه', 'type' => CM::COLOR, 'selectors' => array( '{{WRAPPER}}' => '--ay-tl-dot: {{VALUE}};' ), 'condition' => $on ) );
		self::slider( $el, 'ay_tl_size', 'اندازهٔ نقطه', '--ay-tl-size', 8, 40, 1, 'px', $on );
		self::slider( $el, 'ay_tl_indent', 'فاصلهٔ متن از خط', '--ay-tl-indent', 24, 120, 2, 'px', $on, null, true );
		self::slider( $el, 'ay_tl_at', 'جای روشن شدن در ارتفاع صفحه', '--ay-tl-at', 20, 90, 1, '', $on );
		self::slider( $el, 'ay_tl_dim', 'کم‌رنگی مرحله‌های نرسیده', '--ay-tl-dim', 0.2, 1, 0.05, '', $on );
		$el->end_controls_section();
	}
}

Extensions::init();
