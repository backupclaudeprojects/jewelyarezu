<?php
/**
 * Certificates and honours: cards that open in a viewer with a download button.
 *
 * @package ArezooCore
 */

namespace Arezoo\Widgets;

use Elementor\Controls_Manager as CM;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Credentials extends Base {

	public function get_name() {
		return 'ay-credentials';
	}

	public function get_title() {
		return 'مدارک و افتخارات آرزو';
	}

	public function get_icon() {
		return 'eicon-document-file';
	}

	protected function register_controls() {
		$this->sec( 's_items', 'مدارک و افتخارات' );
		$this->note( 'n', 'برای هر مورد یک فایل (عکس یا PDF) انتخاب کنید. بازدیدکننده با کلیک آن را بزرگ می‌بیند و می‌تواند دانلود کند. موردی که فایل ندارد فقط در ویرایشگر دیده می‌شود، و اگر هیچ موردی فایل نداشته باشد کل این بخش در سایت پنهان می‌ماند.' );
		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => 'عنوان', 'type' => CM::TEXT, 'default' => 'Certificate title', 'label_block' => true ) );
		$rep->add_control( 'issuer', array( 'label' => 'صادرکننده / محل', 'type' => CM::TEXT, 'default' => '', 'label_block' => true ) );
		$rep->add_control( 'year', array( 'label' => 'سال', 'type' => CM::TEXT, 'default' => '' ) );
		$rep->add_control( 'file', array( 'label' => 'فایل مدرک (عکس یا PDF)', 'type' => CM::MEDIA, 'media_types' => array( 'image', 'application/pdf' ), 'dynamic' => array( 'active' => false ) ) );
		$rep->add_control( 'thumb', array( 'label' => 'تصویر روی کارت (اختیاری)', 'type' => CM::MEDIA, 'description' => 'برای PDF پیشنهاد می‌شود. خالی = خودِ عکس مدرک یا یک نشان سند.' ) );
		$this->add_control( 'items', array( 'label' => 'موردها', 'type' => CM::REPEATER, 'fields' => $rep->get_controls(), 'default' => array( array( 'title' => 'Certificate title', 'issuer' => 'Issuing organisation', 'year' => '' ), array( 'title' => 'Award or honour', 'issuer' => 'Event or institution', 'year' => '' ), array( 'title' => 'Course or diploma', 'issuer' => 'School or academy', 'year' => '' ) ), 'title_field' => '{{{ title }}}' ) );
		$this->txt( 'eyebrow', 'برچسب کوچک بالای عنوان', 'Credentials' );
		$this->txt( 'heading', 'عنوان بخش', 'Certificates & honours' );
		$this->sel( 'tag', 'تگ عنوان بخش', array( 'h2' => 'H2', 'h3' => 'H3', 'div' => 'div' ), 'h2' );
		$this->done();

		$this->sec( 's_layout', 'چیدمان و رفتار' );
		$this->sel( 'layout', 'چیدمان', array( 'grid' => 'کارت‌های شبکه‌ای', 'list' => 'فهرست ردیفی', 'slider' => 'نوار افقی لغزنده' ), 'grid', array( 'prefix_class' => 'ay-crl-' ) );
		$this->add_responsive_control( 'columns', array( 'label' => 'تعداد ستون', 'type' => CM::NUMBER, 'min' => 1, 'max' => 6, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1, 'selectors' => array( '{{WRAPPER}}' => '--ay-cr-cols: {{VALUE}};' ), 'condition' => array( 'layout' => 'grid' ) ) );
		$this->cssvar( 'gap', 'فاصلهٔ کارت‌ها', '--ay-cr-gap', 0, 60, 1, 18 );
		$this->add_control( 'aspect', array( 'label' => 'نسبت قاب تصویر (عرض ÷ ارتفاع)', 'type' => CM::SLIDER, 'range' => array( 'px' => array( 'min' => 0.5, 'max' => 2, 'step' => 0.01 ) ), 'default' => array( 'size' => 1.4 ), 'selectors' => array( '{{WRAPPER}}' => '--ay-cr-aspect: {{SIZE}};' ) ) );
		$this->sw( 'download', 'دکمهٔ دانلود در نمای بزرگ', 'yes' );
		$this->txt( 'l_view', 'متن «دیدن»', 'View', array( 'label_block' => false ) );
		$this->txt( 'l_download', 'متن «دانلود»', 'Download', array( 'label_block' => false ) );
		$this->txt( 'l_close', 'متن «بستن»', 'Close', array( 'label_block' => false ) );
		$this->done();

		$this->sec( 'st_card', 'کارت‌ها', true );
		$this->color( 'g_color', 'رنگ شیشهٔ کارت', '--ay-g-color' );
		$this->cssvar( 'g_op', 'پوشش رنگ شیشه', '--ay-g-op', 0, 1, 0.02, 0.6, '', array(), false );
		$this->cssvar( 'g_blur', 'تاری پشت شیشه', '--ay-g-blur', 0, 40, 1, 14, 'px', array(), false );
		$this->box_style( 'card', 'قاب کارت', '.ay-cr-item' );
		$this->color( 'accent', 'رنگ نشان و خط', '--ay-cr-accent' );
		$this->text_style( 'head', 'عنوان بخش', '.ay-cr-heading' );
		$this->text_style( 'title', 'عنوان مدرک', '.ay-cr-title' );
		$this->text_style( 'meta', 'صادرکننده و سال', '.ay-cr-meta' );
		$this->done();
		$this->sec( 'st_view', 'نمای بزرگ', true );
		$this->color( 'v_bg', 'رنگ پس‌زمینهٔ مات', '--ay-cr-veil' );
		$this->cssvar( 'v_op', 'پوشش رنگ پس‌زمینه', '--ay-cr-veil-op', 0, 1, 0.02, 0.6, '', array(), false );
		$this->cssvar( 'v_blur', 'تاری پس‌زمینه', '--ay-cr-veil-blur', 0, 60, 1, 22, 'px', array(), false );
		$this->done();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$edit = $this->in_editor();
		$list = array();
		foreach ( (array) $s['items'] as $it ) {
			$url = ! empty( $it['file']['url'] ) ? $it['file']['url'] : '';
			if ( ! $url && ! $edit ) {
				continue;
			}
			$id    = ! empty( $it['file']['id'] ) ? absint( $it['file']['id'] ) : 0;
			$mime  = $id ? (string) get_post_mime_type( $id ) : '';
			$pdf   = 'application/pdf' === $mime || (bool) preg_match( '/\.pdf(\?|$)/i', $url );
			$thumb = ! empty( $it['thumb']['url'] ) ? ( ! empty( $it['thumb']['id'] ) ? wp_get_attachment_image_url( absint( $it['thumb']['id'] ), 'large' ) : $it['thumb']['url'] ) : '';
			if ( ! $thumb && $url && ! $pdf ) {
				$thumb = $id ? wp_get_attachment_image_url( $id, 'large' ) : $url;
			}
			if ( ! $thumb && $pdf && $id ) {
				$thumb = (string) wp_get_attachment_image_url( $id, 'large' );   /* WordPress makes a preview of the first PDF page when it can */
			}
			$list[] = array( 'title' => (string) $it['title'], 'meta' => trim( $it['issuer'] . ( '' !== trim( (string) $it['year'] ) && '' !== trim( (string) $it['issuer'] ) ? ' · ' : '' ) . $it['year'] ), 'url' => $url, 'pdf' => $pdf, 'thumb' => $thumb );
		}
		if ( ! $list ) {
			return;
		}
		$tag = in_array( $s['tag'], array( 'h2', 'h3', 'div' ), true ) ? $s['tag'] : 'h2';
		$cfg = array( 'download' => 'yes' === $s['download'], 'dl' => $s['l_download'], 'close' => $s['l_close'] );
		echo '<div class="ay-cr" data-ay-cred="' . esc_attr( wp_json_encode( $cfg ) ) . '">';
		if ( '' !== trim( (string) $s['eyebrow'] ) ) {
			echo '<span class="ay-cr-eye">' . esc_html( $s['eyebrow'] ) . '</span>';
		}
		if ( '' !== trim( (string) $s['heading'] ) ) {
			echo '<' . $tag . ' class="ay-cr-heading">' . esc_html( $s['heading'] ) . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '<ul class="ay-cr-grid">';
		foreach ( $list as $i => $c ) {
			$doc = '<span class="ay-cr-doc" aria-hidden="true"><svg viewBox="0 0 48 60" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h24l12 12v42H6z"/><path d="M30 3v12h12M14 27h20M14 35h20M14 43h12"/><circle cx="33" cy="47" r="5"/><path d="m30 51-2 7 5-3 5 3-2-7"/></svg></span>';
			echo '<li class="ay-cr-li" style="--n:' . (int) $i . '">';
			if ( $c['url'] ) {
				echo '<button type="button" class="ay-cr-item" data-src="' . esc_url( $c['url'] ) . '" data-kind="' . ( $c['pdf'] ? 'pdf' : 'image' ) . '" data-title="' . esc_attr( $c['title'] ) . '" data-meta="' . esc_attr( $c['meta'] ) . '" aria-haspopup="dialog">';
			} else {
				echo '<div class="ay-cr-item ay-cr-draft">';
			}
			echo '<span class="ay-cr-pic">' . ( $c['thumb'] ? '<img src="' . esc_url( $c['thumb'] ) . '" alt="" loading="lazy">' : $doc ) . '<span class="ay-cr-open" aria-hidden="true">' . esc_html( $c['url'] ? $s['l_view'] : 'فایل را انتخاب کنید' ) . '</span></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<span class="ay-cr-txt"><span class="ay-cr-title">' . esc_html( $c['title'] ) . '</span>' . ( '' !== $c['meta'] ? '<span class="ay-cr-meta">' . esc_html( $c['meta'] ) . '</span>' : '' ) . '</span>';
			echo $c['url'] ? '</button>' : '</div>';
			echo '</li>';
		}
		echo '</ul></div>';
	}
}
