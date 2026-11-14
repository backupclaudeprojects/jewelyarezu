<?php
/**
 * Digital design studio widgets: looping design motions, the scroll-told design process,
 * sketch ↔ jewel reveal and the photo strip.
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
 * Design motion
 * ====================================================================== */
class Motion extends Base {

	public function get_name() {
		return 'ay-motion';
	}

	public function get_title() {
		return 'موشن طراحی آرزو';
	}

	public function get_icon() {
		return 'eicon-animation';
	}

	protected function register_controls() {
		$this->sec( 's_main', 'موشن' );
		$this->sel( 'type', 'نوع موشن', ay_motion_types(), 'sketch' );
		$this->sel( 'play', 'زمان پخش', array( 'inview' => 'وقتی دیده می‌شود', 'always' => 'همیشه', 'hover' => 'فقط هنگام هاور' ), 'inview' );
		$this->cssvar( 'speed', 'سرعت', '--m-spd', 0.2, 3, 0.05, 1, '', array(), false );
		$this->cssvar( 'size', 'اندازه', '--m-size', 40, 800, 2, 220 );
		$this->align( 'align', '.ay-mo-wrap' );
		$this->txt( 'caption', 'متن زیر موشن (اختیاری)', '' );
		$this->done();
		$this->sec( 'st_main', 'رنگ‌ها', true );
		$this->color( 'line', 'رنگ خط‌ها', '--m-line' );
		$this->color( 'accent', 'رنگ تأکید', '--m-accent' );
		$this->color( 'fillc', 'رنگ سطح‌ها', '--m-fill' );
		$this->color( 'pen', 'رنگ بدنهٔ قلم', '--m-pen' );
		$this->cssvar( 'stroke', 'ضخامت خط', '--m-stroke', 0.5, 5, 0.1, 1.6, '', array(), false );
		$this->text_style( 'cap', 'متن زیر موشن', '.ay-mo-cap' );
		$this->done();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$type = isset( ay_motion_types()[ $s['type'] ] ) ? $s['type'] : 'sketch';
		$play = in_array( $s['play'], array( 'inview', 'always', 'hover' ), true ) ? $s['play'] : 'inview';
		echo '<div class="ay-mo-wrap"><div class="ay-mo ay-mo-' . esc_attr( $type ) . ( 'always' === $play ? ' is-play' : '' ) . '" data-ay-motion="' . esc_attr( $play ) . '" data-type="' . esc_attr( $type ) . '" aria-hidden="true">' . ay_motion_svg( $type ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( '' !== trim( (string) $s['caption'] ) ) {
			echo '<p class="ay-mo-cap">' . esc_html( $s['caption'] ) . '</p>';
		}
		echo '</div>';
	}
}

/* =========================================================================
 * Design process (scroll-told)
 * ====================================================================== */
class Process extends Base {

	public function get_name() {
		return 'ay-process';
	}

	public function get_title() {
		return 'فرایند طراحی دیجیتال آرزو';
	}

	public function get_icon() {
		return 'eicon-scroll';
	}

	public static function fx_options() {
		return array( 'brush' => 'ضربهٔ قلم‌مو (اریب)', 'wipe' => 'پرده از چپ', 'iris' => 'باز شدن از نوک قلم', 'sketch' => 'از اسکچ خطی به رنگ', 'zoom' => 'از تاری و بزرگی به وضوح', 'drop' => 'افتادن لایه از بالا' );
	}

	public static function default_steps() {
		$img = function ( $n ) {
			return array( 'url' => ay_asset( 'img/studio/' . $n . '.webp' ) );
		};
		$t = Timeline::default_steps();
		return array(
			array( 'eyebrow' => 'Brief', 'title' => $t[0]['title'], 'text' => $t[0]['text'], 'image' => $img( '01-desk-references' ), 'fx' => 'brush', 'motion' => 'blueprint', 'pen_x' => array( 'size' => 58 ), 'pen_y' => array( 'size' => 62 ) ),
			array( 'eyebrow' => 'Sketch', 'title' => $t[1]['title'], 'text' => $t[1]['text'], 'image' => $img( '02-paper-sketches' ), 'fx' => 'sketch', 'motion' => 'sketch', 'pen_x' => array( 'size' => 70 ), 'pen_y' => array( 'size' => 40 ) ),
			array( 'eyebrow' => 'Line', 'title' => $t[2]['title'], 'text' => $t[2]['text'], 'image' => $img( '03-line-drawing' ), 'fx' => 'iris', 'motion' => 'facets', 'pen_x' => array( 'size' => 62 ), 'pen_y' => array( 'size' => 50 ) ),
			array( 'eyebrow' => 'Colour & light', 'title' => $t[3]['title'], 'text' => $t[3]['text'], 'image' => $img( '04-rendering' ), 'fx' => 'wipe', 'motion' => 'render', 'pen_x' => array( 'size' => 74 ), 'pen_y' => array( 'size' => 30 ) ),
			array( 'eyebrow' => 'Presentation', 'title' => $t[4]['title'], 'text' => $t[4]['text'], 'image' => $img( '05-final-necklace' ), 'fx' => 'zoom', 'motion' => 'layers', 'pen_x' => array( 'size' => 66 ), 'pen_y' => array( 'size' => 58 ) ),
		);
	}

	protected function register_controls() {
		$this->sec( 's_steps', 'مرحله‌ها' );
		$rep = new Repeater();
		$rep->add_control( 'eyebrow', array( 'label' => 'برچسب کوتاه', 'type' => CM::TEXT, 'default' => 'Step' ) );
		$rep->add_control( 'title', array( 'label' => 'عنوان', 'type' => CM::TEXT, 'default' => 'Title', 'label_block' => true ) );
		$rep->add_control( 'text', array( 'label' => 'متن', 'type' => CM::TEXTAREA, 'default' => '' ) );
		$rep->add_control( 'image', array( 'label' => 'عکس این مرحله', 'type' => CM::MEDIA ) );
		$rep->add_control( 'fx', array( 'label' => 'نوع ظاهر شدن عکس', 'type' => CM::SELECT, 'options' => self::fx_options(), 'default' => 'brush' ) );
		$rep->add_control( 'motion', array( 'label' => 'موشن کنار عکس', 'type' => CM::SELECT, 'options' => array_merge( array( '' => 'بدون موشن' ), ay_motion_types() ), 'default' => 'sketch' ) );
		$rep->add_control( 'pen_x', array( 'label' => 'جای افقی قلم روی عکس', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'default' => array( 'size' => 60 ) ) );
		$rep->add_control( 'pen_y', array( 'label' => 'جای عمودی قلم روی عکس', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'default' => array( 'size' => 50 ) ) );
		$rep->add_control( 'focus', array( 'label' => 'بخش مهم عکس', 'type' => CM::SELECT, 'options' => array( 'center' => 'وسط', 'top' => 'بالا', 'bottom' => 'پایین', 'left' => 'چپ', 'right' => 'راست' ), 'default' => 'center' ) );
		$this->add_control( 'steps', array( 'label' => 'مرحله‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::default_steps(), 'title_field' => '{{{ eyebrow }}} · {{{ title }}}' ) );
		$this->sel( 'tag', 'تگ عنوان', array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ), 'h3' );
		$this->done();

		$this->sec( 's_layout', 'چیدمان' );
		$this->sel( 'side', 'جای قاب عکس', array( 'left' => 'چپ', 'right' => 'راست' ), 'left', array( 'prefix_class' => 'ay-pr-side-' ) );
		$this->sel( 'frame', 'شکل قاب', array( 'tablet' => 'تبلت طراحی', 'plain' => 'قاب سادهٔ گرد', 'paper' => 'کاغذ با چسب' ), 'tablet', array( 'prefix_class' => 'ay-pr-frame-' ) );
		$this->add_control( 'aspect', array( 'label' => 'نسبت قاب (عرض ÷ ارتفاع)', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.5, 'max' => 2, 'step' => 0.01 ) ), 'default' => array( 'size' => 0.8 ), 'selectors' => array( '{{WRAPPER}}' => '--ay-pr-aspect: {{SIZE}};' ) ) );
		$this->cssvar( 'media_w', 'سهم ستون عکس', '--ay-pr-mw', 25, 70, 1, 46, '%' );
		$this->cssvar( 'gap', 'فاصلهٔ دو ستون', '--ay-pr-gap', 0, 160, 2, 80 );
		$this->cssvar( 'step_h', 'ارتفاع هر مرحله هنگام اسکرول', '--ay-pr-step', 30, 120, 1, 70, 'vh' );
		$this->sw( 'sticky', 'قاب عکس هنگام اسکرول ثابت بماند', 'yes', array( 'prefix_class' => 'ay-pr-sticky-' ) );
		$this->cssvar( 'top', 'فاصلهٔ قاب از بالای صفحه', '--ay-pr-top', 0, 300, 1, 110, 'px', array( 'condition' => array( 'sticky' => 'yes' ) ) );
		$this->done();

		$this->sec( 's_extras', 'جزئیات متحرک' );
		$this->sw( 'pen', 'قلم متحرک روی عکس', 'yes' );
		$this->sw( 'chips', 'فهرست لایه‌ها کنار قاب (مثل پنل Layers)', 'yes' );
		$this->txt( 'chips_title', 'عنوان فهرست لایه‌ها', 'Layers', array( 'label_block' => false, 'condition' => array( 'chips' => 'yes' ) ) );
		$this->sw( 'badge', 'موشن هر مرحله کنار قاب', 'yes' );
		$this->sw( 'counter', 'شمارندهٔ مرحله و خط پیشرفت', 'yes' );
		$this->sw( 'tilt', 'کج شدن ملایم قاب با اسکرول', 'yes' );
		$this->sw( 'numbers', 'شمارهٔ بزرگ کنار هر مرحله', 'yes' );
		$this->done();

		$this->sec( 'st_frame', 'قاب عکس', true );
		$this->color( 'bezel', 'رنگ بدنهٔ قاب', '--ay-pr-bezel' );
		$this->cssvar( 'radius', 'گردی گوشه‌ها', '--ay-pr-radius', 0, 80, 1, 30 );
		$this->cssvar( 'bezel_w', 'ضخامت بدنهٔ قاب', '--ay-pr-bw', 0, 40, 1, 12 );
		$this->color( 'pen_c', 'رنگ بدنهٔ قلم', '--m-pen' );
		$this->color( 'chip_bg', 'رنگ پنل لایه‌ها', '--ay-pr-chip' );
		$this->color( 'chip_on', 'رنگ لایهٔ فعال', '--ay-pr-chip-on' );
		$this->color( 'bar', 'رنگ خط پیشرفت', '--ay-pr-bar' );
		$this->color( 'm_line', 'رنگ خط موشن‌ها', '--m-line' );
		$this->color( 'm_accent', 'رنگ تأکید موشن‌ها', '--m-accent' );
		$this->done();
		$this->sec( 'st_text', 'متن‌ها', true );
		$this->text_style( 'num', 'شماره', '.ay-pr-num' );
		$this->text_style( 'eye', 'برچسب کوتاه', '.ay-pr-eye' );
		$this->text_style( 'title', 'عنوان', '.ay-pr-title' );
		$this->text_style( 'text', 'متن', '.ay-pr-text' );
		$this->cssvar( 'dim', 'کم‌رنگی مرحله‌های غیرفعال', '--ay-pr-dim', 0.1, 1, 0.05, 0.35, '', array(), false );
		$this->done();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$steps = array_values( array_filter( (array) $s['steps'], function ( $i ) {
			return ! empty( $i['image']['url'] ) || '' !== trim( (string) $i['title'] );
		} ) );
		if ( ! $steps ) {
			$this->empty_note( 'هنوز مرحله‌ای وارد نشده است.' );
			return;
		}
		$tag = in_array( $s['tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $s['tag'] : 'h3';
		$fx  = self::fx_options();
		$n   = count( $steps );
		echo '<div class="ay-pr" data-ay-process="' . esc_attr( wp_json_encode( array( 'tilt' => 'yes' === $s['tilt'] ) ) ) . '"><div class="ay-pr-media"><div class="ay-pr-stick"><div class="ay-pr-frame"><div class="ay-pr-screen">';
		foreach ( $steps as $i => $st ) {
			$f = isset( $fx[ $st['fx'] ] ) ? $st['fx'] : 'brush';
			echo '<figure class="ay-pr-shot ay-pfx-' . esc_attr( $f ) . ( $i ? '' : ' is-on' ) . '" data-i="' . (int) $i . '" style="--px:' . (float) $this->size( $st['pen_x'], 60 ) . '%;--py:' . (float) $this->size( $st['pen_y'], 50 ) . '%;--pos:' . esc_attr( in_array( $st['focus'], array( 'top', 'bottom', 'left', 'right' ), true ) ? $st['focus'] : 'center' ) . '">';
			if ( ! empty( $st['image']['url'] ) ) {
				$alt = ! empty( $st['image']['id'] ) ? get_post_meta( absint( $st['image']['id'] ), '_wp_attachment_image_alt', true ) : '';
				echo '<img src="' . esc_url( $st['image']['url'] ) . '" alt="' . esc_attr( $alt ? $alt : $st['title'] ) . '"' . ( $i ? ' loading="lazy"' : '' ) . '>';
			}
			echo '</figure>';
		}
		echo '<span class="ay-pr-sheen" aria-hidden="true"></span></div>';
		if ( 'yes' === $s['pen'] ) {
			echo '<svg class="ay-pr-pen" viewBox="-20 -100 120 120" aria-hidden="true" focusable="false">' . ay_stylus_svg( 'ay-pen' ) . '</svg>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
		if ( 'yes' === $s['chips'] ) {
			echo '<div class="ay-pr-chips" aria-hidden="true"><span class="ay-pr-chips-t">' . esc_html( $s['chips_title'] ) . '</span>';
			foreach ( array_reverse( $steps, true ) as $i => $st ) {
				echo '<span class="ay-pr-chip' . ( $i ? '' : ' is-on' ) . '" data-i="' . (int) $i . '"><i></i>' . esc_html( $st['eyebrow'] ) . '</span>';
			}
			echo '</div>';
		}
		if ( 'yes' === $s['badge'] ) {
			echo '<div class="ay-pr-badges" aria-hidden="true">';
			foreach ( $steps as $i => $st ) {
				if ( ! empty( $st['motion'] ) && isset( ay_motion_types()[ $st['motion'] ] ) ) {
					echo '<div class="ay-pr-badge ay-mo ay-mo-' . esc_attr( $st['motion'] ) . ( $i ? '' : ' is-on is-play' ) . '" data-i="' . (int) $i . '" data-type="' . esc_attr( $st['motion'] ) . '">' . ay_motion_svg( $st['motion'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			echo '</div>';
		}
		if ( 'yes' === $s['counter'] ) {
			echo '<div class="ay-pr-count" aria-hidden="true"><span class="ay-pr-cur">01</span><span class="ay-pr-bar"><i></i></span><span>' . esc_html( sprintf( '%02d', $n ) ) . '</span></div>';
		}
		echo '</div></div><ol class="ay-pr-steps">';
		foreach ( $steps as $i => $st ) {
			echo '<li class="ay-pr-step' . ( $i ? '' : ' is-on' ) . '" data-i="' . (int) $i . '">';
			if ( 'yes' === $s['numbers'] ) {
				echo '<span class="ay-pr-num" aria-hidden="true">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</span>';
			}
			if ( '' !== trim( (string) $st['eyebrow'] ) ) {
				echo '<span class="ay-pr-eye">' . esc_html( $st['eyebrow'] ) . '</span>';
			}
			echo '<' . $tag . ' class="ay-pr-title">' . esc_html( $st['title'] ) . '</' . $tag . '><p class="ay-pr-text">' . esc_html( $st['text'] ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( ! empty( $st['image']['url'] ) ) {
				echo '<img class="ay-pr-inline" src="' . esc_url( $st['image']['url'] ) . '" alt="" loading="lazy">';
			}
			echo '</li>';
		}
		echo '</ol></div>';
	}
}

/* =========================================================================
 * Sketch ↔ jewel reveal
 * ====================================================================== */
class Compare extends Base {

	public function get_name() {
		return 'ay-compare';
	}

	public function get_title() {
		return 'از اسکچ تا جواهر آرزو';
	}

	public function get_icon() {
		return 'eicon-image-before-after';
	}

	protected function register_controls() {
		$this->sec( 's_main', 'تصاویر' );
		$this->sel( 'source', 'تصویر «اسکچ» از کجا بیاید', array( 'auto' => 'خودکار: خط‌های همان تصویر نهایی استخراج می‌شود', 'two' => 'دو تصویر جدا (اسکچ و نهایی)' ), 'auto' );
		$this->media( 'after', 'تصویر نهایی', ay_asset( 'img/studio/necklace-detail.webp' ) );
		$this->media( 'before', 'تصویر اسکچ', '', array( 'condition' => array( 'source' => 'two' ) ) );
		$this->sel( 'line', 'رنگ خط‌های اسکچ خودکار', array( 'light' => 'خط روشن روی زمینهٔ تیره', 'dark' => 'خط تیره روی زمینهٔ روشن' ), 'light', array( 'condition' => array( 'source' => 'auto' ) ) );
		$this->add_control( 'detail', array( 'label' => 'شدت خط‌های اسکچ خودکار', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.5, 'max' => 6, 'step' => 0.1 ) ), 'default' => array( 'size' => 2.4 ), 'condition' => array( 'source' => 'auto' ) ) );
		$this->txt( 'l_before', 'برچسب سمت اسکچ', 'Sketch', array( 'label_block' => false ) );
		$this->txt( 'l_after', 'برچسب سمت نهایی', 'Final', array( 'label_block' => false ) );
		$this->done();
		$this->sec( 's_behave', 'رفتار' );
		$this->sel( 'mode', 'نحوهٔ حرکت خط جداکننده', array( 'auto' => 'خودکار رفت و برگشت + کشیدن با موس', 'drag' => 'فقط کشیدن با موس یا انگشت', 'scroll' => 'همراه با اسکرول صفحه' ), 'auto' );
		$this->add_control( 'start', array( 'label' => 'جای شروع خط (درصد)', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'default' => array( 'size' => 50 ) ) );
		$this->num( 'dur', 'مدت یک رفت و برگشت (ثانیه)', 7, 2, 60, 0.5, array( 'condition' => array( 'mode' => 'auto' ) ) );
		$this->sw( 'pen', 'قلم روی خط جداکننده', 'yes' );
		$this->add_control( 'aspect', array( 'label' => 'نسبت قاب (عرض ÷ ارتفاع)', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.4, 'max' => 2.5, 'step' => 0.01 ) ), 'default' => array( 'size' => 0.8 ), 'selectors' => array( '{{WRAPPER}}' => '--ay-cp-aspect: {{SIZE}};' ) ) );
		$this->cssvar( 'maxw', 'بیشترین عرض', '--ay-cp-max', 200, 1600, 10, 560 );
		$this->done();
		$this->sec( 'st_main', 'ظاهر', true );
		$this->box_style( 'frame', 'قاب', '.ay-cp', false );
		$this->color( 'bar', 'رنگ خط جداکننده', '--ay-cp-bar' );
		$this->color( 'sk_bg', 'رنگ زمینهٔ سمت اسکچ', '--ay-cp-bg' );
		$this->color( 'pen_c', 'رنگ بدنهٔ قلم', '--m-pen' );
		$this->text_style( 'label', 'برچسب‌ها', '.ay-cp-label' );
		$this->done();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$a = isset( $s['after']['url'] ) ? $s['after']['url'] : '';
		if ( ! $a ) {
			$this->empty_note( 'تصویر نهایی را انتخاب کنید.' );
			return;
		}
		$two = 'two' === $s['source'] && ! empty( $s['before']['url'] );
		$uid = 'aycp' . $this->get_id();
		$k   = $this->size( $s['detail'], 2.4 );
		$cfg = array( 'mode' => in_array( $s['mode'], array( 'auto', 'drag', 'scroll' ), true ) ? $s['mode'] : 'auto', 'start' => $this->size( $s['start'], 50 ), 'dur' => max( 2, (float) $s['dur'] ) );
		echo '<div class="ay-cp' . ( 'dark' === $s['line'] ? ' ay-cp-dark' : '' ) . '" data-ay-compare="' . esc_attr( wp_json_encode( $cfg ) ) . '" style="--cp:' . (float) $cfg['start'] . '%">';
		if ( ! $two ) {
			/* line art taken from the picture itself: edge detection, then to one tone */
			$m = 'dark' === $s['line'] ? '-' . $k . ' -' . $k . ' -' . $k . ' 0 1.3' : $k . ' ' . $k . ' ' . $k . ' 0 -0.3';   /* the offset drops paper grain */
			echo '<svg class="ay-cp-defs" width="0" height="0" aria-hidden="true" focusable="false"><filter id="' . esc_attr( $uid ) . '" color-interpolation-filters="sRGB"><feColorMatrix type="saturate" values="0"/><feGaussianBlur stdDeviation="1.1"/><feConvolveMatrix order="3" preserveAlpha="true" kernelMatrix="-1 -1 -1 -1 8 -1 -1 -1 -1"/><feColorMatrix type="matrix" values="' . esc_attr( $m . ' ' . $m . ' ' . $m ) . ' 0 0 0 1 0"/></filter></svg>';
		}
		echo '<img class="ay-cp-after" src="' . esc_url( $a ) . '" alt="' . esc_attr( $s['l_after'] ) . '">';
		echo '<div class="ay-cp-before"><img src="' . esc_url( $two ? $s['before']['url'] : $a ) . '" alt="' . esc_attr( $s['l_before'] ) . '"' . ( $two ? '' : ' style="filter:url(#' . esc_attr( $uid ) . ')"' ) . '></div>';
		if ( '' !== trim( (string) $s['l_before'] ) ) {
			echo '<span class="ay-cp-label ay-cp-l1">' . esc_html( $s['l_before'] ) . '</span>';
		}
		if ( '' !== trim( (string) $s['l_after'] ) ) {
			echo '<span class="ay-cp-label ay-cp-l2">' . esc_html( $s['l_after'] ) . '</span>';
		}
		echo '<span class="ay-cp-bar" aria-hidden="true">' . ( 'yes' === $s['pen'] ? '<svg class="ay-cp-pen" viewBox="-20 -100 120 120">' . ay_stylus_svg( 'ay-pen' ) . '</svg>' : '' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<input class="ay-cp-range" type="range" min="0" max="100" step="0.5" value="' . (float) $cfg['start'] . '" aria-label="' . esc_attr( $s['l_before'] . ' / ' . $s['l_after'] ) . '"></div>';
	}
}

/* =========================================================================
 * Photo strip
 * ====================================================================== */
class Strip extends Base {

	public function get_name() {
		return 'ay-strip';
	}

	public function get_title() {
		return 'نوار عکس استودیو آرزو';
	}

	public function get_icon() {
		return 'eicon-carousel-loop';
	}

	public static function default_gallery() {
		$out = array();
		foreach ( array( '06-colour-medallions', '07-colour-necklace', '08-ring-colour', '09-ring-render', '10-ring-layers', '11-at-work', '12-tools', '02-paper-sketches' ) as $n ) {
			$out[] = array( 'id' => '', 'url' => ay_asset( 'img/studio/' . $n . '.webp' ) );
		}
		return $out;
	}

	protected function register_controls() {
		$this->sec( 's_main', 'عکس‌ها' );
		$this->add_control( 'gallery', array( 'label' => 'عکس‌ها', 'type' => CM::GALLERY, 'default' => self::default_gallery() ) );
		$this->cssvar( 'h', 'ارتفاع عکس‌ها', '--ay-sp-h', 80, 700, 5, 320, 'px', array( 'mobile_default' => array( 'unit' => 'px', 'size' => 200 ) ) );
		$this->cssvar( 'gap', 'فاصلهٔ عکس‌ها', '--ay-sp-gap2', 0, 80, 1, 18 );
		$this->num( 'dur', 'مدت یک دور کامل (ثانیه)', 60, 5, 400, 1, array( 'selectors' => array( '{{WRAPPER}}' => '--ay-sp-dur: {{VALUE}}s;' ) ) );
		$this->sel( 'dir', 'جهت حرکت', array( 'normal' => 'به چپ', 'reverse' => 'به راست' ), 'normal', array( 'selectors' => array( '{{WRAPPER}}' => '--ay-sp-dir: {{VALUE}};' ) ) );
		$this->cssvar( 'tilt', 'کجی یکی‌درمیان عکس‌ها', '--ay-sp-tilt', 0, 12, 0.5, 2.5, 'deg', array(), false );
		$this->sw( 'pause', 'توقف هنگام هاور', 'yes', array( 'prefix_class' => 'ay-strip-pause-' ) );
		$this->sw( 'mono', 'تک‌رنگ، و رنگی هنگام هاور', '', array( 'prefix_class' => 'ay-strip-mono-' ) );
		$this->sw( 'fade', 'محو شدن لبه‌های چپ و راست', 'yes', array( 'prefix_class' => 'ay-strip-fade-' ) );
		$this->done();
		$this->sec( 'st_main', 'قاب عکس', true );
		$this->box_style( 'pic', 'قاب', '.ay-strip-item', false );
		$this->done();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$imgs = array();
		foreach ( (array) $s['gallery'] as $g ) {
			$url = ! empty( $g['id'] ) ? wp_get_attachment_image_url( absint( $g['id'] ), 'large' ) : ( isset( $g['url'] ) ? $g['url'] : '' );
			if ( $url ) {
				$alt    = ! empty( $g['id'] ) ? (string) get_post_meta( absint( $g['id'] ), '_wp_attachment_image_alt', true ) : '';
				$imgs[] = array( $url, $alt );
			}
		}
		if ( ! $imgs ) {
			$this->empty_note( 'چند عکس برای نوار انتخاب کنید.' );
			return;
		}
		echo '<div class="ay-strip"><div class="ay-strip-track">';
		for ( $c = 0; $c < 2; $c++ ) {
			foreach ( $imgs as $im ) {
				echo '<span class="ay-strip-item"' . ( $c ? ' aria-hidden="true"' : '' ) . '><img src="' . esc_url( $im[0] ) . '" alt="' . esc_attr( $c ? '' : $im[1] ) . '" loading="lazy"></span>';
			}
		}
		echo '</div></div>';
	}
}
