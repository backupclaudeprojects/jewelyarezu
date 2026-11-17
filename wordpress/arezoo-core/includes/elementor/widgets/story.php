<?php
/**
 * Sticky portrait story: the portrait stays on screen while chapters scroll past it with
 * classic, restrained motions. Used by two About-page demos.
 *
 * @package ArezooCore
 */

namespace Arezoo\Widgets;

use Elementor\Controls_Manager as CM;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Story extends Base {

	public function get_name() {
		return 'ay-story';
	}

	public function get_title() {
		return 'روایت با پرترهٔ چسبان آرزو';
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public static function default_chapters() {
		$t = Timeline::default_steps();
		return array(
			array( 'kicker' => 'About me', 'title' => 'Jewelry designer', 'text' => 'I specialize in jewelry design, concept development, and visual presentation.', 'motion' => '' ),
			array( 'kicker' => 'Services', 'title' => 'What I design', 'text' => 'My services include original jewelry concepts, bespoke design, collection development, and digital renderings tailored to each project’s creative direction and requirements.', 'motion' => 'sketch' ),
			array( 'kicker' => 'Collaboration', 'title' => 'Who I work with', 'text' => 'I collaborate with jewelry brands, manufacturers, and industry professionals on individual designs, exclusive collections, and creative development projects.', 'motion' => 'facets' ),
			array( 'kicker' => 'Process', 'title' => $t[2]['title'], 'text' => $t[0]['text'] . ' ' . $t[2]['text'], 'motion' => 'wire' ),
			array( 'kicker' => 'Presentation', 'title' => $t[3]['title'], 'text' => $t[3]['text'] . ' ' . $t[4]['text'], 'motion' => 'render' ),
		);
	}

	protected function register_controls() {
		$this->sec( 's_photo', 'پرتره' );
		$this->media( 'image', 'عکس شما', ay_asset( 'img/studio/11-at-work.webp' ) );
		$this->sel( 'style', 'سبک', array( 'arch' => 'کلاسیک: قاب طاقی با خط‌های ظریف', 'gallery' => 'گالری: قاب موزه‌ای با پاسپارتو' ), 'arch', array( 'prefix_class' => 'ay-sy-' ) );
		$this->sel( 'side', 'جای پرتره', array( 'left' => 'چپ', 'right' => 'راست' ), 'left', array( 'prefix_class' => 'ay-sy-side-' ) );
		$this->sw( 'sy_pin', 'پرتره هنگام اسکرول چسبان بماند', 'yes' );
		$this->cssvar( 'top', 'فاصلهٔ پرتره از بالای صفحه', '--ay-sy-top', 0, 300, 1, 110, 'px', array( 'condition' => array( 'sy_pin' => 'yes' ) ) );
		$this->cssvar( 'photo_w', 'سهم ستون پرتره', '--ay-sy-mw', 25, 60, 1, 40, '%' );
		$this->cssvar( 'gap', 'فاصلهٔ دو ستون', '--ay-sy-gap', 0, 180, 2, 96 );
		$this->add_control( 'aspect', array( 'label' => 'نسبت قاب (عرض ÷ ارتفاع)', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.5, 'max' => 1.4, 'step' => 0.01 ) ), 'default' => array( 'size' => 0.76 ), 'selectors' => array( '{{WRAPPER}}' => '--ay-sy-aspect: {{SIZE}};' ) ) );
		$this->sel( 'focus', 'بخش مهم عکس', array( 'center' => 'وسط', 'top' => 'بالا', 'bottom' => 'پایین', 'left' => 'چپ', 'right' => 'راست' ), 'center', array( 'selectors' => array( '{{WRAPPER}} .ay-sy-pic img' => 'object-position: {{VALUE}};' ) ) );
		$this->txt( 'name', 'نام روی پلاک', 'Arezoo Yousefi' );
		$this->txt( 'role', 'عنوان زیر نام', 'Jewelry Designer' );
		$this->done();

		$this->sec( 's_ch', 'فصل‌ها' );
		$rep = new Repeater();
		$rep->add_control( 'kicker', array( 'label' => 'برچسب کوتاه', 'type' => CM::TEXT, 'default' => 'Chapter' ) );
		$rep->add_control( 'title', array( 'label' => 'عنوان', 'type' => CM::TEXT, 'default' => 'Title', 'label_block' => true ) );
		$rep->add_control( 'text', array( 'label' => 'متن', 'type' => CM::TEXTAREA, 'rows' => 6, 'default' => '' ) );
		$rep->add_control( 'image', array( 'label' => 'عکس کنار متن (اختیاری)', 'type' => CM::MEDIA ) );
		$rep->add_control( 'motion', array( 'label' => 'موشن طراحی کنار متن', 'type' => CM::SELECT, 'options' => array_merge( array( '' => 'بدون موشن' ), ay_motion_types() ), 'default' => '' ) );
		$this->add_control( 'chapters', array( 'label' => 'فصل‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::default_chapters(), 'title_field' => '{{{ kicker }}} · {{{ title }}}' ) );
		$this->sel( 'tag', 'تگ عنوان', array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ), 'h2' );
		$this->sel( 'numerals', 'شمارهٔ فصل', array( 'roman' => 'رومی (I, II, III)', 'digit' => 'عددی (01, 02)', 'none' => 'بدون شماره' ), 'roman' );
		$this->done();

		$this->sec( 's_motion', 'موشن‌ها' );
		$this->sel( 'enter', 'ورود هر فصل', array( 'rise' => 'کلاسیک: خط کشیده می‌شود، متن آرام بالا می‌آید', 'curtain' => 'پرده از زیر هر سطر', 'fade' => 'محو شدن نرم', 'none' => 'بدون حرکت' ), 'rise', array( 'prefix_class' => 'ay-sy-in-' ) );
		$this->sel( 'photo_fx', 'حرکت پرتره با اسکرول', array( 'zoom' => 'زوم خیلی آرام', 'pan' => 'جابه‌جایی آرام داخل قاب', 'tone' => 'از تک‌رنگ به رنگی', 'none' => 'ثابت' ), 'zoom', array( 'prefix_class' => 'ay-sy-pfx-' ) );
		$this->sw( 'progress', 'خط پیشرفت و شمارهٔ فصل کنار پرتره', 'yes' );
		$this->sw( 'ornament', 'تزئین چرخان پشت پرتره', 'yes' );
		$this->cssvar( 'ch_gap', 'فاصلهٔ فصل‌ها', '--ay-sy-chgap', 20, 60, 1, 34, 'vh' );
		$this->cssvar( 'dim', 'کم‌رنگی فصل‌های غیرفعال', '--ay-sy-dim', 0.1, 1, 0.05, 0.4, '', array(), false );
		$this->done();

		$this->sec( 'st_photo', 'قاب پرتره', true );
		$this->color( 'frame_c', 'رنگ خط‌های قاب', '--ay-sy-line' );
		$this->color( 'mat_c', 'رنگ پاسپارتو / زمینهٔ قاب', '--ay-sy-mat' );
		$this->cssvar( 'mat_w', 'پهنای پاسپارتو', '--ay-sy-matw', 0, 60, 1, 18 );
		$this->filters( 'photo_f', 'فیلتر عکس', '.ay-sy-pic img' );
		$this->text_style( 'pname', 'نام روی پلاک', '.ay-sy-name' );
		$this->text_style( 'prole', 'عنوان زیر نام', '.ay-sy-role' );
		$this->done();
		$this->sec( 'st_text', 'متن فصل‌ها', true );
		$this->text_style( 'num', 'شمارهٔ فصل', '.ay-sy-num' );
		$this->text_style( 'kick', 'برچسب کوتاه', '.ay-sy-kick' );
		$this->text_style( 'title', 'عنوان', '.ay-sy-title' );
		$this->text_style( 'text', 'متن', '.ay-sy-text' );
		$this->color( 'rule', 'رنگ خط جداکننده', '--ay-sy-rule' );
		$this->color( 'm_line', 'رنگ خط موشن‌ها', '--m-line' );
		$this->color( 'm_accent', 'رنگ تأکید موشن‌ها', '--m-accent' );
		$this->done();
	}

	private function roman( $n ) {
		$map = array( 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I' );
		$out = '';
		foreach ( $map as $v => $r ) {
			while ( $n >= $v ) {
				$out .= $r;
				$n   -= $v;
			}
		}
		return $out;
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$ch = array_values( array_filter( (array) $s['chapters'], function ( $c ) {
			return '' !== trim( (string) $c['title'] ) || '' !== trim( (string) $c['text'] );
		} ) );
		$tag = in_array( $s['tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $s['tag'] : 'h2';
		$n   = count( $ch );
		echo '<div class="ay-sy' . ( 'yes' === $s['sy_pin'] ? ' ay-sy-sticky' : '' ) . '" data-ay-story="1"><div class="ay-sy-media"><div class="ay-sy-stick">';
		if ( 'yes' === $s['ornament'] ) {
			echo '<svg class="ay-sy-orn" viewBox="0 0 200 200" aria-hidden="true" focusable="false"><circle cx="100" cy="100" r="96" fill="none" stroke="currentColor" stroke-width=".4" stroke-dasharray="1 4"/><circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" stroke-width=".3"/><g fill="none" stroke="currentColor" stroke-width=".4">';
			for ( $i = 0; $i < 24; $i++ ) {
				echo '<path d="M100 4V' . ( $i % 6 ? 9 : 14 ) . '" transform="rotate(' . ( $i * 15 ) . ' 100 100)"/>';
			}
			echo '</g></svg>';
		}
		echo '<figure class="ay-sy-frame"><span class="ay-sy-pic">';
		if ( ! empty( $s['image']['url'] ) ) {
			$alt = ! empty( $s['image']['id'] ) ? get_post_meta( absint( $s['image']['id'] ), '_wp_attachment_image_alt', true ) : '';
			echo '<img src="' . esc_url( $s['image']['url'] ) . '" alt="' . esc_attr( $alt ? $alt : $s['name'] ) . '">';
		}
		echo '</span><i class="ay-sy-l1" aria-hidden="true"></i><i class="ay-sy-l2" aria-hidden="true"></i>';
		if ( '' !== trim( (string) $s['name'] ) || '' !== trim( (string) $s['role'] ) ) {
			echo '<figcaption class="ay-sy-plate"><span class="ay-sy-name">' . esc_html( $s['name'] ) . '</span><span class="ay-sy-role">' . esc_html( $s['role'] ) . '</span></figcaption>';
		}
		echo '</figure>';
		if ( 'yes' === $s['progress'] && $n > 1 ) {
			echo '<div class="ay-sy-prog" aria-hidden="true"><span class="ay-sy-cur">01</span><span class="ay-sy-track">';
			for ( $i = 0; $i < $n; $i++ ) {
				echo '<i class="ay-sy-dot' . ( $i ? '' : ' is-on' ) . '"></i>';
			}
			echo '</span><span>' . esc_html( sprintf( '%02d', $n ) ) . '</span></div>';
		}
		echo '</div></div><div class="ay-sy-chs">';
		foreach ( $ch as $i => $c ) {
			echo '<section class="ay-sy-ch' . ( $i ? '' : ' is-on' ) . '">';
			if ( 'none' !== $s['numerals'] ) {
				echo '<span class="ay-sy-num" aria-hidden="true">' . esc_html( 'digit' === $s['numerals'] ? sprintf( '%02d', $i + 1 ) : $this->roman( $i + 1 ) ) . '</span>';
			}
			echo '<i class="ay-sy-rule" aria-hidden="true"></i>';
			if ( '' !== trim( (string) $c['kicker'] ) ) {
				echo '<span class="ay-sy-kick"><span>' . esc_html( $c['kicker'] ) . '</span></span>';
			}
			echo '<' . $tag . ' class="ay-sy-title"><span>' . esc_html( $c['title'] ) . '</span></' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="ay-sy-body"><p class="ay-sy-text">' . wp_kses_post( nl2br( esc_html( $c['text'] ) ) ) . '</p>';
			if ( ! empty( $c['motion'] ) && isset( ay_motion_types()[ $c['motion'] ] ) ) {
				echo '<div class="ay-sy-mo ay-mo ay-mo-' . esc_attr( $c['motion'] ) . '" data-ay-motion="inview" data-type="' . esc_attr( $c['motion'] ) . '" aria-hidden="true">' . ay_motion_svg( $c['motion'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div>';
			if ( ! empty( $c['image']['url'] ) ) {
				echo '<span class="ay-sy-img"><img src="' . esc_url( $c['image']['url'] ) . '" alt="" loading="lazy"></span>';
			}
			echo '</section>';
		}
		echo '</div></div>';
	}
}
