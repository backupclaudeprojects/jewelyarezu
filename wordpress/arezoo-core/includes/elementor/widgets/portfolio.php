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
		$this->halo_switch();
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
					$attr = ( 'yes' === $s['link'] ? ' href="' . esc_url( $w['url'] ) . '"' : '' ) . ' style="' . esc_attr( ay_work_vars( $id ) ) . '"';
					if ( $c ) {
						$attr .= ' tabindex="-1" aria-hidden="true"';
					}
					echo '<' . $tag . ' class="ay-hz-tile"' . $attr . '>' . ay_work_halo( $id ) . ay_work_img( $id, 'ay-card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
		$this->sel( 'card_mode', 'حالت کارت', array( 'gallery' => 'حالت ۱: سه تصویر (کالکشن یا اثر)', 'feature' => 'حالت ۲: یک تصویر + توضیحات' ), 'gallery', array( 'prefix_class' => 'ay-stm-', 'description' => 'حالت ۲ برای هر کارت یک تصویر و کنارش متن کوتاه یا بلند، مشخصات و دکمه نشان می‌دهد. تنظیماتش در بخش «حالت ۲» است.' ) );
		$this->sel( 'source', 'منبع کارت‌ها', array( 'collections' => 'هر کالکشن یک کارت (خودکار)', 'types' => 'هر نوع یک کارت (انگشتر، گردنبند…)', 'works' => 'آثاری که انتخاب می‌کنم (هر اثر یک کارت)', 'query' => 'آثار با فیلتر (کالکشن، نوع، ویژه، تعداد، ترتیب)', 'manual' => 'کارت‌های دستی' ), 'collections', array( 'description' => 'اگر کالکشن‌ها در تنظیمات خاموش باشند، «هر کالکشن یک کارت» خودکار «هر نوع یک کارت» می‌شود.' ) );
		$terms = function ( $tax ) {
			$out = array();
			foreach ( ay_terms_ordered( $tax ) as $t ) {
				$out[ $t->term_id ] = $t->name;
			}
			return $out;
		};
		$works = array();
		foreach ( get_posts( array( 'post_type' => 'ay_work', 'post_status' => 'publish', 'numberposts' => 300, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) ) ) as $wp ) {
			$works[ $wp->ID ] = $wp->post_title;
		}
		$this->add_control( 'cols', array( 'label' => 'فقط این کالکشن‌ها', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $terms( 'ay_collection' ), 'description' => 'خالی = همه، به ترتیب صفحهٔ «دسته‌بندی‌ها»', 'condition' => array( 'source' => 'collections' ) ) );
		$this->add_control( 'pick_types', array( 'label' => 'فقط این نوع‌ها', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $terms( 'ay_type' ), 'description' => 'خالی = همه', 'condition' => array( 'source' => 'types' ) ) );
		$this->add_control( 'pick_works', array( 'label' => 'آثار (به همین ترتیب)', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $works, 'condition' => array( 'source' => 'works' ) ) );
		$this->add_control( 'sq_col', array( 'label' => 'فقط این کالکشن‌ها', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $terms( 'ay_collection' ), 'condition' => array( 'source' => 'query' ) ) );
		$this->add_control( 'sq_type', array( 'label' => 'فقط این نوع‌ها', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $terms( 'ay_type' ), 'condition' => array( 'source' => 'query' ) ) );
		$this->sw( 'sq_featured', 'فقط آثار ویژه', '', array( 'condition' => array( 'source' => 'query' ) ) );
		$this->add_control( 'sq_exclude', array( 'label' => 'این آثار نمایش داده نشوند', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $works, 'condition' => array( 'source' => 'query' ) ) );
		$this->sel( 'sq_order', 'ترتیب', array( 'menu' => 'ترتیب دستی (عدد «ترتیب» هر اثر)', 'new' => 'تازه‌ترین اول', 'old' => 'قدیمی‌ترین اول', 'title' => 'الفبایی', 'rand' => 'تصادفی' ), 'menu', array( 'condition' => array( 'source' => 'query' ) ) );
		$this->num( 'max', 'حداکثر تعداد کارت', 6, 1, 30, 1, array( 'condition' => array( 'source' => array( 'collections', 'types', 'query' ) ) ) );
		$this->sel( 'term_pic', 'تصویر کارت‌های کالکشن / نوع در حالت ۲', array( 'first' => 'تصویر اولین اثر', 'featured' => 'تصویر اولین اثر ویژه', 'rand' => 'تصویر یک اثر تصادفی' ), 'first', array( 'condition' => array( 'card_mode' => 'feature', 'source' => array( 'collections', 'types' ) ) ) );
		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => 'عنوان', 'type' => CM::TEXT, 'default' => 'Collection', 'label_block' => true ) );
		$rep->add_control( 'meta', array( 'label' => 'متن کوچک کنار عنوان', 'type' => CM::TEXT, 'default' => '', 'label_block' => true ) );
		$rep->add_control( 'text', array( 'label' => 'توضیح (حالت ۲)', 'type' => CM::TEXTAREA, 'rows' => 4, 'default' => '' ) );
		$rep->add_control( 'btn', array( 'label' => 'متن دکمه', 'type' => CM::TEXT, 'default' => 'View collection' ) );
		$rep->add_control( 'link', array( 'label' => 'لینک', 'type' => CM::URL, 'dynamic' => array( 'active' => false ) ) );
		$rep->add_control( 'tall', array( 'label' => 'تصویر بلند (حالت ۲: تنها تصویر کارت)', 'type' => CM::MEDIA ) );
		$rep->add_control( 'small1', array( 'label' => 'تصویر کوچک ۱', 'type' => CM::MEDIA ) );
		$rep->add_control( 'small2', array( 'label' => 'تصویر کوچک ۲', 'type' => CM::MEDIA ) );
		$this->add_control( 'cards', array( 'label' => 'کارت‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => array(), 'title_field' => '{{{ title }}}', 'condition' => array( 'source' => 'manual' ) ) );
		$this->sw( 'number', 'شماره‌گذاری کارت‌ها', 'yes', array( 'separator' => 'before' ) );
		$this->sw( 'meta_on', 'نمایش متن کوچک («Collection · n pieces»)', 'yes' );
		$this->txt( 'meta_word', 'واژهٔ «Collection»', 'Collection', array( 'condition' => array( 'meta_on' => 'yes', 'source' => array( 'collections', 'types' ) ) ) );
		$this->txt( 'btn_text', 'متن دکمه', 'View collection', array( 'description' => 'خالی = بدون دکمه', 'condition' => array( 'source' => array( 'collections', 'types' ) ) ) );
		$this->txt( 'btn_work', 'متن دکمهٔ کارت‌های اثر', 'View piece', array( 'description' => 'خالی = بدون دکمه', 'condition' => array( 'source' => array( 'works', 'query' ) ) ) );
		$this->sel( 'tag', 'تگ عنوان', array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ), 'h3' );
		$this->done();

		$this->sec( 's_feature', 'حالت ۲: یک تصویر + توضیحات', false, array( 'card_mode' => 'feature' ) );
		$this->sel( 'f_side', 'جای تصویر', array( 'left' => 'چپ', 'right' => 'راست', 'alt' => 'یکی‌درمیان چپ و راست' ), 'left', array( 'prefix_class' => 'ay-stf-side-' ) );
		$this->cssvar( 'f_iw', 'سهم ستون تصویر', '--ay-stf-iw', 25, 75, 1, 46, '%' );
		$this->sel( 'f_fit', 'جا شدن تصویر', array( 'contain' => 'کامل (برای عکس بدون پس‌زمینه)', 'cover' => 'پر کردن قاب (برای عکس‌های عکاسی)' ), 'contain', array( 'prefix_class' => 'ay-stf-fit-' ) );
		$this->cssvar( 'f_ipad', 'فاصلهٔ تصویر از لبهٔ قاب', '--ay-stf-ipad', 0, 30, 1, 8, '%', array( 'condition' => array( 'f_fit' => 'contain' ) ) );
		$this->sel( 'f_m_shape', 'شکل قاب تصویر در موبایل', array( 'wide' => 'افقی (پهن و کوتاه)', 'tall' => 'عمودی', 'square' => 'مربع', 'auto' => 'هم‌شکل خودِ تصویر' ), 'wide', array( 'prefix_class' => 'ay-stf-m-', 'description' => 'برای عکس‌های عمودی «عمودی» یا «هم‌شکل خودِ تصویر» را انتخاب کنید.' ) );
		$this->cssvar( 'f_m_ratio', 'نسبت قاب عمودی در موبایل (عرض ÷ ارتفاع)', '--ay-stf-mr', 0.4, 1, 0.01, 0.75, '', array( 'condition' => array( 'f_m_shape' => 'tall' ) ), false );
		$this->add_control( 'f_m_w', array( 'label' => 'عرض قاب تصویر در موبایل', 'type' => CM::SLIDER, 'size_units' => array( '%' ), 'range' => array( '%' => array( 'min' => 30, 'max' => 100, 'step' => 1 ) ), 'default' => array( 'unit' => '%', 'size' => 62 ), 'selectors' => array( '{{WRAPPER}}' => '--ay-stf-mw: {{SIZE}}%; --ay-stf-mwn: calc({{SIZE}} / 100);' ), 'condition' => array( 'f_m_shape!' => 'wide' ), 'description' => 'اگر «روی‌هم افتادن» روشن است و متن کارت بلند باشد، قاب تصویر کمی کوتاه‌تر می‌شود تا کارت کامل در صفحهٔ گوشی جا شود.' ) );
		$this->cssvar( 'f_m_h', 'ارتفاع قاب افقی در موبایل', '--ay-stf-mh', 12, 60, 1, null, 'vh', array( 'condition' => array( 'f_m_shape' => 'wide' ) ), false );
		$this->sel( 'f_img', 'کدام تصویر اثر', array( 'cover' => 'تصویر اصلی', 'second' => 'دومین تصویر گالری (اگر باشد)' ), 'cover' );
		$this->halo_switch();
		$this->sel( 'f_eyebrow', 'برچسب بالای عنوان', array( 'none' => 'بدون برچسب', 'type' => 'نوع اثر', 'collection' => 'کالکشن اثر', 'both' => 'کالکشن · نوع', 'year' => 'سال', 'custom' => 'متن دلخواه' ), 'type' );
		$this->txt( 'f_eyebrow_txt', 'متن برچسب', 'Selected piece', array( 'condition' => array( 'f_eyebrow' => 'custom' ) ) );
		$this->sel( 'f_text', 'متن توضیح', array( 'short' => 'توضیح کوتاه (خلاصهٔ اثر یا چند واژهٔ اول)', 'long' => 'توضیح کامل', 'none' => 'بدون توضیح' ), 'short', array( 'description' => 'خلاصهٔ هر اثر را در صفحهٔ ویرایش همان اثر، کادر «توضیح کوتاه» بنویسید. برای کالکشن و نوع، توضیح خودِ کالکشن یا نوع استفاده می‌شود. متن هر کارت را می‌توانید پایین‌تر در «ویرایش دستی کارت‌ها» عوض کنید.' ) );
		$this->num( 'f_words', 'بیشترین تعداد واژه‌ها (توضیح کوتاه)', 32, 5, 200, 1, array( 'condition' => array( 'f_text' => 'short' ) ) );
		$this->add_control( 'f_specs', array( 'label' => 'مشخصات زیر توضیح', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => array( 'collection' => 'کالکشن', 'type' => 'نوع', 'materials' => 'متریال', 'gemstones' => 'سنگ‌ها', 'technique' => 'تکنیک', 'year' => 'سال', 'extra' => 'جزئیات دلخواه اثر', 'count' => 'تعداد آثار (کارت کالکشن / نوع)' ), 'default' => array( 'materials', 'gemstones' ) ) );
		$this->sel( 'f_specs_style', 'نمایش مشخصات', array( 'rows' => 'ردیف‌های عنوان و مقدار', 'chips' => 'برچسب‌های کوچک کنار هم', 'grid' => 'شبکهٔ دو ستونه' ), 'rows', array( 'prefix_class' => 'ay-stf-specs-' ) );
		$this->sel( 'f_btn_pos', 'جای دکمه', array( 'body' => 'زیر متن', 'head' => 'کنار عنوان', 'none' => 'بدون دکمه' ), 'body' );
		$this->sel( 'f_valign', 'جای عمودی متن', array( 'flex-start' => 'بالا', 'center' => 'وسط', 'flex-end' => 'پایین', 'space-between' => 'پخش در ارتفاع' ), 'center', array( 'selectors' => array( '{{WRAPPER}} .ay-stf-body' => 'justify-content: {{VALUE}};' ) ) );
		$this->align( 'f_align', '.ay-stf-body' );
		$this->cssvar( 'f_tmax', 'بیشترین عرض متن', '--ay-stf-tmax', 20, 90, 1, 46, 'ch' );
		$this->num( 'f_clamp', 'بیشترین سطر توضیح در موبایل (۰ = همه)', 4, 0, 30, 1 );
		$this->num( 'f_specs_m', 'بیشترین ردیف مشخصات در موبایل', 2, 0, 20, 1, array( 'description' => 'تا کارت روی‌هم در صفحهٔ گوشی کامل جا شود.' ) );
		$this->sw( 'f_link_pic', 'تصویر هم لینک باشد', 'yes' );
		$this->done();

		$this->sec( 's_over', 'ویرایش دستی کارت‌ها' );
		$this->note( 'over_n', 'برای هر اثر، کالکشن یا نوعی که کارتش نمایش داده می‌شود می‌توانید عنوان، برچسب، متن، تصویر، دکمه و لینک را دستی عوض کنید یا کارت را پنهان کنید. هر چه خالی بماند از خودِ اثر خوانده می‌شود.' );
		$targets = array( '' => '— انتخاب —' );
		foreach ( $works as $wid => $wt ) {
			$targets[ 'w:' . $wid ] = 'اثر: ' . $wt;
		}
		foreach ( $terms( 'ay_collection' ) as $tid => $tn ) {
			$targets[ 'c:' . $tid ] = 'کالکشن: ' . $tn;
		}
		foreach ( $terms( 'ay_type' ) as $tid => $tn ) {
			$targets[ 't:' . $tid ] = 'نوع: ' . $tn;
		}
		$rep = new Repeater();
		$rep->add_control( 'target', array( 'label' => 'کارتِ', 'type' => CM::SELECT, 'options' => $targets, 'default' => '' ) );
		$rep->add_control( 'hide', array( 'label' => 'این کارت نمایش داده نشود', 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => '' ) );
		$rep->add_control( 'title', array( 'label' => 'عنوان', 'type' => CM::TEXT, 'label_block' => true ) );
		$rep->add_control( 'eyebrow', array( 'label' => 'برچسب بالای عنوان / متن کوچک', 'type' => CM::TEXT, 'label_block' => true ) );
		$rep->add_control( 'text', array( 'label' => 'متن توضیح', 'type' => CM::TEXTAREA, 'rows' => 5 ) );
		$rep->add_control( 'specs', array( 'label' => 'مشخصات دلخواه (هر سطر: عنوان | مقدار)', 'type' => CM::TEXTAREA, 'rows' => 3, 'placeholder' => "Metal | White gold\nStones | Emerald" ) );
		$rep->add_control( 'image', array( 'label' => 'تصویر', 'type' => CM::MEDIA ) );
		$rep->add_control( 'btn', array( 'label' => 'متن دکمه', 'type' => CM::TEXT ) );
		$rep->add_control( 'link', array( 'label' => 'لینک', 'type' => CM::URL, 'dynamic' => array( 'active' => false ) ) );
		$this->add_control( 'over', array( 'label' => 'کارت‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => array(), 'title_field' => '{{{ target }}}' ) );
		$this->done();

		$this->sec( 's_stack', 'روی‌هم افتادن' );
		$this->sw( 'stack', 'کارت‌ها هنگام اسکرول روی هم بچسبند', 'yes', array( 'prefix_class' => 'ay-stack-on-', 'description' => 'خاموش: کارت‌ها مثل یک فهرست عادی زیر هم می‌آیند.' ) );
		$this->sel( 'stack_m', 'روی‌هم افتادن در موبایل', array( 'on' => 'روشن', 'off' => 'خاموش (کارت‌ها زیر هم، تصویر با اندازهٔ کامل)' ), 'on', array( 'prefix_class' => 'ay-stack-m-', 'condition' => array( 'stack' => 'yes' ), 'description' => 'وقتی روشن است، هر کارت باید کامل در صفحهٔ گوشی جا شود؛ اگر متن کارت بلند باشد قاب تصویر کوتاه‌تر می‌شود. برای تصویر عمودیِ بزرگ در موبایل، این را خاموش کنید.' ) );
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
		$this->sec( 'st_feature', 'حالت ۲', true, array( 'card_mode' => 'feature' ) );
		$this->box_style( 'f_pic', 'قاب تصویر', '.ay-stf-pic', false );
		$this->cssvar( 'f_gap', 'فاصلهٔ تصویر و متن', '--ay-stf-gap', 0, 120, 1, 40 );
		$this->add_responsive_control( 'f_pad', array( 'label' => 'فاصلهٔ داخلی ستون متن', 'type' => CM::DIMENSIONS, 'size_units' => array( 'px', 'em' ), 'selectors' => array( '{{WRAPPER}} .ay-stf-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->text_style( 'f_eye', 'برچسب بالای عنوان', '.ay-stf-eye' );
		$this->text_style( 'f_txt', 'متن توضیح', '.ay-stf-text' );
		$this->text_style( 'f_sk', 'عنوان مشخصات', '.ay-stf-specs dt' );
		$this->text_style( 'f_sv', 'مقدار مشخصات', '.ay-stf-specs dd' );
		$this->color( 'f_line', 'رنگ خط‌های مشخصات', '--ay-stf-line' );
		$this->color( 'f_chip', 'رنگ زمینهٔ برچسب‌های مشخصات', '--ay-stf-chip' );
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

	/** Manual changes for one card (key like w:12, c:3, t:5). */
	private function override( $s, $key ) {
		foreach ( (array) ( isset( $s['over'] ) ? $s['over'] : array() ) as $o ) {
			if ( ! empty( $o['target'] ) && $o['target'] === $key ) {
				return $o;
			}
		}
		return array();
	}

	private function words( $html, $n ) {
		return wp_trim_words( wp_strip_all_tags( strip_shortcodes( (string) $html ) ), max( 1, (int) $n ), '…' );
	}

	/** Spec rows of a work, a term card or manual lines. */
	private function spec_rows( $s, $id, $count = 0, $term = null ) {
		$rows   = array();
		$labels = array( 'collection' => 'Collection', 'type' => 'Type', 'materials' => 'Materials', 'gemstones' => 'Gemstones', 'technique' => 'Technique', 'year' => 'Year', 'count' => 'Pieces' );
		$w      = $id ? ay_work( $id ) : null;
		foreach ( (array) $s['f_specs'] as $f ) {
			if ( 'count' === $f ) {
				if ( $count ) {
					$rows[] = array( $labels['count'], (string) $count );
				}
			} elseif ( ! $w ) {
				continue;
			} elseif ( 'extra' === $f ) {
				$rows = array_merge( $rows, ay_work_extra( $id ) );
			} elseif ( 'collection' === $f || 'type' === $f ) {
				$v = implode( ', ', wp_list_pluck( 'collection' === $f ? $w['collections'] : $w['types'], 'name' ) );
				if ( '' !== $v ) {
					$rows[] = array( $labels[ $f ], $v );
				}
			} elseif ( '' !== trim( (string) $w[ $f ] ) ) {
				$rows[] = array( $labels[ $f ], $w[ $f ] );
			}
		}
		return $rows;
	}

	private function eyebrow( $s, $id ) {
		$w = ay_work( $id );
		if ( ! $w ) {
			return '';
		}
		$col = implode( ', ', wp_list_pluck( $w['collections'], 'name' ) );
		$typ = implode( ', ', wp_list_pluck( $w['types'], 'name' ) );
		switch ( $s['f_eyebrow'] ) {
			case 'type':
				return $typ;
			case 'collection':
				return $col;
			case 'both':
				return trim( $col . ( $col && $typ ? ' · ' : '' ) . $typ );
			case 'year':
				return $w['year'];
			case 'custom':
				return (string) $s['f_eyebrow_txt'];
		}
		return '';
	}

	/** Picture of a work for mode 2. */
	private function work_pic( $s, $id ) {
		if ( 'second' === $s['f_img'] ) {
			$ids = ay_work_gallery_ids( $id );
			if ( isset( $ids[1] ) ) {
				$alt = get_post_meta( $ids[1], '_wp_attachment_image_alt', true );
				return wp_get_attachment_image( $ids[1], 'large', false, array( 'alt' => $alt ? $alt : get_the_title( $id ), 'loading' => 'lazy' ) );
			}
		}
		return ay_work_img( $id, 'large' );
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$feature = 'feature' === $s['card_mode'];
		$src     = $s['source'];
		if ( 'collections' === $src && ! ay_use_collections() ) {
			$src = 'types';
		}
		$cards = array();
		$img   = function ( $m, $alt ) {
			return ! empty( $m['url'] ) ? '<img src="' . esc_url( $m['url'] ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy">' : '';
		};
		if ( 'manual' === $src ) {
			foreach ( (array) $s['cards'] as $c ) {
				$url     = ! empty( $c['link']['url'] ) ? $c['link']['url'] : '#';
				$cards[] = array( 'key' => '', 'id' => 0, 'title' => $c['title'], 'meta' => $c['meta'], 'eye' => $c['meta'], 'text' => isset( $c['text'] ) ? $c['text'] : '', 'btn' => $c['btn'], 'url' => $url, 'specs' => array(), 'vars' => '', 'halo' => '', 'pic' => $img( $c['tall'], $c['title'] ), 'pics' => array( array( $img( $c['small1'], $c['title'] ), $url ), array( $img( $c['small2'], $c['title'] ), $url ), array( $img( $c['tall'], $c['title'] ), $url ) ) );
			}
		} elseif ( 'works' === $src || 'query' === $src ) {
			if ( 'works' === $src ) {
				$ids = array_values( array_filter( array_map( 'absint', (array) $s['pick_works'] ) ) );
				$ids = $ids ? ay_query_works( array( 'include' => $ids ) ) : array();
			} else {
				$ids = ay_query_works( array( 'collection' => (array) $s['sq_col'], 'type' => (array) $s['sq_type'], 'featured' => 'yes' === $s['sq_featured'], 'exclude' => (array) $s['sq_exclude'], 'order' => $s['sq_order'], 'count' => max( 1, (int) $s['max'] ) ) );
			}
			foreach ( $ids as $id ) {
				$w = ay_work( $id );
				if ( ! $w ) {
					continue;
				}
				$gal  = ay_work_gallery_ids( $id );
				$pics = array();
				foreach ( array( isset( $gal[1] ) ? $gal[1] : 0, isset( $gal[2] ) ? $gal[2] : 0, isset( $gal[0] ) ? $gal[0] : 0 ) as $k => $att ) {
					$pics[] = array( $att ? wp_get_attachment_image( $att, 'large', false, array( 'alt' => $w['title'], 'loading' => 'lazy' ) ) : ( 2 === $k ? ay_work_img( $id, 'large' ) : '' ), $w['url'] );
				}
				if ( '' === $pics[0][0] ) {
					$pics[0][0] = $pics[2][0];
				}
				if ( '' === $pics[1][0] ) {
					$pics[1][0] = $pics[0][0];
				}
				$typ     = implode( ' · ', wp_list_pluck( $w['types'], 'name' ) );
				$text    = 'long' === $s['f_text'] ? $w['desc'] : ( 'short' === $s['f_text'] ? ( has_excerpt( $id ) ? get_the_excerpt( $id ) : $this->words( $w['desc'], $s['f_words'] ) ) : '' );
				$cards[] = array( 'key' => 'w:' . $id, 'id' => $id, 'title' => $w['title'], 'meta' => trim( implode( ' · ', wp_list_pluck( $w['collections'], 'name' ) ) . ( $w['collections'] && $typ ? ' · ' : '' ) . $typ ), 'eye' => $this->eyebrow( $s, $id ), 'text' => $text, 'btn' => $s['btn_work'], 'url' => $w['url'], 'specs' => $this->spec_rows( $s, $id ), 'vars' => ay_work_vars( $id ), 'halo' => ay_work_halo( $id ), 'pic' => $this->work_pic( $s, $id ), 'pics' => $pics );
			}
		} else {
			$tax  = 'types' === $src ? 'ay_type' : 'ay_collection';
			$want = array_filter( array_map( 'absint', (array) ( 'types' === $src ? $s['pick_types'] : $s['cols'] ) ) );
			foreach ( ay_terms_ordered( $tax, true ) as $t ) {
				if ( $want && ! in_array( (int) $t->term_id, $want, true ) ) {
					continue;
				}
				$ids = ay_query_works( array( 'types' === $src ? 'type' : 'collection' => array( $t->term_id ) ) );
				if ( ! $ids ) {
					continue;
				}
				$tall  = $ids[0];
				$small = array( isset( $ids[1] ) ? $ids[1] : $ids[0], isset( $ids[2] ) ? $ids[2] : ( isset( $ids[1] ) ? $ids[1] : $ids[0] ) );
				$pics  = array();
				foreach ( array( $small[0], $small[1], $tall ) as $pid ) {
					$pics[] = array( ay_work_img( $pid, 'large' ), get_permalink( $pid ) );
				}
				$lead = $ids[0];
				if ( 'rand' === $s['term_pic'] ) {
					$lead = $ids[ array_rand( $ids ) ];
				} elseif ( 'featured' === $s['term_pic'] ) {
					foreach ( $ids as $pid ) {
						if ( '1' === (string) get_post_meta( $pid, '_ay_featured', true ) ) {
							$lead = $pid;
							break;
						}
					}
				}
				$desc    = term_description( $t );
				$text    = 'long' === $s['f_text'] ? $desc : ( 'short' === $s['f_text'] ? $this->words( $desc, $s['f_words'] ) : '' );
				$meta    = trim( $s['meta_word'] . ' · ' . count( $ids ) . ( 1 === count( $ids ) ? ' piece' : ' pieces' ), ' ·' );
				$cards[] = array( 'key' => ( 'types' === $src ? 't:' : 'c:' ) . $t->term_id, 'id' => 0, 'title' => $t->name, 'meta' => $meta, 'eye' => $meta, 'text' => $text, 'btn' => $s['btn_text'], 'url' => get_term_link( $t ), 'specs' => $this->spec_rows( $s, 0, count( $ids ) ), 'vars' => ay_work_vars( $lead ), 'halo' => ay_work_halo( $lead ), 'pic' => ay_work_img( $lead, 'large' ), 'pics' => $pics );
				if ( count( $cards ) >= (int) $s['max'] ) {
					break;
				}
			}
		}
		/* manual changes per card */
		$out = array();
		foreach ( $cards as $c ) {
			$o = $c['key'] ? $this->override( $s, $c['key'] ) : array();
			if ( ! empty( $o['hide'] ) && 'yes' === $o['hide'] ) {
				continue;
			}
			foreach ( array( 'title' => 'title', 'eyebrow' => 'eye', 'btn' => 'btn' ) as $from => $to ) {
				if ( isset( $o[ $from ] ) && '' !== trim( (string) $o[ $from ] ) ) {
					$c[ $to ] = $o[ $from ];
					if ( 'eye' === $to ) {
						$c['meta'] = $o[ $from ];
					}
				}
			}
			if ( isset( $o['text'] ) && '' !== trim( (string) $o['text'] ) ) {
				$c['text'] = wpautop( esc_html( $o['text'] ) );
			}
			if ( ! empty( $o['link']['url'] ) ) {
				$c['url'] = $o['link']['url'];
				foreach ( $c['pics'] as $k => $p ) {
					$c['pics'][ $k ][1] = $o['link']['url'];
				}
			}
			if ( ! empty( $o['image']['url'] ) ) {
				$c['pic']        = $img( $o['image'], $c['title'] );
				$c['pics'][2][0] = $c['pic'];
			}
			if ( isset( $o['specs'] ) && '' !== trim( (string) $o['specs'] ) ) {
				foreach ( preg_split( '/\r?\n/', $o['specs'] ) as $line ) {
					$parts = array_map( 'trim', explode( '|', $line, 2 ) );
					if ( '' !== $parts[0] ) {
						$c['specs'][] = array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
					}
				}
			}
			$out[] = $c;
		}
		$cards = $out;
		if ( ! $cards ) {
			$this->empty_note( 'کارتی برای نمایش نیست. منبع کارت‌ها را بررسی کنید (مثلاً برای «آثاری که انتخاب می‌کنم» دست‌کم یک اثر انتخاب کنید).' );
			return;
		}
		$tag = in_array( $s['tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $s['tag'] : 'h3';
		$cfg = array( 'step' => $this->size( $s['step'], 0.03 ), 'min' => $this->size( $s['min'], 0.8 ), 'range' => $this->size( $s['range'], 85 ) / 100 );
		echo '<div class="ay-st" data-ay-stack="' . esc_attr( wp_json_encode( $cfg ) ) . '">';
		foreach ( $cards as $i => $c ) {
			$url  = is_wp_error( $c['url'] ) ? '#' : $c['url'];
			$btn  = '' !== trim( (string) $c['btn'] ) ? '<a class="ay-st-btn" href="' . esc_url( $url ) . '">' . esc_html( $c['btn'] ) . '<span class="ay-arr" aria-hidden="true"></span></a>' : '';
			$head = '<header class="ay-st-head">' . ( 'yes' === $s['number'] ? '<span class="ay-st-num">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</span>' : '' ) . '<' . $tag . ' class="ay-st-title">' . esc_html( $c['title'] ) . '</' . $tag . '>';
			if ( ! $feature ) {
				if ( 'yes' === $s['meta_on'] && '' !== trim( (string) $c['meta'] ) ) {
					$head .= '<span class="ay-st-meta">' . esc_html( $c['meta'] ) . '</span>';
				}
				echo '<article class="ay-st-card" style="--i:' . (int) $i . '">' . $head . $btn . '</header><div class="ay-st-media"><div class="ay-st-small">' . $this->pic( $c['pics'][0][0], $c['pics'][0][1] ) . $this->pic( $c['pics'][1][0], $c['pics'][1][1] ) . '</div>' . $this->pic( $c['pics'][2][0], $c['pics'][2][1], 'ay-st-tall' ) . '</div></article>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				continue;
			}
			/* mode 2: one picture and text */
			$clamp = (int) $s['f_clamp'];
			$pic   = $c['halo'] . $c['pic'];
			echo '<article class="ay-st-card ay-st-feat" style="--i:' . (int) $i . ';' . esc_attr( $c['vars'] ) . ( $clamp ? '--ay-stf-clamp:' . $clamp . ';' : '' ) . '">';
			echo 'yes' === $s['f_link_pic'] ? '<a class="ay-st-pic ay-stf-pic" href="' . esc_url( $url ) . '" tabindex="-1" aria-hidden="true">' . $pic . '</a>' : '<span class="ay-st-pic ay-stf-pic">' . $pic . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="ay-stf-body">';
			if ( 'none' !== $s['f_eyebrow'] && '' !== trim( (string) $c['eye'] ) && 'yes' === $s['meta_on'] ) {
				echo '<span class="ay-stf-eye">' . esc_html( $c['eye'] ) . '</span>';
			}
			echo $head . ( 'head' === $s['f_btn_pos'] ? $btn : '' ) . '</header>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( '' !== trim( wp_strip_all_tags( (string) $c['text'] ) ) ) {
				echo '<div class="ay-stf-text' . ( $clamp ? ' is-clamp' : '' ) . '">' . wp_kses_post( false === strpos( (string) $c['text'], '<p' ) ? wpautop( $c['text'] ) : $c['text'] ) . '</div>';
			}
			if ( $c['specs'] ) {
				echo '<dl class="ay-stf-specs">';
				$max_m = isset( $s['f_specs_m'] ) && '' !== (string) $s['f_specs_m'] ? (int) $s['f_specs_m'] : 2;
				foreach ( $c['specs'] as $k => $r ) {
					echo '<div' . ( $k >= $max_m ? ' class="ay-stf-mh"' : '' ) . '><dt>' . esc_html( $r[0] ) . '</dt><dd>' . esc_html( $r[1] ) . '</dd></div>';
				}
				echo '</dl>';
			}
			if ( 'body' === $s['f_btn_pos'] ) {
				echo $btn; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div></article>';
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
		$this->sw( 'f_col', 'فیلتر کالکشن', '' );
		$this->txt( 'f_col_label', 'برچسب فیلتر کالکشن', 'Collection', array( 'condition' => array( 'f_col' => 'yes' ) ) );
		$this->sw( 'f_type', 'فیلتر نوع', 'yes' );
		$this->txt( 'f_type_label', 'برچسب فیلتر نوع', 'Type', array( 'condition' => array( 'f_type' => 'yes' ) ) );
		$this->txt( 'f_all', 'واژهٔ «همه»', 'All' );
		$this->sw( 'f_empty', 'نمایش دسته‌های بدون اثر', '' );
		$this->sel( 'f_style', 'ظاهر فیلترها', array( 'text' => 'فقط متن (بدون باکس)', 'capsule' => 'کپسول شیشه‌ای با نشانگر لغزان', 'box' => 'باکس شیشه‌ای با نور و خط لغزان' ), 'text', array( 'description' => 'در دو حالت شیشه‌ای، همهٔ دسته‌ها داخل یک باکس مثل نوار پایین موبایل‌اند و با کلیک، نشانگر با موشن به دستهٔ بعدی می‌رود. دسته‌ها از «آرزو ← دسته‌بندی‌ها» کم و زیاد می‌شوند.' ) );
		$this->sw( 'f_labels', 'نمایش برچسب کنار باکس', '', array( 'condition' => array( 'f_style!' => 'text' ) ) );
		$this->add_responsive_control( 'f_align', array( 'label' => 'جای باکس', 'type' => CM::CHOOSE, 'options' => array( 'flex-start' => array( 'title' => 'چپ', 'icon' => 'eicon-text-align-left' ), 'center' => array( 'title' => 'وسط', 'icon' => 'eicon-text-align-center' ), 'flex-end' => array( 'title' => 'راست', 'icon' => 'eicon-text-align-right' ) ), 'default' => 'center', 'selectors' => array( '{{WRAPPER}}' => '--ay-fl-align: {{VALUE}};' ), 'condition' => array( 'f_style!' => 'text' ) ) );
		$this->sel( 'fx', 'موشن متن دسته‌ها', self::fx_options(), 'line', array( 'description' => 'دسته‌ها فقط متن‌اند و باکسی پشتشان نیست.', 'condition' => array( 'f_style' => 'text' ) ) );
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
		$this->card_fx_controls( 'yes' );

		$this->sec( 'st_fbox', 'باکس شیشه‌ای فیلترها', true, array( 'f_style!' => 'text' ) );
		$this->color( 'fb_color', 'رنگ شیشه', '--ay-g-color' );
		$this->cssvar( 'fb_op', 'پوشش رنگ شیشه', '--ay-g-op', 0, 1, 0.02, 0.7, '', array(), false );
		$this->cssvar( 'fb_blur', 'تاری پشت شیشه', '--ay-g-blur', 0, 40, 1, 16, 'px', array(), false );
		$this->color( 'fb_line', 'رنگ خط دور', '--ay-g-line' );
		$this->cssvar( 'fb_radius', 'گردی گوشه‌ها (حالت باکس)', '--ay-fc-radius', 0, 40, 1, 22 );
		$this->cssvar( 'fb_pad', 'فاصلهٔ داخلی', '--ay-fc-pad', 0, 20, 1, 6 );
		$this->color( 'fb_ind', 'رنگ نشانگر لغزان', '--ay-fc-ind' );
		$this->color( 'fb_on', 'رنگ متن دستهٔ انتخاب‌شده', '--ay-fc-on' );
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
			$glass = in_array( $s['f_style'], array( 'capsule', 'box' ), true );
			$out   = '<div class="ay-fl-row" role="group" aria-label="' . esc_attr( $label ) . '">' . ( ! $glass || 'yes' === $s['f_labels'] ? '<span class="ay-fl-label">' . esc_html( $label ) . '</span>' : '' ) . '<div class="ay-fl-items">' . ( $glass ? '<i class="ay-fl-ind" aria-hidden="true"></i>' : '' ) . $btn( 'all', $s['f_all'] );
			foreach ( $terms as $t ) {
				$out .= $btn( $t->term_id, $t->name );
			}
			return $out . '</div></div>';
		};
		$cfg = array( 'one' => $s['count_one'], 'many' => $s['count_many'] );
		echo '<div class="ay-ar" data-ay-archive="' . esc_attr( wp_json_encode( $cfg ) ) . '">';
		$filters = ( 'yes' === $s['f_col'] && ay_use_collections() ? $row( 'col', $s['f_col_label'], 'ay_collection', $pre['collection'] ? $pre['collection'] : 'all' ) : '' ) . ( 'yes' === $s['f_type'] ? $row( 'type', $s['f_type_label'], 'ay_type', $pre['type'] ? $pre['type'] : 'all' ) : '' );
		if ( $filters ) {
			$fs = in_array( $s['f_style'], array( 'capsule', 'box' ), true ) ? $s['f_style'] : 'text';
			echo '<div class="ay-fl ay-fls-' . esc_attr( $fs ) . ( 'text' === $fs ? ' ay-fx-' . esc_attr( $fx ) : '' ) . '">' . $filters . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
		$this->card_fx_controls( '' );
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
		$by = 'collection' === $s['by'] && ! ay_use_collections() ? 'type' : $s['by'];
		if ( 'collection' === $by && $w['collections'] ) {
			$args['collection'] = wp_list_pluck( $w['collections'], 'term_id' );
		} elseif ( 'type' === $by && $w['types'] ) {
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
