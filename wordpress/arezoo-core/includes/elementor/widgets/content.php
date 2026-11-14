<?php
/**
 * Content widgets: FAQ (five layouts) and scroll timeline.
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
 * FAQ
 * ====================================================================== */
class Faq extends Base {

	public function get_name() {
		return 'ay-faq';
	}

	public function get_title() {
		return 'سوالات متداول آرزو';
	}

	public function get_icon() {
		return 'eicon-help-o';
	}

	public static function layouts() {
		return array( 'accordion' => '۱ · آکاردئون ساده', 'sidebar' => '۲ · فهرست موضوع کنار پاسخ‌ها', 'columns' => '۳ · دو ستونه', 'cards' => '۴ · کارت‌های شیشه‌ای', 'tabs' => '۵ · تب‌های افقی' );
	}

	protected function register_controls() {
		$this->sec( 's_items', 'پرسش‌ها' );
		$rep = new Repeater();
		$rep->add_control( 'group', array( 'label' => 'عنوان گروه', 'type' => CM::TEXT, 'default' => '', 'label_block' => true, 'description' => 'پرسش‌هایی که عنوان گروه یکسان دارند کنار هم می‌آیند.' ) );
		$rep->add_control( 'q', array( 'label' => 'پرسش', 'type' => CM::TEXT, 'default' => '', 'label_block' => true ) );
		$rep->add_control( 'a', array( 'label' => 'پاسخ', 'type' => CM::WYSIWYG, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => 'پرسش‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => ay_faq_default(), 'title_field' => '{{{ q }}}' ) );
		$this->done();

		$this->sec( 's_layout', 'چیدمان و رفتار' );
		$this->sel( 'layout', 'چیدمان', self::layouts(), 'sidebar' );
		$this->sw( 'search', 'کادر جستجو', 'yes' );
		$this->txt( 'search_ph', 'متن داخل کادر جستجو', 'Search questions', array( 'condition' => array( 'search' => 'yes' ) ) );
		$this->txt( 'none', 'متن وقتی چیزی پیدا نشد', 'No questions match your search.', array( 'condition' => array( 'search' => 'yes' ) ) );
		$this->sw( 'groups', 'نمایش عنوان گروه‌ها', 'yes' );
		$this->sel( 'group_tag', 'تگ عنوان گروه', array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ), 'h3' );
		$this->sw( 'counts', 'نمایش تعداد پرسش هر گروه', 'yes' );
		$this->sw( 'first_open', 'باز بودن اولین پاسخ', 'yes' );
		$this->sw( 'single', 'فقط یک پاسخ باز بماند', 'yes' );
		$this->sw( 'schema', 'دادهٔ ساختاریافتهٔ FAQ برای گوگل', '', array( 'description' => 'فقط برای یک ویجت سوالات در هر صفحه روشن کنید. اگر افزونهٔ سئو همین داده را اضافه می‌کند خاموش بماند.' ) );
		$this->cssvar( 'cols_gap', 'فاصلهٔ ستون‌ها و گروه‌ها', '--ay-fq-gap', 0, 120, 1, 48 );
		$this->cssvar( 'side_w', 'عرض فهرست موضوع‌ها', '--ay-fq-side', 160, 480, 5, 290, 'px', array( 'condition' => array( 'layout' => 'sidebar' ) ) );
		$this->done();

		$this->sec( 'st_text', 'متن‌ها', true );
		$this->text_style( 'gt', 'عنوان گروه', '.ay-fq-gt' );
		$this->text_style( 'topic', 'موضوع‌ها (فهرست و تب)', '.ay-fq-topic' );
		$this->color( 'topic_on', 'رنگ موضوع انتخاب‌شده', array( '.ay-fq-topic.is-on, {{WRAPPER}} .ay-fq-topic:hover' => 'color' ) );
		$this->text_style( 'q', 'پرسش', '.ay-fq-q' );
		$this->text_style( 'a', 'پاسخ', '.ay-fq-a' );
		$this->done();
		$this->sec( 'st_items', 'ردیف‌ها و جستجو', true );
		$this->color( 'line', 'رنگ خط بین پرسش‌ها', '--ay-fq-line' );
		$this->color( 'icon', 'رنگ علامت بعلاوه', array( '.ay-fq-ic' => 'color' ) );
		$this->cssvar( 'q_pad', 'فاصلهٔ عمودی هر پرسش', '--ay-fq-pad', 4, 50, 1, 17 );
		$this->color( 'search_c', 'رنگ متن جستجو', array( '.ay-fq-search input' => 'color' ) );
		$this->color( 'search_l', 'رنگ خط زیر جستجو', array( '.ay-fq-search input' => 'border-bottom-color' ) );
		$this->cssvar( 'search_w', 'بیشترین عرض کادر جستجو', '--ay-fq-sw', 160, 900, 5, 440 );
		$this->done();
		$this->sec( 'st_cards', 'کارت‌های شیشه‌ای', true, array( 'layout' => 'cards' ) );
		$this->color( 'card_color', 'رنگ شیشه', '--ay-g-color' );
		$this->cssvar( 'card_op', 'پوشش رنگ', '--ay-g-op', 0, 1, 0.02, 0.62, '', array(), false );
		$this->cssvar( 'card_blur', 'تاری پشت شیشه', '--ay-g-blur', 0, 40, 1, 14, 'px', array(), false );
		$this->color( 'card_glow', 'رنگ هالهٔ گوشه', '--ay-fq-glow' );
		$this->cssvar( 'card_r', 'گردی گوشه‌ها', '--ay-fq-radius', 0, 60, 1, 26 );
		$this->done();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array_values( array_filter( (array) $s['items'], function ( $i ) {
			return '' !== trim( (string) ( isset( $i['q'] ) ? $i['q'] : '' ) );
		} ) );
		if ( ! $items ) {
			$this->empty_note( 'هنوز پرسشی وارد نشده است.' );
			return;
		}
		$groups = array();
		foreach ( $items as $it ) {
			$g = trim( (string) $it['group'] );
			if ( ! isset( $groups[ $g ] ) ) {
				$groups[ $g ] = array();
			}
			$groups[ $g ][] = $it;
		}
		$layout = isset( self::layouts()[ $s['layout'] ] ) ? $s['layout'] : 'sidebar';
		$tag    = in_array( $s['group_tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $s['group_tag'] : 'h3';
		$uid    = 'ayfq' . $this->get_id();
		$name   = 'yes' === $s['single'] ? $uid : '';
		echo '<div class="ay-fq ay-fql-' . esc_attr( $layout ) . '" data-ay-faq="' . ( $name ? '1' : '0' ) . '">';
		if ( 'yes' === $s['search'] ) {
			echo '<div class="ay-fq-search">' . ay_icon( 'search', 18 ) . '<label class="ay-sr" for="' . esc_attr( $uid ) . '-q">' . esc_html( $s['search_ph'] ) . '</label><input id="' . esc_attr( $uid ) . '-q" type="search" placeholder="' . esc_attr( $s['search_ph'] ) . '" autocomplete="off"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( count( $groups ) > 1 ) {
			echo '<div class="ay-fq-nav" role="group" aria-label="Topics">';
			$g = 0;
			foreach ( $groups as $title => $list ) {
				echo '<button type="button" class="ay-fq-topic' . ( 0 === $g ? ' is-on' : '' ) . '" aria-pressed="' . ( 0 === $g ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $uid . '-g' . $g ) . '" data-g="' . (int) $g . '"><span>' . esc_html( '' !== $title ? $title : 'Questions' ) . '</span>' . ( 'yes' === $s['counts'] ? '<em>' . count( $list ) . '</em>' : '' ) . '</button>';
				$g++;
			}
			echo '</div>';
		}
		echo '<div class="ay-fq-groups">';
		$n  = 0;
		$gi = 0;
		foreach ( $groups as $title => $list ) {
			echo '<div class="ay-fq-group' . ( 0 === $gi ? ' is-on' : '' ) . '" id="' . esc_attr( $uid . '-g' . $gi ) . '">';
			$gi++;
			if ( 'yes' === $s['groups'] && '' !== $title ) {
				echo '<' . $tag . ' class="ay-fq-gt">' . esc_html( $title ) . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			foreach ( $list as $it ) {
				$open = ( 0 === $n && 'yes' === $s['first_open'] ) ? ' open' : '';
				echo '<details class="ay-fq-item"' . $open . ( $name ? ' name="' . esc_attr( $name ) . '"' : '' ) . '><summary><span class="ay-fq-q">' . esc_html( $it['q'] ) . '</span><span class="ay-fq-ic" aria-hidden="true"></span></summary><div class="ay-fq-a">' . wp_kses_post( wpautop( (string) $it['a'] ) ) . '</div></details>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$n++;
			}
			echo '</div>';
		}
		echo '</div>';
		if ( 'yes' === $s['search'] ) {
			echo '<p class="ay-fq-none" hidden>' . esc_html( $s['none'] ) . '</p>';
		}
		echo '</div>';

		if ( 'yes' === $s['schema'] && ! $this->in_editor() ) {
			$main = array();
			foreach ( $items as $it ) {
				$main[] = array( '@type' => 'Question', 'name' => wp_strip_all_tags( $it['q'] ), 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( (string) $it['a'] ) ) );
			}
			echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $main ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}

/* =========================================================================
 * Scroll timeline
 * ====================================================================== */
class Timeline extends Base {

	public function get_name() {
		return 'ay-timeline';
	}

	public function get_title() {
		return 'خط زمانی آرزو';
	}

	public function get_icon() {
		return 'eicon-time-line';
	}

	public static function default_steps() {
		return array(
			array( 'title' => 'Initial discussion', 'text' => 'The process begins with an initial discussion about your ideas, preferences, and project requirements.' ),
			array( 'title' => 'Inspiration and references', 'text' => 'Reference images, sketches, gemstones, architectural forms, cultural influences, or personal stories can all serve as inspiration.' ),
			array( 'title' => 'Design concept', 'text' => 'From there, I develop the design concept and refine the details through a collaborative process.' ),
			array( 'title' => 'Digital renderings', 'text' => 'Digital renderings communicate the design’s form, proportions, materials, and gemstones through detailed visual presentations.' ),
			array( 'title' => 'Final design', 'text' => 'The final design aligns with the agreed creative direction.' ),
		);
	}

	protected function register_controls() {
		$this->sec( 's_items', 'مرحله‌ها' );
		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => 'عنوان', 'type' => CM::TEXT, 'default' => 'Step', 'label_block' => true ) );
		$rep->add_control( 'text', array( 'label' => 'متن', 'type' => CM::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => 'مرحله‌ها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => self::default_steps(), 'title_field' => '{{{ title }}}' ) );
		$this->sel( 'tag', 'تگ عنوان', array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ), 'h3' );
		$this->sw( 'numbers', 'شماره داخل نقطه‌ها', '' );
		$this->done();
		$this->sec( 's_behave', 'رفتار' );
		$this->sw( 'fill', 'پر شدن خط با اسکرول', 'yes' );
		$this->add_control( 'at', array( 'label' => 'جای روشن شدن در ارتفاع صفحه (درصد)', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 20, 'max' => 90 ) ), 'default' => array( 'size' => 62 ) ) );
		$this->cssvar( 'dim', 'کم‌رنگی مرحله‌های نرسیده', '--ay-tl-dim', 0.2, 1, 0.05, 0.62, '', array(), false );
		$this->cssvar( 'gap', 'فاصلهٔ مرحله‌ها', '--ay-tl-gap', 8, 160, 2, 54 );
		$this->cssvar( 'indent', 'فاصلهٔ متن از خط', '--ay-tl-indent', 24, 120, 2, 46 );
		$this->done();
		$this->sec( 'st_main', 'ظاهر', true );
		$this->color( 'line', 'رنگ خط', '--ay-tl-line' );
		$this->color( 'fillc', 'رنگ خط پرشده', '--ay-tl-fill' );
		$this->color( 'dot', 'رنگ داخل نقطه', '--ay-tl-dot' );
		$this->cssvar( 'size', 'اندازهٔ نقطه', '--ay-tl-size', 8, 48, 1, 21 );
		$this->text_style( 'title', 'عنوان', '.ay-tl-title' );
		$this->text_style( 'text', 'متن', '.ay-tl-text' );
		$this->cssvar( 'maxw', 'بیشترین عرض', '--ay-tl-max', 280, 1400, 10, 760 );
		$this->done();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array_values( array_filter( (array) $s['items'], function ( $i ) {
			return '' !== trim( (string) $i['title'] . (string) $i['text'] );
		} ) );
		if ( ! $items ) {
			$this->empty_note( 'هنوز مرحله‌ای وارد نشده است.' );
			return;
		}
		$tag = in_array( $s['tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $s['tag'] : 'h3';
		echo '<ol class="ay-tl' . ( 'yes' === $s['numbers'] ? ' has-num' : '' ) . '" data-ay-timeline="' . esc_attr( wp_json_encode( array( 'fill' => 'yes' === $s['fill'], 'at' => $this->size( $s['at'], 62 ) / 100 ) ) ) . '">';
		foreach ( $items as $i => $it ) {
			echo '<li class="ay-tl-item"><span class="ay-tl-dot" aria-hidden="true">' . ( 'yes' === $s['numbers'] ? (int) ( $i + 1 ) : '' ) . '</span><' . $tag . ' class="ay-tl-title">' . esc_html( $it['title'] ) . '</' . $tag . '><p class="ay-tl-text">' . esc_html( $it['text'] ) . '</p></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</ol>';
	}
}
