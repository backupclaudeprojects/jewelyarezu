<?php
/**
 * Widgets for the single work template. Each one shows one part of the current work,
 * so the template stays a normal Elementor layout the owner can rearrange.
 *
 * @package ArezooCore
 */

namespace Arezoo\Widgets;

use Elementor\Controls_Manager as CM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
 * Gallery
 * ====================================================================== */
class Work_Gallery extends Base {

	public function get_name() {
		return 'ay-work-gallery';
	}

	public function get_title() {
		return 'گالری اثر آرزو';
	}

	public function get_icon() {
		return 'eicon-gallery-justified';
	}

	protected function register_controls() {
		$this->sec( 's_main', 'گالری' );
		$this->sel( 'layout', 'چیدمان', array( 'stage' => 'تصویر بزرگ + تصاویر کوچک زیر آن', 'side' => 'تصاویر کوچک کنار تصویر بزرگ', 'grid' => 'همهٔ تصاویر در شبکه', 'strip' => 'نوار افقی لغزنده' ), 'stage', array( 'prefix_class' => 'ay-wgl-' ) );
		$this->add_control( 'aspect', array( 'label' => 'نسبت قاب تصویر بزرگ', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.5, 'max' => 2, 'step' => 0.01 ) ), 'default' => array( 'size' => 1 ), 'selectors' => array( '{{WRAPPER}}' => '--ay-wg-aspect: {{SIZE}};' ) ) );
		$this->cssvar( 'pad', 'فاصلهٔ تصویر از لبهٔ قاب', '--ay-wg-pad', 0, 30, 1, 9, '%' );
		$this->cssvar( 'thumb', 'اندازهٔ تصاویر کوچک', '--ay-wg-thumb', 40, 200, 1, 76 );
		$this->cssvar( 'gap', 'فاصله‌ها', '--ay-wg-gap', 0, 40, 1, 12 );
		$this->add_responsive_control( 'columns', array( 'label' => 'تعداد ستون شبکه', 'type' => CM::NUMBER, 'min' => 1, 'max' => 5, 'default' => 2, 'selectors' => array( '{{WRAPPER}}' => '--ay-wg-cols: {{VALUE}};' ), 'condition' => array( 'layout' => 'grid' ) ) );
		$this->sw( 'lightbox', 'بزرگ‌نمایی با کلیک (لایت‌باکس المنتور)', 'yes' );
		$this->sw( 'sticky', 'ثابت ماندن هنگام اسکرول (در دسکتاپ)', 'yes', array( 'prefix_class' => 'ay-wg-sticky-' ) );
		$this->cssvar( 'sticky_top', 'فاصله از بالای صفحه هنگام ثابت ماندن', '--ay-wg-top', 0, 300, 1, 104, 'px', array( 'condition' => array( 'sticky' => 'yes' ) ) );
		$this->done();
		$this->sec( 'st_main', 'قاب‌ها', true );
		$this->box_style( 'stage', 'قاب تصویر بزرگ', '.ay-wg-stage', false );
		$this->box_style( 'th', 'قاب تصاویر کوچک', '.ay-wg-thumb', false );
		$this->color( 'th_on', 'رنگ حاشیهٔ تصویر انتخاب‌شده', array( '.ay-wg-thumb.is-on' => 'border-color' ) );
		$this->done();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = ay_current_work_id();
		if ( ! $id ) {
			$this->empty_note( 'برای پیش‌نمایش این ویجت دست‌کم یک اثر لازم است.' );
			return;
		}
		$ids = ay_work_gallery_ids( $id );
		if ( ! $ids ) {
			$this->empty_note( 'این اثر هنوز تصویری ندارد.' );
			return;
		}
		$title = get_the_title( $id );
		$full  = function ( $att ) {
			return (string) wp_get_attachment_image_url( $att, 'full' );
		};
		$lb    = 'yes' === $s['lightbox'];
		$open  = function ( $att ) use ( $lb, $full, $id ) {
			return $lb ? '<a class="ay-wg-zoom" href="' . esc_url( $full( $att ) ) . '" data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="ay-' . (int) $id . '">' : '<span class="ay-wg-zoom">';
		};
		$close = $lb ? '</a>' : '</span>';
		$img   = function ( $att, $size ) use ( $title ) {
			$alt = get_post_meta( $att, '_wp_attachment_image_alt', true );
			return wp_get_attachment_image( $att, $size, false, array( 'alt' => $alt ? $alt : $title ) );
		};
		echo '<div class="ay-wg" data-ay-gallery>';
		if ( in_array( $s['layout'], array( 'grid', 'strip' ), true ) ) {
			echo '<div class="ay-wg-all">';
			foreach ( $ids as $att ) {
				echo '<div class="ay-wg-stage">' . $open( $att ) . $img( $att, 'large' ) . $close . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div>';
		} else {
			echo '<div class="ay-wg-stage">';
			foreach ( $ids as $k => $att ) {
				echo '<div class="ay-wg-slide' . ( $k ? '' : ' is-on' ) . '" data-k="' . (int) $k . '">' . $open( $att ) . $img( $att, 'large' ) . $close . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div>';
			if ( count( $ids ) > 1 ) {
				echo '<div class="ay-wg-thumbs">';
				foreach ( $ids as $k => $att ) {
					echo '<button type="button" class="ay-wg-thumb' . ( $k ? '' : ' is-on' ) . '" data-k="' . (int) $k . '" aria-label="' . esc_attr( 'Show image ' . ( $k + 1 ) ) . '">' . wp_get_attachment_image( $att, 'thumbnail', false, array( 'alt' => '' ) ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '</div>';
			}
		}
		echo '</div>';
	}
}

/* =========================================================================
 * One field of the work as text
 * ====================================================================== */
class Work_Field extends Base {

	public function get_name() {
		return 'ay-work-field';
	}

	public function get_title() {
		return 'فیلد اثر آرزو';
	}

	public function get_icon() {
		return 'eicon-post-title';
	}

	protected function register_controls() {
		$this->sec( 's_main', 'فیلد' );
		$this->sel( 'field', 'چه چیزی نمایش داده شود', array( 'title' => 'نام اثر', 'desc' => 'توضیح اثر', 'crumbs' => 'مسیر (Collections / کالکشن)', 'collection' => 'کالکشن', 'type' => 'نوع', 'materials' => 'متریال', 'gemstones' => 'سنگ‌ها', 'technique' => 'تکنیک', 'year' => 'سال' ), 'title' );
		$this->sel( 'tag', 'تگ HTML', array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'p' => 'p', 'div' => 'div', 'span' => 'span' ), 'h1', array( 'condition' => array( 'field!' => 'desc' ) ) );
		$this->txt( 'before', 'متن قبل', '' );
		$this->txt( 'after', 'متن بعد', '' );
		$this->txt( 'crumb_root', 'واژهٔ اول مسیر', 'Collections', array( 'condition' => array( 'field' => 'crumbs' ) ) );
		$this->add_control( 'crumb_link', array( 'label' => 'لینک واژهٔ اول مسیر', 'type' => CM::URL, 'dynamic' => array( 'active' => false ), 'condition' => array( 'field' => 'crumbs' ) ) );
		$this->sw( 'link_terms', 'کالکشن و نوع به صفحهٔ خودشان لینک شوند', 'yes', array( 'condition' => array( 'field' => array( 'collection', 'type', 'crumbs' ) ) ) );
		$this->align( 'align', '.ay-wf' );
		$this->done();
		$this->sec( 'st_main', 'متن', true );
		$this->typo( 't', 'تایپوگرافی', '.ay-wf' );
		$this->color( 'c', 'رنگ', array( '.ay-wf' => 'color' ) );
		$this->color( 'lc', 'رنگ لینک', array( '.ay-wf a' => 'color' ) );
		$this->color( 'lch', 'رنگ لینک هنگام هاور', array( '.ay-wf a:hover' => 'color' ) );
		$this->cssvar( 'maxw', 'بیشترین عرض متن', '--ay-wf-max', 10, 120, 1, null, 'ch' );
		$this->done();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = ay_current_work_id();
		if ( ! $id ) {
			$this->empty_note( 'برای پیش‌نمایش این ویجت دست‌کم یک اثر لازم است.' );
			return;
		}
		$w     = ay_work( $id );
		$link  = 'yes' === $s['link_terms'];
		$terms = function ( $list ) use ( $link ) {
			$out = array();
			foreach ( $list as $t ) {
				$url   = get_term_link( $t );
				$out[] = ( $link && ! is_wp_error( $url ) ) ? '<a href="' . esc_url( $url ) . '">' . esc_html( $t->name ) . '</a>' : esc_html( $t->name );
			}
			return implode( ', ', $out );
		};
		switch ( $s['field'] ) {
			case 'desc':
				$html = wp_kses_post( $w['desc'] );
				break;
			case 'crumbs':
				$root = esc_html( $s['crumb_root'] );
				if ( ! empty( $s['crumb_link']['url'] ) ) {
					$root = '<a href="' . esc_url( $s['crumb_link']['url'] ) . '">' . $root . '</a>';
				}
				$html = $root . ( $w['collections'] ? ' / ' . $terms( $w['collections'] ) : '' );
				break;
			case 'collection':
				$html = $terms( $w['collections'] );
				break;
			case 'type':
				$html = $terms( $w['types'] );
				break;
			case 'title':
				$html = esc_html( $w['title'] );
				break;
			default:
				$html = isset( $w[ $s['field'] ] ) ? esc_html( $w[ $s['field'] ] ) : '';
		}
		if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
			$this->empty_note( 'این فیلد برای این اثر خالی است.' );
			return;
		}
		$tag = 'desc' === $s['field'] ? 'div' : ( in_array( $s['tag'], array( 'h1', 'h2', 'h3', 'p', 'div', 'span' ), true ) ? $s['tag'] : 'div' );
		echo '<' . $tag . ' class="ay-wf ay-wf-' . esc_attr( $s['field'] ) . '">' . esc_html( $s['before'] ) . $html . esc_html( $s['after'] ) . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/* =========================================================================
 * Details boxes
 * ====================================================================== */
class Work_Specs extends Base {

	public function get_name() {
		return 'ay-work-specs';
	}

	public function get_title() {
		return 'مشخصات اثر آرزو';
	}

	public function get_icon() {
		return 'eicon-info-box';
	}

	protected function register_controls() {
		$this->sec( 's_main', 'مشخصات' );
		$this->add_control( 'fields', array( 'label' => 'کدام مشخصات', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => array( 'collection' => 'کالکشن', 'type' => 'نوع', 'materials' => 'متریال', 'gemstones' => 'سنگ‌ها', 'technique' => 'تکنیک', 'year' => 'سال' ), 'default' => array( 'collection', 'type', 'materials', 'gemstones', 'technique', 'year' ) ) );
		$this->sw( 'extra', 'نمایش «جزئیات دلخواه» اثر', 'yes' );
		foreach ( array( 'collection' => 'Collection', 'type' => 'Type', 'materials' => 'Materials', 'gemstones' => 'Gemstones', 'technique' => 'Technique', 'year' => 'Year' ) as $k => $v ) {
			$this->txt( 'l_' . $k, 'برچسب «' . $v . '»', $v, array( 'label_block' => false ) );
		}
		$this->add_responsive_control( 'columns', array( 'label' => 'تعداد ستون', 'type' => CM::NUMBER, 'min' => 1, 'max' => 4, 'default' => 2, 'mobile_default' => 1, 'selectors' => array( '{{WRAPPER}}' => '--ay-sp-cols: {{VALUE}};' ) ) );
		$this->cssvar( 'gap', 'فاصلهٔ باکس‌ها', '--ay-sp-gap', 0, 40, 1, 12 );
		$this->done();

		$this->sec( 's_wash', 'پترن آبرنگی' );
		$this->sw( 'wash', 'پترن آبرنگی کمرنگ روی باکس‌ها', 'yes', array( 'prefix_class' => 'ay-wash-' ) );
		$this->color( 'wash_color', 'رنگ پترن', '--ay-wash', array( 'condition' => array( 'wash' => 'yes' ) ) );
		$this->cssvar( 'wash_op', 'شدت پترن', '--ay-wash-op', 0, 1, 0.01, 0.16, '', array( 'condition' => array( 'wash' => 'yes' ) ), false );
		$this->cssvar( 'wash_size', 'اندازهٔ لکه‌ها', '--ay-wash-size', 160, 900, 10, 420, 'px', array( 'condition' => array( 'wash' => 'yes' ) ), false );
		$this->done();

		$this->sec( 'st_main', 'باکس‌ها', true );
		$this->box_style( 'box', 'باکس', '.ay-sp' );
		$this->text_style( 'k', 'برچسب', '.ay-sp-k' );
		$this->text_style( 'v', 'مقدار', '.ay-sp-v' );
		$this->done();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = ay_current_work_id();
		if ( ! $id ) {
			$this->empty_note( 'برای پیش‌نمایش این ویجت دست‌کم یک اثر لازم است.' );
			return;
		}
		$w    = ay_work( $id );
		$rows = array();
		foreach ( (array) $s['fields'] as $f ) {
			if ( 'collection' === $f ) {
				$val = implode( ', ', wp_list_pluck( $w['collections'], 'name' ) );
			} elseif ( 'type' === $f ) {
				$val = implode( ', ', wp_list_pluck( $w['types'], 'name' ) );
			} else {
				$val = isset( $w[ $f ] ) ? $w[ $f ] : '';
			}
			if ( '' !== trim( (string) $val ) ) {
				$rows[] = array( isset( $s[ 'l_' . $f ] ) ? $s[ 'l_' . $f ] : $f, $val );
			}
		}
		if ( 'yes' === $s['extra'] ) {
			$rows = array_merge( $rows, ay_work_extra( $id ) );
		}
		if ( ! $rows ) {
			$this->empty_note( 'این اثر هنوز مشخصاتی ندارد.' );
			return;
		}
		echo '<ul class="ay-sps">';
		foreach ( $rows as $r ) {
			echo '<li class="ay-sp"><span class="ay-sp-k">' . esc_html( $r[0] ) . '</span><span class="ay-sp-v">' . esc_html( $r[1] ) . '</span></li>';
		}
		echo '</ul>';
	}
}

/* =========================================================================
 * Previous / next
 * ====================================================================== */
class Work_Nav extends Base {

	public function get_name() {
		return 'ay-work-nav';
	}

	public function get_title() {
		return 'اثر قبلی و بعدی آرزو';
	}

	public function get_icon() {
		return 'eicon-post-navigation';
	}

	protected function register_controls() {
		$this->sec( 's_main', 'ناوبری' );
		$this->txt( 'prev', 'برچسب قبلی', 'Previous', array( 'label_block' => false ) );
		$this->txt( 'next', 'برچسب بعدی', 'Next', array( 'label_block' => false ) );
		$this->sw( 'titles', 'نمایش نام اثر', 'yes' );
		$this->sw( 'same', 'فقط در همان کالکشن', '' );
		$this->sw( 'loop', 'بعد از آخرین اثر به اولین برگردد', 'yes' );
		$this->done();
		$this->sec( 'st_main', 'ظاهر', true );
		$this->text_style( 'l', 'برچسب', '.ay-wn-l' );
		$this->text_style( 't', 'نام اثر', '.ay-wn-t', true );
		$this->color( 'line', 'رنگ خط بالا', array( '.ay-wn' => 'border-top-color' ) );
		$this->done();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = ay_current_work_id();
		if ( ! $id ) {
			$this->empty_note( 'برای پیش‌نمایش این ویجت دست‌کم یک اثر لازم است.' );
			return;
		}
		$args = array();
		if ( 'yes' === $s['same'] ) {
			$w = ay_work( $id );
			if ( $w['collections'] ) {
				$args['collection'] = wp_list_pluck( $w['collections'], 'term_id' );
			}
		}
		$ids = ay_query_works( $args );
		$pos = array_search( $id, $ids, true );
		if ( false === $pos || count( $ids ) < 2 ) {
			return;
		}
		$n    = count( $ids );
		$loop = 'yes' === $s['loop'];
		$prev = $pos > 0 ? $ids[ $pos - 1 ] : ( $loop ? $ids[ $n - 1 ] : 0 );
		$next = $pos < $n - 1 ? $ids[ $pos + 1 ] : ( $loop ? $ids[0] : 0 );
		$item = function ( $pid, $label, $class ) use ( $s ) {
			if ( ! $pid ) {
				return '<span class="' . $class . '"></span>';
			}
			return '<a class="' . $class . '" href="' . esc_url( get_permalink( $pid ) ) . '"><span class="ay-wn-l">' . esc_html( $label ) . '</span>' . ( 'yes' === $s['titles'] ? '<span class="ay-wn-t">' . esc_html( get_the_title( $pid ) ) . '</span>' : '' ) . '</a>';
		};
		echo '<nav class="ay-wn" aria-label="Other pieces">' . $item( $prev, $s['prev'], 'ay-wn-prev' ) . $item( $next, $s['next'], 'ay-wn-next' ) . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
