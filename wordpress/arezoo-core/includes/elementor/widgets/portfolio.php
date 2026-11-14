<?php
/**
 * Portfolio widgets: horizontal portfolio driven by page scroll, stacked cards,
 * the collections archive with filters, and related works.
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
 * Horizontal portfolio
 * ====================================================================== */
class Hscroll extends Base {

	public function get_name() {
		return 'ay-hscroll';
	}

	public function get_title() {
		return 'پورتفولیوی افقی آرزو';
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	protected function register_controls() {
		$this->sec( 's_query', 'آثار' );
		$this->query_controls( -1 );
		$this->done();

		$this->sec( 's_rows', 'ردیف‌ها و حرکت' );
		$this->num( 'rows', 'تعداد ردیف', 2, 1, 4 );
		$this->sel( 'dir', 'جهت ردیف اول هنگام اسکرول به پایین', array( 'right' => 'به راست', 'left' => 'به چپ' ), 'right' );
		$this->sw( 'alternate', 'ردیف‌ها یکی‌درمیان برعکس حرکت کنند', 'yes' );
		$this->add_control( 'mult', array( 'label' => 'ضریب حرکت با اسکرول', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 1.5, 'step' => 0.05 ) ), 'default' => array( 'size' => 0.3 ), 'description' => 'هر ۱۰۰ پیکسل اسکرول صفحه، ردیف‌ها را به این نسبت جابه‌جا می‌کند.' ) );
		$this->num( 'repeat', 'تکرار آثار برای حلقهٔ بی‌درز', 3, 2, 6 );
		$this->num( 'shift', 'جابه‌جایی شروع ردیف‌های بعدی (تعداد کاشی)', 3, 0, 20 );
		$this->sw( 'auto', 'حرکت خودکار پس از توقف اسکرول', 'yes', array( 'separator' => 'before' ) );
		$this->num( 'auto_delay', 'چند ثانیه بعد از توقف اسکرول شروع شود', 3, 0, 60, 0.5, array( 'condition' => array( 'auto' => 'yes' ) ) );
		$this->num( 'auto_speed', 'سرعت حرکت خودکار (پیکسل در ثانیه)', 60, 5, 400, 5, array( 'condition' => array( 'auto' => 'yes' ) ) );
		$this->sw( 'pause_hover', 'توقف حرکت خودکار هنگام هاور', 'yes', array( 'condition' => array( 'auto' => 'yes' ) ) );
		$this->done();

		$this->sec( 's_tile', 'کاشی‌ها' );
		$this->cssvar( 'tile_w', 'عرض کاشی', '--ay-hz-w', 120, 800, 5, 420, 'px', array( 'tablet_default' => array( 'unit' => 'px', 'size' => 340 ), 'mobile_default' => array( 'unit' => 'px', 'size' => 250 ) ) );
		$this->cssvar( 'tile_h', 'ارتفاع کاشی', '--ay-hz-h', 80, 700, 5, 270, 'px', array( 'tablet_default' => array( 'unit' => 'px', 'size' => 220 ), 'mobile_default' => array( 'unit' => 'px', 'size' => 170 ) ) );
		$this->cssvar( 'gap', 'فاصلهٔ کاشی‌ها', '--ay-hz-gap', 0, 80, 1, 12 );
		$this->cssvar( 'img_pad', 'فاصلهٔ تصویر از لبهٔ کاشی', '--ay-hz-pad', 0, 30, 1, 9, '%' );
		$this->sw( 'caption', 'نمایش نام اثر', 'yes' );
		$this->sw( 'caption_col', 'نمایش کالکشن کنار نام', 'yes', array( 'condition' => array( 'caption' => 'yes' ) ) );
		$this->sw( 'link', 'کاشی‌ها به صفحهٔ اثر لینک شوند', 'yes' );
		$this->done();

		$this->sec( 'st_tile', 'کاشی', true );
		$this->color( 'bg1', 'رنگ کاشی ۱', '--ay-hz-bg1' );
		$this->color( 'bg2', 'رنگ کاشی ۲', '--ay-hz-bg2' );
		$this->color( 'bg3', 'رنگ کاشی ۳', '--ay-hz-bg3' );
		$this->cssvar( 'radius', 'گردی گوشه‌ها', '--ay-hz-radius', 0, 80, 1, 18 );
		$this->color( 'line', 'رنگ خط دور', '--ay-hz-line' );
		$this->cssvar( 'zoom', 'بزرگ‌نمایی تصویر هنگام هاور', '--ay-hz-zoom', 1, 1.4, 0.01, 1.06, '', array(), false );
		$this->text_style( 'cap', 'نام اثر', '.ay-hz-cap strong' );
		$this->text_style( 'capm', 'نام کالکشن', '.ay-hz-cap span' );
		$this->done();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$ids = $this->query_works( $s );
		if ( ! $ids ) {
			$this->empty_note( 'هنوز اثری برای نمایش نیست. از «آرزو ← آثار» اثر اضافه کنید.' );
			return;
		}
		$rows = max( 1, min( 4, (int) $s['rows'] ) );
		$rep  = max( 2, min( 6, (int) $s['repeat'] ) );
		$cfg  = array(
			'mult'  => $this->size( $s['mult'], 0.3 ),
			'dir'   => 'left' === $s['dir'] ? -1 : 1,
			'alt'   => 'yes' === $s['alternate'],
			'auto'  => 'yes' === $s['auto'],
			'delay' => (float) $s['auto_delay'],
			'speed' => (float) $s['auto_speed'],
			'hover' => 'yes' === $s['pause_hover'],
		);
		echo '<div class="ay-hz" data-ay-hz="' . esc_attr( wp_json_encode( $cfg ) ) . '">';
		for ( $r = 0; $r < $rows; $r++ ) {
			$set = $ids;
			if ( $r % 2 ) {
				$set = array_reverse( $set );
			}
			$k = ( (int) $s['shift'] * $r ) % count( $set );
			if ( $k ) {
				$set = array_merge( array_slice( $set, $k ), array_slice( $set, 0, $k ) );
			}
			echo '<div class="ay-hz-row" data-n="' . count( $set ) . '">';
			for ( $c = 0; $c < $rep; $c++ ) {
				foreach ( $set as $id ) {
					$w    = ay_work( $id );
					$tag  = 'yes' === $s['link'] ? 'a' : 'div';
					$attr = 'yes' === $s['link'] ? ' href="' . esc_url( $w['url'] ) . '"' : '';
					if ( $c ) {
						$attr .= ' tabindex="-1" aria-hidden="true"';
					}
					echo '<' . $tag . ' class="ay-hz-tile"' . $attr . '>' . ay_work_img( $id, 'ay-card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					if ( 'yes' === $s['caption'] ) {
						echo '<span class="ay-hz-cap"><strong>' . esc_html( $w['title'] ) . '</strong>';
						if ( 'yes' === $s['caption_col'] && $w['collections'] ) {
							echo '<span>' . esc_html( $w['collections'][0]->name ) . '</span>';
						}
						echo '</span>';
					}
					echo '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			echo '</div>';
		}
		echo '</div>';
	}
}

/* =========================================================================
 * Stacked cards
 * ====================================================================== */
class Stack extends Base {

	public function get_name() {
		return 'ay-stack';
	}

	public function get_title() {
		return 'کارت‌های روی‌هم آرزو';
	}

	public function get_icon() {
		return 'eicon-form-vertical';
	}

	protected function register_controls() {
		$this->sec( 's_source', 'کارت‌ها' );
		$this->sel( 'source', 'منبع کارت‌ها', array( 'collections' => 'هر کالکشن یک کارت (خودکار)', 'manual' => 'کارت‌های دستی' ), 'collections' );
		$terms = array();
		foreach ( ay_terms_ordered( 'ay_collection' ) as $t ) {
			$terms[ $t->term_id ] = $t->name;
		}
		$this->add_control( 'cols', array( 'label' => 'فقط این کالکشن‌ها', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $terms, 'description' => 'خالی = همه، به ترتیب صفحهٔ «دسته‌بندی‌ها»', 'condition' => array( 'source' => 'collections' ) ) );
		$this->num( 'max', 'حداکثر تعداد کارت', 6, 1, 20, 1, array( 'condition' => array( 'source' => 'collections' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => 'عنوان', 'type' => CM::TEXT, 'default' => 'Collection', 'label_block' => true ) );
		$rep->add_control( 'meta', array( 'label' => 'متن کوچک کنار عنوان', 'type' => CM::TEXT, 'default' => '', 'label_block' => true ) );
		$rep->add_control( 'btn', array( 'label' => 'متن دکمه', 'type' => CM::TEXT, 'default' => 'View collection' ) );
		$rep->add_control( 'link', array( 'label' => 'لینک', 'type' => CM::URL, 'dynamic' => array( 'active' => false ) ) );
		$rep->add_control( 'tall', array( 'label' => 'تصویر بلند', 'type' => CM::MEDIA ) );
		$rep->add_control( 'small1', array( 'label' => 'تصویر کوچک ۱', 'type' => CM::MEDIA ) );
		$rep->add_control( 'small2', array( 'label' => 'تصویر کوچک ۲', 'type' => CM::MEDIA ) );
		$this->add_control( 'cards', array( 'label' => 'کارت‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => array(), 'title_field' => '{{{ title }}}', 'condition' => array( 'source' => 'manual' ) ) );
		$this->sw( 'number', 'شماره‌گذاری کارت‌ها', 'yes', array( 'separator' => 'before' ) );
		$this->sw( 'meta_on', 'نمایش متن کوچک («Collection · n pieces»)', 'yes' );
		$this->txt( 'meta_word', 'واژهٔ «Collection»', 'Collection', array( 'condition' => array( 'meta_on' => 'yes', 'source' => 'collections' ) ) );
		$this->txt( 'btn_text', 'متن دکمه', 'View collection', array( 'description' => 'خالی = بدون دکمه', 'condition' => array( 'source' => 'collections' ) ) );
		$this->sel( 'tag', 'تگ عنوان', array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ), 'h3' );
		$this->done();

		$this->sec( 's_stack', 'روی‌هم افتادن' );
		$this->sw( 'stack', 'کارت‌ها هنگام اسکرول روی هم بچسبند', 'yes', array( 'prefix_class' => 'ay-stack-on-', 'description' => 'خاموش: کارت‌ها مثل یک فهرست عادی زیر هم می‌آیند.' ) );
		$on = array( 'stack' => 'yes' );
		$this->cssvar( 'top', 'فاصلهٔ چسبیدن از بالای صفحه', '--ay-st-top', 0, 400, 1, 128, 'px', array( 'condition' => $on, 'mobile_default' => array( 'unit' => 'px', 'size' => 96 ) ) );
		$this->cssvar( 'layer', 'فاصلهٔ لبهٔ لایه‌ها از هم', '--ay-st-gap', 0, 120, 1, 28, 'px', array( 'condition' => $on ) );
		$this->add_control( 'step', array( 'label' => 'میزان کوچک شدن هر لایه', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 0.12, 'step' => 0.005 ) ), 'default' => array( 'size' => 0.03 ), 'condition' => $on ) );
		$this->add_control( 'min', array( 'label' => 'کمترین مقیاس', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.5, 'max' => 1, 'step' => 0.01 ) ), 'default' => array( 'size' => 0.8 ), 'condition' => $on ) );
		$this->add_control( 'range', array( 'label' => 'طول اسکرول برای هر لایه (درصد ارتفاع صفحه)', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 20, 'max' => 200, 'step' => 5 ) ), 'default' => array( 'size' => 85 ), 'condition' => $on ) );
		$this->cssvar( 'space', 'فاصلهٔ بین کارت‌ها', '--ay-st-space', 0, 60, 1, 12, array( 'vh', 'px' ) );
		$this->done();

		$this->sec( 's_layout', 'چیدمان داخل کارت' );
		$this->sel( 'layout', 'چیدمان تصاویر', array( 'split' => 'دو تصویر کوچک + یک تصویر بلند', 'split-rev' => 'تصویر بلند + دو تصویر کوچک', 'trio' => 'سه تصویر هم‌اندازه', 'single' => 'فقط تصویر بلند' ), 'split', array( 'prefix_class' => 'ay-stl-' ) );
		$this->cssvar( 'ratio', 'سهم ستون تصاویر کوچک', '--ay-st-ratio', 20, 70, 1, 40, '%', array( 'condition' => array( 'layout' => array( 'split', 'split-rev' ) ) ) );
		$this->cssvar( 'media_h', 'ارتفاع بخش تصاویر', '--ay-st-h', 160, 900, 5, 460, array( 'px', 'vh' ) );
		$this->cssvar( 'media_gap', 'فاصلهٔ تصاویر', '--ay-st-mgap', 0, 60, 1, 12 );
		$this->done();

		$this->sec( 'st_card', 'کارت', true );
		$this->color( 'bg1', 'رنگ کارت ۱', '--ay-st-bg1' );
		$this->color( 'bg2', 'رنگ کارت ۲', '--ay-st-bg2' );
		$this->color( 'bg3', 'رنگ کارت ۳', '--ay-st-bg3' );
		$this->box_style( 'card', 'قاب کارت', '.ay-st-card' );
		$this->box_style( 'pic', 'قاب تصاویر', '.ay-st-pic', false );
		$this->done();
		$this->sec( 'st_text', 'متن‌ها', true );
		$this->text_style( 'num', 'شماره', '.ay-st-num' );
		$this->text_style( 'title', 'عنوان', '.ay-st-title' );
		$this->text_style( 'meta', 'متن کوچک', '.ay-st-meta' );
		$this->text_style( 'btn', 'دکمه', '.ay-st-btn', true );
		$this->done();
	}

	private function pic( $html, $url, $class = '' ) {
		return '<a class="ay-st-pic ' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . $html . '</a>';
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$cards = array();
		if ( 'manual' === $s['source'] ) {
			foreach ( (array) $s['cards'] as $c ) {
				$img = function ( $m ) use ( $c ) {
					return ! empty( $m['url'] ) ? '<img src="' . esc_url( $m['url'] ) . '" alt="' . esc_attr( $c['title'] ) . '" loading="lazy">' : '';
				};
				$url     = ! empty( $c['link']['url'] ) ? $c['link']['url'] : '#';
				$cards[] = array( 'title' => $c['title'], 'meta' => $c['meta'], 'btn' => $c['btn'], 'url' => $url, 'pics' => array( array( $img( $c['small1'] ), $url ), array( $img( $c['small2'] ), $url ), array( $img( $c['tall'] ), $url ) ) );
			}
		} else {
			$want = array_filter( array_map( 'absint', (array) $s['cols'] ) );
			foreach ( ay_terms_ordered( 'ay_collection', true ) as $t ) {
				if ( $want && ! in_array( (int) $t->term_id, $want, true ) ) {
					continue;
				}
				$ids = ay_query_works( array( 'collection' => array( $t->term_id ) ) );
				if ( ! $ids ) {
					continue;
				}
				$tall  = $ids[0];
				$small = array( isset( $ids[1] ) ? $ids[1] : $ids[0], isset( $ids[2] ) ? $ids[2] : ( isset( $ids[1] ) ? $ids[1] : $ids[0] ) );
				$pics  = array();
				foreach ( array( $small[0], $small[1], $tall ) as $pid ) {
					$pics[] = array( ay_work_img( $pid, 'large' ), get_permalink( $pid ) );
				}
				$cards[] = array(
					'title' => $t->name,
					'meta'  => trim( $s['meta_word'] . ' · ' . count( $ids ) . ( 1 === count( $ids ) ? ' piece' : ' pieces' ), ' ·' ),
					'btn'   => $s['btn_text'],
					'url'   => get_term_link( $t ),
					'pics'  => $pics,
				);
				if ( count( $cards ) >= (int) $s['max'] ) {
					break;
				}
			}
		}
		if ( ! $cards ) {
			$this->empty_note( 'کارتی برای نمایش نیست. برای حالت خودکار، دست‌کم یک کالکشن با اثر لازم است.' );
			return;
		}
		$tag = in_array( $s['tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $s['tag'] : 'h3';
		$cfg = array( 'step' => $this->size( $s['step'], 0.03 ), 'min' => $this->size( $s['min'], 0.8 ), 'range' => $this->size( $s['range'], 85 ) / 100 );
		echo '<div class="ay-st" data-ay-stack="' . esc_attr( wp_json_encode( $cfg ) ) . '">';
		foreach ( $cards as $i => $c ) {
			$url = is_wp_error( $c['url'] ) ? '#' : $c['url'];
			echo '<article class="ay-st-card" style="--i:' . (int) $i . '"><header class="ay-st-head">';
			if ( 'yes' === $s['number'] ) {
				echo '<span class="ay-st-num">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</span>';
			}
			echo '<' . $tag . ' class="ay-st-title">' . esc_html( $c['title'] ) . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( 'yes' === $s['meta_on'] && '' !== trim( (string) $c['meta'] ) ) {
				echo '<span class="ay-st-meta">' . esc_html( $c['meta'] ) . '</span>';
			}
			if ( '' !== trim( (string) $c['btn'] ) ) {
				echo '<a class="ay-st-btn" href="' . esc_url( $url ) . '">' . esc_html( $c['btn'] ) . '<span class="ay-arr" aria-hidden="true"></span></a>';
			}
			echo '</header><div class="ay-st-media"><div class="ay-st-small">' . $this->pic( $c['pics'][0][0], $c['pics'][0][1] ) . $this->pic( $c['pics'][1][0], $c['pics'][1][1] ) . '</div>' . $this->pic( $c['pics'][2][0], $c['pics'][2][1], 'ay-st-tall' ) . '</div></article>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}
}

/* =========================================================================
 * Collections archive with filters
 * ====================================================================== */
class Archive extends Base {

	public function get_name() {
		return 'ay-archive';
	}

	public function get_title() {
		return 'آرشیو کالکشن‌ها آرزو';
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public static function fx_options() {
		return array( 'line' => 'خط از چپ', 'ink' => 'خط موج‌دار دستی', 'space' => 'باز شدن حروف و نقطه', 'shimmer' => 'عبور نور از حروف', 'roll' => 'غلتیدن واژه', 'none' => 'بدون حرکت' );
	}

	protected function register_controls() {
		$this->sec( 's_query', 'آثار' );
		$this->query_controls( -1 );
		$this->done();

		$this->sec( 's_filters', 'فیلترها' );
		$this->sw( 'f_col', 'فیلتر کالکشن', 'yes' );
		$this->txt( 'f_col_label', 'برچسب فیلتر کالکشن', 'Collection', array( 'condition' => array( 'f_col' => 'yes' ) ) );
		$this->sw( 'f_type', 'فیلتر نوع', 'yes' );
		$this->txt( 'f_type_label', 'برچسب فیلتر نوع', 'Type', array( 'condition' => array( 'f_type' => 'yes' ) ) );
		$this->txt( 'f_all', 'واژهٔ «همه»', 'All' );
		$this->sw( 'f_empty', 'نمایش دسته‌های بدون اثر', '' );
		$this->sel( 'fx', 'موشن متن دسته‌ها', self::fx_options(), 'line', array( 'description' => 'دسته‌ها فقط متن‌اند و باکسی پشتشان نیست.' ) );
		$this->sw( 'count', 'نمایش تعداد آثار', 'yes' );
		$this->txt( 'count_one', 'واژهٔ مفرد', 'piece', array( 'condition' => array( 'count' => 'yes' ) ) );
		$this->txt( 'count_many', 'واژهٔ جمع', 'pieces', array( 'condition' => array( 'count' => 'yes' ) ) );
		$this->txt( 'none', 'متن وقتی اثری پیدا نشد', 'No pieces match these filters.' );
		$this->sw( 'url_sync', 'انتخاب اولیه از نشانی صفحهٔ کالکشن یا نوع', 'yes' );
		$this->done();

		$this->sec( 's_layout', 'چیدمان' );
		$this->sel( 'layout', 'نوع چیدمان', array( 'grid' => 'شبکهٔ منظم', 'masonry' => 'شبکهٔ ناهم‌ارتفاع', 'list' => 'فهرست ردیفی', 'editorial' => 'مجله‌ای (یکی بزرگ، بقیه کوچک)', 'slider' => 'نوار افقی لغزنده' ), 'grid', array( 'prefix_class' => 'ay-arl-' ) );
		$this->add_responsive_control( 'columns', array( 'label' => 'تعداد ستون', 'type' => CM::NUMBER, 'min' => 1, 'max' => 6, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 2, 'selectors' => array( '{{WRAPPER}}' => '--ay-ar-cols: {{VALUE}};' ), 'condition' => array( 'layout!' => array( 'list', 'slider' ) ) ) );
		$this->cssvar( 'gap', 'فاصلهٔ کارت‌ها', '--ay-ar-gap', 0, 80, 1, 30, 'px', array( 'mobile_default' => array( 'unit' => 'px', 'size' => 14 ) ) );
		$this->add_control( 'aspect', array( 'label' => 'نسبت قاب تصویر (عرض ÷ ارتفاع)', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.4, 'max' => 2, 'step' => 0.01 ) ), 'default' => array( 'size' => 0.8 ), 'selectors' => array( '{{WRAPPER}}' => '--ay-ar-aspect: {{SIZE}};' ) ) );
		$this->cssvar( 'img_pad', 'فاصلهٔ تصویر از لبهٔ قاب', '--ay-ar-pad', 0, 30, 1, 10, '%' );
		$this->cssvar( 'slide_w', 'عرض هر کارت در نوار', '--ay-ar-slide', 160, 700, 5, 340, 'px', array( 'condition' => array( 'layout' => 'slider' ) ) );
		$this->sw( 'name', 'نمایش نام اثر', 'yes', array( 'prefix_class' => 'ay-ar-name-' ) );
		$this->sw( 'meta', 'نمایش کالکشن و نوع', 'yes', array( 'prefix_class' => 'ay-ar-meta-' ) );
		$this->done();

		$this->sec( 'st_filters', 'فیلترها', true );
		$this->text_style( 'flabel', 'برچسب', '.ay-fl-label' );
		$this->text_style( 'fitem', 'دسته', '.ay-fi' );
		$this->color( 'fitem_on', 'رنگ دستهٔ انتخاب‌شده', array( '.ay-fi.is-on, {{WRAPPER}} .ay-fi:hover' => 'color' ) );
		$this->color( 'fx_color', 'رنگ موشن', '--ay-fx-color' );
		$this->cssvar( 'fgap', 'فاصلهٔ دسته‌ها', '--ay-fl-gap', 4, 80, 1, 26 );
		$this->color( 'fline', 'رنگ خط بالا و پایین', '--ay-fl-line' );
		$this->done();
		$this->sec( 'st_card', 'کارت‌ها', true );
		$this->color( 'pic1', 'رنگ قاب تصویر ۱', '--ay-ar-bg1' );
		$this->color( 'pic2', 'رنگ قاب تصویر ۲', '--ay-ar-bg2' );
		$this->box_style( 'pic', 'قاب تصویر', '.ay-card-pic', false );
		$this->cssvar( 'zoom', 'بزرگ‌نمایی هنگام هاور', '--ay-ar-zoom', 1, 1.4, 0.01, 1.07, '', array(), false );
		$this->text_style( 'cname', 'نام اثر', '.ay-card-name' );
		$this->text_style( 'cmeta', 'کالکشن و نوع', '.ay-card-meta' );
		$this->text_style( 'ccount', 'تعداد آثار', '.ay-ar-count' );
		$this->done();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$ids = $this->query_works( $s );
		$pre = 'yes' === $s['url_sync'] ? ay_archive_preselect() : array( 'collection' => 0, 'type' => 0 );
		$fx  = isset( self::fx_options()[ $s['fx'] ] ) ? $s['fx'] : 'line';
		$row = function ( $group, $label, $tax, $selected ) use ( $s ) {
			$terms = ay_terms_ordered( $tax, 'yes' !== $s['f_empty'] );
			if ( ! $terms ) {
				return '';
			}
			$btn = function ( $val, $text ) use ( $group, $selected ) {
				$on = (string) $val === (string) $selected;
				return '<button type="button" class="ay-fi' . ( $on ? ' is-on' : '' ) . '" data-g="' . esc_attr( $group ) . '" data-v="' . esc_attr( $val ) . '" aria-pressed="' . ( $on ? 'true' : 'false' ) . '"><span class="ay-fi-t" data-t="' . esc_attr( $text ) . '">' . esc_html( $text ) . '</span></button>';
			};
			$out = '<div class="ay-fl-row" role="group" aria-label="' . esc_attr( $label ) . '"><span class="ay-fl-label">' . esc_html( $label ) . '</span><div class="ay-fl-items">' . $btn( 'all', $s['f_all'] );
			foreach ( $terms as $t ) {
				$out .= $btn( $t->term_id, $t->name );
			}
			return $out . '</div></div>';
		};
		$cfg = array( 'one' => $s['count_one'], 'many' => $s['count_many'] );
		echo '<div class="ay-ar" data-ay-archive="' . esc_attr( wp_json_encode( $cfg ) ) . '">';
		$filters = ( 'yes' === $s['f_col'] ? $row( 'col', $s['f_col_label'], 'ay_collection', $pre['collection'] ? $pre['collection'] : 'all' ) : '' ) . ( 'yes' === $s['f_type'] ? $row( 'type', $s['f_type_label'], 'ay_type', $pre['type'] ? $pre['type'] : 'all' ) : '' );
		if ( $filters ) {
			echo '<div class="ay-fl ay-fx-' . esc_attr( $fx ) . '">' . $filters . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( 'yes' === $s['count'] ) {
			echo '<p class="ay-ar-count" aria-live="polite"></p>';
		}
		echo '<div class="ay-ar-grid">';
		foreach ( $ids as $n => $id ) {
			echo $this->work_card( $id, $n ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div><p class="ay-ar-none" hidden>' . esc_html( $s['none'] ) . '</p></div>';
		if ( ! $ids ) {
			$this->empty_note( 'هنوز اثری برای نمایش نیست. از «آرزو ← آثار» اثر اضافه کنید.' );
		}
	}
}

/* =========================================================================
 * Related works (single work page)
 * ====================================================================== */
class Related extends Base {

	public function get_name() {
		return 'ay-related';
	}

	public function get_title() {
		return 'آثار مرتبط آرزو';
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	protected function register_controls() {
		$this->sec( 's_main', 'آثار مرتبط' );
		$this->sel( 'by', 'ارتباط بر اساس', array( 'collection' => 'هم‌کالکشن', 'type' => 'هم‌نوع', 'any' => 'هر اثر دیگر' ), 'collection' );
		$this->num( 'count', 'تعداد', 3, 1, 12 );
		$this->sw( 'fallback', 'اگر اثر مرتبطی نبود، آثار دیگر نمایش داده شود', 'yes' );
		$this->add_responsive_control( 'columns', array( 'label' => 'تعداد ستون', 'type' => CM::NUMBER, 'min' => 1, 'max' => 6, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 2, 'selectors' => array( '{{WRAPPER}}' => '--ay-ar-cols: {{VALUE}};' ) ) );
		$this->cssvar( 'gap', 'فاصلهٔ کارت‌ها', '--ay-ar-gap', 0, 80, 1, 30, 'px', array( 'mobile_default' => array( 'unit' => 'px', 'size' => 14 ) ) );
		$this->add_control( 'aspect', array( 'label' => 'نسبت قاب تصویر', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.4, 'max' => 2, 'step' => 0.01 ) ), 'default' => array( 'size' => 0.8 ), 'selectors' => array( '{{WRAPPER}}' => '--ay-ar-aspect: {{SIZE}};' ) ) );
		$this->done();
		$this->sec( 'st_card', 'کارت‌ها', true );
		$this->color( 'pic1', 'رنگ قاب تصویر ۱', '--ay-ar-bg1' );
		$this->color( 'pic2', 'رنگ قاب تصویر ۲', '--ay-ar-bg2' );
		$this->box_style( 'pic', 'قاب تصویر', '.ay-card-pic', false );
		$this->text_style( 'cname', 'نام اثر', '.ay-card-name' );
		$this->text_style( 'cmeta', 'کالکشن و نوع', '.ay-card-meta' );
		$this->done();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$cur = ay_current_work_id();
		if ( ! $cur ) {
			$this->empty_note( 'برای پیش‌نمایش این ویجت دست‌کم یک اثر لازم است.' );
			return;
		}
		$args = array( 'count' => (int) $s['count'], 'exclude' => array( $cur ) );
		$w    = ay_work( $cur );
		if ( 'collection' === $s['by'] && $w['collections'] ) {
			$args['collection'] = wp_list_pluck( $w['collections'], 'term_id' );
		} elseif ( 'type' === $s['by'] && $w['types'] ) {
			$args['type'] = wp_list_pluck( $w['types'], 'term_id' );
		}
		$ids = ay_query_works( $args );
		if ( ! $ids && 'yes' === $s['fallback'] ) {
			$ids = ay_query_works( array( 'count' => (int) $s['count'], 'exclude' => array( $cur ) ) );
		}
		if ( ! $ids ) {
			$this->empty_note( 'اثر مرتبطی پیدا نشد.' );
			return;
		}
		echo '<div class="ay-ar ay-rel"><div class="ay-ar-grid">';
		foreach ( $ids as $n => $id ) {
			echo $this->work_card( $id, $n ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div></div>';
	}
}
