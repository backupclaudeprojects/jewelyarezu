<?php
/**
 * Site setup: one run creates everything the site needs — pages, header, footer, single
 * work / archive / 404 templates, the menu, the front page, permalinks, Elementor defaults,
 * fonts, the colour theme and sample works. Running it again only adds what is missing;
 * it never overwrites a page or template that already exists.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ay_setup_pages() {
	return array(
		'home'        => array( 'title' => 'Home', 'slug' => 'home' ),
		'collections' => array( 'title' => 'Collections', 'slug' => 'collections' ),
		'about'       => array( 'title' => 'About Me', 'slug' => 'about-me' ),
		'contact'     => array( 'title' => 'Contact', 'slug' => 'contact' ),
	);
}

/** Elementor layout of a page as it is first built. */
function ay_setup_page_layout( $key ) {
	switch ( $key ) {
		case 'home':
			$full = array( 'content_width' => 'full', 'padding' => ay_box( 0 ) );
			return array(
				ay_demo_hero( 1 ),
				ay_band( array(
					ay_row( array(
						ay_col( array( ay_eyebrow( 'Jewelry Designer' ) ), array( 'width' => ay_px( 25, '%' ), 'width_mobile' => ay_px( 100, '%' ) ) ),
						ay_col( array(
							ay_heading( 'I specialize in jewelry design, concept development, and visual presentation.', 'p', 46, array( 'typography_font_weight' => '400', 'typography_line_height' => ay_px( 1.2, 'em' ) ) ),
							ay_button( 'About me', ay_page_url( 'about-me' ), 'line' ),
						), array( 'width' => ay_px( 75, '%' ), 'width_mobile' => ay_px( 100, '%' ), 'flex_gap' => ay_gap( 28 ), 'ay_rv' => 'up' ) ),
					) ),
				), array( 'ay_sk' => 'ribbon-pendant', 'ay_sk_op' => ay_px( 0.07 ), 'ay_sk_x' => ay_px( 90, '%' ), 'ay_sk_size' => ay_px( 420 ) ) ),
				ay_band( array( ay_eyebrow( 'Portfolio' ), ay_heading( 'Selected pieces', 'h2', 50 ) ), array( 'padding' => ay_box( 40, 24, 30, 24 ), 'flex_gap' => ay_gap( 10 ) ) ),
				ay_c( array_merge( $full, array( 'padding' => ay_box( 0, 0, 60, 0 ) ) ), array( ay_w( 'ay-hscroll', array() ) ) ),
				ay_band( array( ay_eyebrow( 'Digital design' ), ay_heading( 'How a piece is designed', 'h2', 50 ), ay_w( 'ay-process', ay_process_settings() ) ), array( 'flex_gap' => ay_gap( 18 ), 'padding' => ay_box( 70, 24, 40, 24 ), '_element_id' => 'ay-process' ) ),
				ay_band( array( ay_eyebrow( 'Collections' ), ay_heading( 'Three collections, piece by piece', 'h2', 50 ), ay_w( 'ay-stack', array() ) ), array( 'flex_gap' => ay_gap( 18 ) ) ),
				ay_band( array(
					ay_row( array(
						ay_col( array( ay_w( 'ay-compare', array( 'after' => ay_media( 'img/studio/necklace-detail.webp' ) ) ) ), array( 'width' => ay_px( 44, '%' ), 'width_mobile' => ay_px( 100, '%' ), 'ay_rv' => 'ink' ) ),
						ay_col( array(
							ay_eyebrow( 'Sketch to jewel' ),
							ay_heading( 'Every piece is drawn before it is painted', 'h2', 50 ),
							ay_text( '<p>Digital renderings communicate the design’s form, proportions, materials, and gemstones through detailed visual presentations.</p>' ),
							ay_row( array(
								ay_w( 'ay-motion', array( 'type' => 'sketch', 'caption' => 'Sketch', 'size' => ay_px( 120 ), '_element_width' => 'auto' ) ),
								ay_w( 'ay-motion', array( 'type' => 'layers', 'caption' => 'Layers', 'size' => ay_px( 120 ), '_element_width' => 'auto' ) ),
								ay_w( 'ay-motion', array( 'type' => 'wire', 'caption' => '3D form', 'size' => ay_px( 120 ), '_element_width' => 'auto' ) ),
								ay_w( 'ay-motion', array( 'type' => 'render', 'caption' => 'Rendering', 'size' => ay_px( 120 ), '_element_width' => 'auto' ) ),
							), array( 'flex_gap' => ay_gap( 26 ), 'flex_direction_mobile' => 'row', 'flex_wrap' => 'wrap', 'flex_wrap_mobile' => 'wrap', 'padding' => ay_box( 18, 0, 0, 0 ) ) ),
						), array( 'width' => ay_px( 56, '%' ), 'width_mobile' => ay_px( 100, '%' ), 'flex_gap' => ay_gap( 16 ), 'flex_justify_content' => 'center' ) ),
					), array( 'flex_gap' => ay_gap( 80 ), 'flex_align_items' => 'center' ) ),
				) ),
				ay_c( array_merge( $full, array( 'padding' => ay_box( 10, 0, 30, 0 ) ) ), array( ay_w( 'ay-strip', array() ) ) ),
				ay_closing( 'Have a question about a piece or a collection?', 'Get in touch', ay_page_url( 'contact' ) ),
			);
		case 'collections':
			return array( ay_page_head( 'Portfolio', 'Collections', 'Browse every piece, or narrow the view by collection and by type.' ), ay_demo_archive( 1 ) );
		case 'about':
			return ay_demo_about( 1 );
		case 'contact':
			return array(
				ay_page_head( 'Contact', 'Write to me', 'Every message is read and answered personally.' ),
				ay_band( array( ay_w( 'ay-contact', array() ) ), array( 'padding' => ay_box( 10, 24, 40, 24 ) ) ),
				ay_demo_faq( 2 ),
			);
	}
	return array();
}

function ay_setup_part_layout( $slot ) {
	switch ( $slot ) {
		case 'header':
			return ay_demo_header( 1 );
		case 'footer':
			return ay_demo_footer( 1 );
		case 'single':
			return ay_demo_single( 1 );
		case 'archive':
			return array( ay_page_head( 'Portfolio', 'Collections', 'Browse every piece, or narrow the view by collection and by type.' ), ay_band( array( ay_w( 'ay-archive', array() ) ), array( 'padding' => ay_box( 10, 24, 110, 24 ) ) ) );
		case '404':
			return array( ay_band( array(
				ay_w( 'ay-sketch', array( 'motif' => 'filigree-rings', 'size' => ay_px( 260 ), 'op' => ay_px( 0.5 ), 'motion' => 'float' ) ),
				ay_eyebrow( 'Error 404' ),
				ay_heading( 'This page is not in the collection', 'h1', 68 ),
				ay_text( '<p>The link may be old, or the piece may have been renamed.</p>' ),
				ay_button( 'Explore Jewelry', ay_page_url( 'collections' ) ),
			), array_merge( ay_pad_top(), array( 'flex_gap' => ay_gap( 16 ), 'padding' => ay_box( 150, 24, 110, 24 ) ) ) ) );
	}
	return array();
}

/**
 * Build (or complete) the site.
 *
 * @param array $args samples: import sample works; force: rebuild layouts of existing pages/parts too.
 * @return array log lines (Persian)
 */
function ay_build_site( $args = array() ) {
	$args = wp_parse_args( $args, array( 'samples' => true, 'force' => false ) );
	$log  = array();
	if ( ! ay_elementor_ready() || ! ay_load_widgets() ) {
		return array( 'المنتور نصب و فعال نیست؛ ابتدا آن را فعال کنید.' );
	}

	/* 1 · WordPress and Elementor settings */
	if ( '' === (string) get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$log[] = 'پیوندهای یکتا روی «نام نوشته» تنظیم شد.';
	}
	foreach ( array( 'elementor_disable_color_schemes' => 'yes', 'elementor_disable_typography_schemes' => 'yes', 'elementor_experiment-container' => 'active', 'elementor_unfiltered_files_upload' => '' ) as $k => $v ) {
		if ( '' !== $v && get_option( $k ) !== $v ) {
			update_option( $k, $v );
		}
	}
	$cpt = (array) get_option( 'elementor_cpt_support', array( 'post', 'page' ) );
	if ( ! in_array( 'page', $cpt, true ) ) {
		$cpt[] = 'page';
		update_option( 'elementor_cpt_support', $cpt );
	}
	if ( false === get_option( 'arezoo_settings' ) ) {
		add_option( 'arezoo_settings', ay_default_settings() );
	}
	if ( '' === trim( (string) get_option( 'blogname' ) ) ) {
		update_option( 'blogname', ay_opt( 'brand' ) );
	}
	ay_apply_kit_defaults();
	$log[] = 'تنظیمات المنتور (کانتینرها، فونت‌ها، عرض سایت) اعمال شد.';

	/* 2 · sample works */
	if ( $args['samples'] ) {
		$n = ay_import_samples();
		if ( $n ) {
			$log[] = $n . ' اثر نمونه و دسته‌بندی‌هایش وارد شد.';
		}
	}

	/* 3 · pages (created first, so links between them resolve) */
	$created = array();
	foreach ( ay_setup_pages() as $key => $p ) {
		$id = (int) get_option( 'arezoo_page_' . $key );
		if ( ! $id || 'page' !== get_post_type( $id ) || 'trash' === get_post_status( $id ) ) {
			$existing = get_page_by_path( $p['slug'] );
			if ( $existing && 'publish' === $existing->post_status && ! get_post_meta( $existing->ID, '_elementor_data', true ) && '' === trim( (string) $existing->post_content ) ) {
				$id = (int) $existing->ID;
			} else {
				$id = (int) wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $p['title'], 'post_name' => $p['slug'], 'comment_status' => 'closed' ) );
			}
			if ( $id ) {
				update_option( 'arezoo_page_' . $key, $id );
				$created[ $key ] = $id;
				$log[]           = 'برگهٔ «' . $p['title'] . '» ساخته شد.';
			}
		}
	}
	foreach ( ay_setup_pages() as $key => $p ) {
		$id = (int) get_option( 'arezoo_page_' . $key );
		if ( $id && ( isset( $created[ $key ] ) || $args['force'] ) ) {
			if ( $args['force'] && ! isset( $created[ $key ] ) ) {
				ay_backup_layout( $id, 'rebuild' );
			}
			ay_save_layout( $id, ay_setup_page_layout( $key ), 'wp-page' );
		}
	}

	/* 4 · site parts (Elementor saved templates) */
	$types = array( 'header' => 'section', 'footer' => 'section', 'single' => 'page', 'archive' => 'page', '404' => 'page' );
	foreach ( ay_parts() as $slot => $label ) {
		$id  = ay_part_id( $slot );
		$new = false;
		if ( ! $id ) {
			$id = (int) wp_insert_post( array( 'post_type' => 'elementor_library', 'post_status' => 'publish', 'post_title' => 'آرزو — ' . $label ) );
			if ( ! $id ) {
				continue;
			}
			wp_set_object_terms( $id, $types[ $slot ], 'elementor_library_type' );
			ay_set_part( $slot, $id );
			$new   = true;
			$log[] = 'قالب «' . $label . '» ساخته شد.';
		}
		if ( $new || $args['force'] ) {
			if ( ! $new ) {
				ay_backup_layout( $id, 'rebuild' );
			}
			ay_save_layout( $id, ay_setup_part_layout( $slot ), $types[ $slot ] );
		}
	}

	/* 5 · menu */
	$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['primary'] ) || ! wp_get_nav_menu_object( $locations['primary'] ) ) {
		$menu = wp_get_nav_menu_object( 'Arezoo main menu' );
		$mid  = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( 'Arezoo main menu' );
		if ( $mid && ! is_wp_error( $mid ) ) {
			if ( ! wp_get_nav_menu_items( $mid ) ) {
				foreach ( ay_setup_pages() as $key => $p ) {
					$pid = (int) get_option( 'arezoo_page_' . $key );
					if ( $pid ) {
						wp_update_nav_menu_item( $mid, 0, array( 'menu-item-title' => $p['title'], 'menu-item-object' => 'page', 'menu-item-object-id' => $pid, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
					}
				}
			}
			$locations['primary'] = $mid;
			set_theme_mod( 'nav_menu_locations', $locations );
			$log[] = 'فهرست اصلی ساخته و به جایگاه «منوی اصلی» داده شد.';
		}
	}

	/* 6 · front page */
	$home = (int) get_option( 'arezoo_page_home' );
	if ( $home && ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) || isset( $created['home'] ) ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
		$log[] = 'برگهٔ «Home» صفحهٔ اصلی سایت شد.';
	}

	/* 7 · colour theme + demo record */
	ay_apply_theme( ay_active_theme_id() );
	$log[] = 'تم رنگی «' . ay_active_theme()['label'] . '» اعمال شد.';
	$state = ay_demo_state();
	foreach ( array( 'hero' => 1, 'archive' => 1, 'single' => 1, 'about' => 1, 'faq' => 2, 'header' => 1, 'footer' => 1 ) as $area => $n ) {
		list( $pid ) = ay_demo_target( $area );
		if ( $pid && ( empty( $state[ $area ] ) || $args['force'] ) ) {
			$state[ $area ] = array( 'n' => $n, 'hash' => md5( (string) get_post_meta( $pid, '_elementor_data', true ) ), 'time' => time() );
		}
	}
	update_option( 'arezoo_demos', $state );

	flush_rewrite_rules();
	\Elementor\Plugin::$instance->files_manager->clear_cache();
	update_option( 'arezoo_built', time() );
	$log[] = 'سایت آماده است.';
	return $log;
}

/** A fresh site: nothing was built yet and WordPress still shows the blog on the front page. */
function ay_site_is_fresh() {
	if ( get_option( 'arezoo_built' ) ) {
		return false;
	}
	$pages = wp_count_posts( 'page' );
	return 'page' !== get_option( 'show_on_front' ) && (int) $pages->publish <= 2;
}

/**
 * On a fresh site everything is set up automatically the first time an administrator
 * opens the dashboard after the theme, Elementor and this plugin are all active.
 * On a site that already has content nothing is touched until «ساخت سایت» is pressed.
 */
add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_options' ) || get_option( 'arezoo_built' ) || get_transient( 'arezoo_building' ) ) {
		return;
	}
	if ( ! ay_elementor_ready() || 'arezoo' !== get_template() || ! ay_site_is_fresh() ) {
		return;
	}
	if ( ! did_action( 'elementor/init' ) ) {
		return;
	}
	set_transient( 'arezoo_building', 1, 120 );
	$log = ay_build_site();
	delete_transient( 'arezoo_building' );
	set_transient( 'arezoo_build_log', $log, 300 );
}, 30 );
