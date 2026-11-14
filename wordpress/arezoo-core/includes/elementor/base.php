<?php
/**
 * Base class of the Arezoo widgets: short helpers for the controls every widget needs,
 * so each widget can expose many clear, Persian-labelled settings.
 *
 * @package ArezooCore
 */

namespace Arezoo\Widgets;

use Elementor\Controls_Manager as CM;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Base extends \Elementor\Widget_Base {

	public function get_categories() {
		return array( 'arezoo' );
	}

	public function get_style_depends(): array {
		return array( 'arezoo' );
	}

	public function get_script_depends(): array {
		return array( 'arezoo' );
	}

	public function get_keywords() {
		return array( 'arezoo', 'آرزو' );
	}

	protected function is_dynamic_content(): bool {
		return true;
	}

	/* ---------------------------------------------------------------- sections */
	protected function sec( $id, $label, $style = false, $condition = null ) {
		$args = array( 'label' => $label, 'tab' => $style ? CM::TAB_STYLE : CM::TAB_CONTENT );
		if ( $condition ) {
			$args['condition'] = $condition;
		}
		$this->start_controls_section( $id, $args );
	}

	protected function done() {
		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------- controls */
	protected function txt( $id, $label, $default = '', $extra = array() ) {
		$this->add_control( $id, array_merge( array( 'label' => $label, 'type' => CM::TEXT, 'default' => $default, 'label_block' => true, 'dynamic' => array( 'active' => false ) ), $extra ) );
	}

	protected function area( $id, $label, $default = '', $extra = array() ) {
		$this->add_control( $id, array_merge( array( 'label' => $label, 'type' => CM::TEXTAREA, 'default' => $default, 'rows' => 3 ), $extra ) );
	}

	protected function sw( $id, $label, $default = 'yes', $extra = array() ) {
		$this->add_control( $id, array_merge( array( 'label' => $label, 'type' => CM::SWITCHER, 'label_on' => 'بله', 'label_off' => 'خیر', 'return_value' => 'yes', 'default' => $default ), $extra ) );
	}

	protected function sel( $id, $label, $options, $default, $extra = array() ) {
		$this->add_control( $id, array_merge( array( 'label' => $label, 'type' => CM::SELECT, 'options' => $options, 'default' => $default ), $extra ) );
	}

	protected function num( $id, $label, $default, $min, $max, $step = 1, $extra = array() ) {
		$this->add_control( $id, array_merge( array( 'label' => $label, 'type' => CM::NUMBER, 'default' => $default, 'min' => $min, 'max' => $max, 'step' => $step ), $extra ) );
	}

	protected function note( $id, $text ) {
		$this->add_control( $id, array( 'type' => CM::RAW_HTML, 'raw' => $text, 'content_classes' => 'elementor-descriptor ay-note' ) );
	}

	protected function head( $id, $label ) {
		$this->add_control( $id, array( 'label' => $label, 'type' => CM::HEADING, 'separator' => 'before' ) );
	}

	protected function media( $id, $label, $url = '', $extra = array() ) {
		$this->add_control( $id, array_merge( array( 'label' => $label, 'type' => CM::MEDIA, 'default' => array( 'url' => $url ), 'dynamic' => array( 'active' => false ) ), $extra ) );
	}

	/** Colour that writes one or more CSS properties. $map: [ selector => 'property' ] or a CSS variable name. */
	protected function color( $id, $label, $map, $extra = array() ) {
		$selectors = array();
		if ( is_string( $map ) ) {
			$selectors['{{WRAPPER}}'] = $map . ': {{VALUE}};';
		} else {
			foreach ( $map as $sel => $prop ) {
				$selectors[ '{{WRAPPER}} ' . $sel ] = $prop . ': {{VALUE}};';
			}
		}
		$this->add_control( $id, array_merge( array( 'label' => $label, 'type' => CM::COLOR, 'selectors' => $selectors ), $extra ) );
	}

	/**
	 * Responsive slider that writes a CSS variable on the widget.
	 * $unit '' means a plain number (speed, ratio, opacity).
	 */
	protected function cssvar( $id, $label, $var, $min, $max, $step, $default = null, $unit = 'px', $extra = array(), $responsive = true ) {
		$units = '' === $unit ? array( 'px' ) : ( is_array( $unit ) ? $unit : array( $unit ) );
		$range = array();
		foreach ( $units as $u ) {
			$range[ $u ] = array( 'min' => $min, 'max' => $max, 'step' => $step );
		}
		$args = array_merge( array(
			'label'      => $label,
			'type'       => CM::SLIDER,
			'size_units' => $units,
			'range'      => $range,
			'selectors'  => array( '{{WRAPPER}}' => $var . ': {{SIZE}}' . ( '' === $unit ? '' : '{{UNIT}}' ) . ';' ),
		), $extra );
		if ( null !== $default ) {
			$args['default'] = array( 'unit' => $units[0], 'size' => $default );
		}
		if ( $responsive ) {
			$this->add_responsive_control( $id, $args );
		} else {
			$this->add_control( $id, $args );
		}
	}

	protected function typo( $id, $label, $sel ) {
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $id, 'label' => $label, 'selector' => '{{WRAPPER}} ' . $sel ) );
	}

	/** Typography + colour (+ optional hover colour) for one piece of text. */
	protected function text_style( $id, $label, $sel, $hover = false ) {
		$this->head( $id . '_h', $label );
		$this->typo( $id . '_t', 'تایپوگرافی', $sel );
		$this->color( $id . '_c', 'رنگ', array( $sel => 'color' ) );
		if ( $hover ) {
			$this->color( $id . '_ch', 'رنگ هنگام هاور', array( $sel . ':hover' => 'color' ) );
		}
	}

	/** Background, border, corners, padding and shadow of one box. */
	protected function box_style( $id, $label, $sel, $padding = true ) {
		$this->head( $id . '_h', $label );
		$this->color( $id . '_bg', 'رنگ زمینه', array( $sel => 'background-color' ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => $id . '_b', 'label' => 'حاشیه', 'selector' => '{{WRAPPER}} ' . $sel ) );
		$this->add_responsive_control( $id . '_r', array( 'label' => 'گردی گوشه‌ها', 'type' => CM::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} ' . $sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		if ( $padding ) {
			$this->add_responsive_control( $id . '_p', array( 'label' => 'فاصلهٔ داخلی', 'type' => CM::DIMENSIONS, 'size_units' => array( 'px', 'em', '%' ), 'selectors' => array( '{{WRAPPER}} ' . $sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		}
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => $id . '_s', 'label' => 'سایه', 'selector' => '{{WRAPPER}} ' . $sel ) );
	}

	protected function filters( $id, $label, $sel ) {
		$this->add_group_control( Group_Control_Css_Filter::get_type(), array( 'name' => $id, 'label' => $label, 'selector' => '{{WRAPPER}} ' . $sel ) );
	}

	protected function align( $id, $sel, $prop = 'text-align' ) {
		$this->add_responsive_control( $id, array(
			'label'     => 'تراز',
			'type'      => CM::CHOOSE,
			'options'   => array(
				'left'   => array( 'title' => 'چپ', 'icon' => 'eicon-text-align-left' ),
				'center' => array( 'title' => 'وسط', 'icon' => 'eicon-text-align-center' ),
				'right'  => array( 'title' => 'راست', 'icon' => 'eicon-text-align-right' ),
			),
			'selectors' => array( '{{WRAPPER}} ' . $sel => $prop . ': {{VALUE}};' ),
		) );
	}

	/** The shared "which works" controls of the portfolio widgets. */
	protected function query_controls( $count = -1 ) {
		$terms = function ( $tax ) {
			$out = array();
			foreach ( ay_terms_ordered( $tax ) as $t ) {
				$out[ $t->term_id ] = $t->name;
			}
			return $out;
		};
		$this->add_control( 'q_collection', array( 'label' => 'فقط این کالکشن‌ها', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $terms( 'ay_collection' ), 'description' => 'خالی = همهٔ کالکشن‌ها' ) );
		$this->add_control( 'q_type', array( 'label' => 'فقط این نوع‌ها', 'type' => CM::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $terms( 'ay_type' ), 'description' => 'خالی = همهٔ نوع‌ها' ) );
		$this->sw( 'q_featured', 'فقط آثار ویژه', '' );
		$this->num( 'q_count', 'تعداد آثار', $count, -1, 200, 1, array( 'description' => '۱- یعنی همه' ) );
		$this->sel( 'q_order', 'ترتیب', array( 'menu' => 'ترتیب دستی (عدد «ترتیب» هر اثر)', 'new' => 'تازه‌ترین اول', 'old' => 'قدیمی‌ترین اول', 'title' => 'الفبایی', 'rand' => 'تصادفی' ), 'menu' );
	}

	protected function query_works( $s, $extra = array() ) {
		return ay_query_works( array_merge( array(
			'collection' => isset( $s['q_collection'] ) ? (array) $s['q_collection'] : array(),
			'type'       => isset( $s['q_type'] ) ? (array) $s['q_type'] : array(),
			'featured'   => isset( $s['q_featured'] ) && 'yes' === $s['q_featured'],
			'count'      => isset( $s['q_count'] ) ? (int) $s['q_count'] : -1,
			'order'      => isset( $s['q_order'] ) ? $s['q_order'] : 'menu',
		), $extra ) );
	}

	protected function in_editor() {
		return \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode();
	}

	/** Friendly placeholder shown in the editor when a widget has nothing to display yet. */
	protected function empty_note( $text ) {
		if ( $this->in_editor() ) {
			echo '<div class="ay-empty" dir="rtl">' . esc_html( $text ) . '</div>';
		}
	}

	protected function size( $v, $default = 0 ) {
		return ( is_array( $v ) && isset( $v['size'] ) && '' !== $v['size'] ) ? (float) $v['size'] : $default;
	}

	/** One portfolio card (shared by the archive and "related" widgets). */
	protected function work_card( $id, $n = 0 ) {
		$w = ay_work( $id );
		if ( ! $w ) {
			return '';
		}
		$meta = array();
		$cols = array();
		$typs = array();
		foreach ( $w['collections'] as $t ) {
			$meta[] = $t->name;
			$cols[] = $t->term_id;
		}
		foreach ( $w['types'] as $t ) {
			$meta[] = $t->name;
			$typs[] = $t->term_id;
		}
		return '<a class="ay-card" style="--n:' . (int) $n . '" href="' . esc_url( $w['url'] ) . '" data-col="' . esc_attr( implode( ' ', $cols ) ) . '" data-type="' . esc_attr( implode( ' ', $typs ) ) . '">'
			. '<span class="ay-card-pic">' . ay_work_img( $id, 'ay-card' ) . '</span>'
			. '<span class="ay-card-txt"><span class="ay-card-name">' . esc_html( $w['title'] ) . '</span><span class="ay-card-meta">' . esc_html( implode( ' · ', $meta ) ) . '</span></span></a>';
	}
}
