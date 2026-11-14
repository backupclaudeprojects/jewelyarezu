<?php
/**
 * Portfolio content: works, collections, types, the work editor and sample content.
 * Works without Elementor, so entries never disappear when it is switched off.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ay_register_content() {
	register_post_type( 'ay_work', array(
		'labels'        => array(
			'name'               => 'آثار',
			'singular_name'      => 'اثر',
			'add_new'            => 'افزودن اثر',
			'add_new_item'       => 'افزودن اثر تازه',
			'edit_item'          => 'ویرایش اثر',
			'new_item'           => 'اثر تازه',
			'view_item'          => 'نمایش اثر',
			'search_items'       => 'جستجوی آثار',
			'not_found'          => 'اثری پیدا نشد.',
			'not_found_in_trash' => 'اثری در زباله‌دان نیست.',
			'all_items'          => 'آثار',
			'menu_name'          => 'آثار',
			'featured_image'     => 'تصویر اصلی اثر',
			'set_featured_image' => 'انتخاب تصویر اصلی',
			'remove_featured_image' => 'برداشتن تصویر اصلی',
			'use_featured_image' => 'استفاده به‌عنوان تصویر اصلی',
		),
		'public'        => true,
		'show_in_menu'  => 'arezoo',
		'show_in_rest'  => false,
		'has_archive'   => false,
		'menu_icon'     => 'dashicons-star-filled',
		'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'rewrite'       => array( 'slug' => 'work', 'with_front' => false ),
	) );

	$tax = function ( $one, $many ) {
		return array(
			'name'          => $many,
			'singular_name' => $one,
			'all_items'     => 'همهٔ ' . $many,
			'edit_item'     => 'ویرایش ' . $one,
			'add_new_item'  => 'افزودن ' . $one,
			'search_items'  => 'جستجو',
			'not_found'     => 'موردی پیدا نشد.',
			'menu_name'     => $many,
		);
	};
	register_taxonomy( 'ay_collection', 'ay_work', array(
		'labels'            => $tax( 'کالکشن', 'کالکشن‌ها' ),
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_menu'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => false,
		'rewrite'           => array( 'slug' => 'collection', 'with_front' => false ),
	) );
	register_taxonomy( 'ay_type', 'ay_work', array(
		'labels'            => $tax( 'نوع', 'نوع‌ها' ),
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_menu'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => false,
		'rewrite'           => array( 'slug' => 'jewelry-type', 'with_front' => false ),
	) );

	foreach ( array( 'materials', 'gemstones', 'technique', 'year', 'featured', 'gallery', 'specs' ) as $k ) {
		register_post_meta( 'ay_work', '_ay_' . $k, array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
}
add_action( 'init', 'ay_register_content', 5 );

/* Work pictures are shown whole; this size is only a lighter file for cards. */
add_action( 'after_setup_theme', function () {
	add_image_size( 'ay-card', 900, 900, false );
}, 20 );

add_filter( 'enter_title_here', function ( $text, $post ) {
	return 'ay_work' === $post->post_type ? 'نام اثر' : $text;
}, 10, 2 );

/* --------------------------------------------------------------------------
 * Work editor: one clear panel made of cards with icons and live previews.
 * ----------------------------------------------------------------------- */
add_action( 'add_meta_boxes_ay_work', function () {
	add_meta_box( 'ay_work_box', 'جزئیات اثر', 'ay_work_box', 'ay_work', 'normal', 'high' );
} );

add_action( 'edit_form_after_title', function ( $post ) {
	if ( 'ay_work' === $post->post_type ) {
		echo '<p class="ay-ed-hint" dir="rtl">توضیح اثر را در کادر زیر بنویسید (به انگلیسی، چون روی سایت نمایش داده می‌شود). تصویر اصلی را از ستون کناری «تصویر اصلی اثر» انتخاب کنید و کالکشن و نوع را هم از همان ستون تیک بزنید.</p>';
	}
} );

function ay_work_box( $post ) {
	wp_nonce_field( 'ay_work_save', 'ay_work_nonce' );
	$gallery  = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post->ID, '_ay_gallery', true ) ) ) );
	$featured = '1' === (string) get_post_meta( $post->ID, '_ay_featured', true );
	echo '<div class="ay-ed" dir="rtl" style="--ay-wash:' . esc_attr( ay_hex( ay_opt( 'admin_wash' ), '#99E1D9' ) ) . '">';

	/* gallery */
	echo '<section class="ay-ed-card ay-ed-wide"><h3>' . ay_icon( 'image', 22 ) . '<span>گالری تصاویر</span></h3>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p class="ay-ed-help">تصاویر بیشتر این اثر برای صفحهٔ تک‌اثر. تصویر اصلی همیشه اول نمایش داده می‌شود. برای تغییر ترتیب، تصاویر را بکشید.</p>';
	echo '<input type="hidden" id="ay_gallery" name="ay_gallery" value="' . esc_attr( implode( ',', $gallery ) ) . '">';
	echo '<div id="ay_gallery_list" class="ay-ed-gallery">';
	foreach ( $gallery as $gid ) {
		$src = wp_get_attachment_image_url( $gid, 'thumbnail' );
		if ( $src ) {
			echo '<span class="ay-ed-thumb" data-id="' . (int) $gid . '"><img src="' . esc_url( $src ) . '" alt=""><button type="button" class="ay-ed-x" aria-label="حذف این تصویر">×</button></span>';
		}
	}
	echo '</div><p><button type="button" class="button button-primary" id="ay_gallery_add">افزودن تصویر به گالری</button> <button type="button" class="button-link" id="ay_gallery_clear">پاک کردن همه</button></p></section>';

	/* named details */
	echo '<section class="ay-ed-card"><h3>' . ay_icon( 'list', 22 ) . '<span>مشخصات اصلی</span></h3><p class="ay-ed-help">هر کدام خالی بماند، در سایت نمایش داده نمی‌شود.</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	foreach ( ay_work_fields() as $key => $f ) {
		printf(
			'<label class="ay-ed-field" for="ay_%1$s">%2$s<span>%3$s</span><input type="text" id="ay_%1$s" name="ay_%1$s" value="%4$s" placeholder="%5$s" dir="ltr"></label>',
			esc_attr( $key ),
			ay_icon( $f['icon'], 18 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $f['label'] ),
			esc_attr( get_post_meta( $post->ID, '_ay_' . $key, true ) ),
			esc_attr( $f['ph'] )
		);
	}
	echo '</section>';

	/* free details */
	$row = function ( $i, $t, $v ) {
		return '<div class="ay-ed-row"><span class="ay-ed-drag" title="جابه‌جایی">⋮⋮</span><input type="text" dir="ltr" placeholder="عنوان (مثلاً Carat)" name="ay_specs[' . $i . '][0]" value="' . esc_attr( $t ) . '"><input type="text" dir="ltr" placeholder="مقدار (مثلاً 2.4 ct)" name="ay_specs[' . $i . '][1]" value="' . esc_attr( $v ) . '"><button type="button" class="ay-ed-x" aria-label="حذف این ردیف">×</button></div>';
	};
	echo '<section class="ay-ed-card"><h3>' . ay_icon( 'gem', 22 ) . '<span>جزئیات دلخواه</span></h3><p class="ay-ed-help">هر تعداد جزئیات دیگر، مثل وزن، اندازه یا نوع بست. بعد از مشخصات اصلی نمایش داده می‌شوند.</p><div id="ay_specs_list">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	foreach ( ay_work_extra( $post->ID ) as $i => $sp ) {
		echo $row( (int) $i, $sp[0], $sp[1] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div><p><button type="button" class="button" id="ay_specs_add">+ افزودن ردیف</button></p>';
	echo '<script type="text/template" id="ay_specs_tpl">' . $row( '__i__', '', '' ) . '</script></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	/* display */
	echo '<section class="ay-ed-card"><h3>' . ay_icon( 'star', 22 ) . '<span>نمایش</span></h3>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<label class="ay-ed-check"><input type="checkbox" name="ay_featured" value="1" ' . checked( $featured, true, false ) . '><span><strong>اثر ویژه</strong><br>در بخش‌هایی که فقط آثار ویژه را نشان می‌دهند، این اثر نمایش داده شود.</span></label>';
	echo '<label class="ay-ed-field" for="ay_order">' . ay_icon( 'list', 18 ) . '<span>ترتیب</span><input type="number" id="ay_order" name="ay_order" value="' . (int) $post->menu_order . '" min="0" step="1" dir="ltr"></label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p class="ay-ed-help">عدد کوچک‌تر زودتر نمایش داده می‌شود. آثار با عدد یکسان، از تازه به قدیم مرتب می‌شوند.</p></section>';
	echo '</div>';
}

add_action( 'save_post_ay_work', function ( $post_id ) {
	if ( ! isset( $_POST['ay_work_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ay_work_nonce'] ) ), 'ay_work_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array_keys( ay_work_fields() ) as $key ) {
		update_post_meta( $post_id, '_ay_' . $key, isset( $_POST[ 'ay_' . $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ay_' . $key ] ) ) : '' );
	}
	update_post_meta( $post_id, '_ay_featured', empty( $_POST['ay_featured'] ) ? '' : '1' );
	$gallery = isset( $_POST['ay_gallery'] ) ? implode( ',', array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['ay_gallery'] ) ) ) ) ) ) : '';
	update_post_meta( $post_id, '_ay_gallery', $gallery );
	$specs = array();
	if ( isset( $_POST['ay_specs'] ) && is_array( $_POST['ay_specs'] ) ) {
		foreach ( wp_unslash( $_POST['ay_specs'] ) as $sp ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$t = isset( $sp[0] ) ? sanitize_text_field( $sp[0] ) : '';
			$v = isset( $sp[1] ) ? sanitize_text_field( $sp[1] ) : '';
			if ( '' !== $t ) {
				$specs[] = array( $t, $v );
			}
		}
	}
	update_post_meta( $post_id, '_ay_specs', wp_slash( wp_json_encode( array_slice( $specs, 0, 40 ), JSON_UNESCAPED_UNICODE ) ) );
	if ( isset( $_POST['ay_order'] ) ) {
		$order = absint( wp_unslash( $_POST['ay_order'] ) );
		if ( (int) get_post_field( 'menu_order', $post_id ) !== $order ) {
			remove_all_actions( 'save_post_ay_work' );
			wp_update_post( array( 'ID' => $post_id, 'menu_order' => $order ) );
		}
	}
} );

/* list table: picture and "featured" columns */
add_filter( 'manage_ay_work_posts_columns', function ( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		if ( 'title' === $k ) {
			$out['ay_pic'] = 'تصویر';
		}
		$out[ $k ] = $v;
	}
	$out['ay_feat'] = 'ویژه';
	return $out;
} );
add_action( 'manage_ay_work_posts_custom_column', function ( $col, $id ) {
	if ( 'ay_pic' === $col ) {
		echo get_the_post_thumbnail( $id, array( 56, 56 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} elseif ( 'ay_feat' === $col ) {
		echo '1' === (string) get_post_meta( $id, '_ay_featured', true ) ? '★' : '—';
	}
}, 10, 2 );

/* --------------------------------------------------------------------------
 * Sample content (the three sample sets, cut into ten single pieces).
 * Everything created here is flagged, so it can be removed with one button.
 * ----------------------------------------------------------------------- */
function ay_sample_works() {
	return array(
		array( 'emerald-kite-earrings', 'Emerald Kite Earrings', 'Kite', 'Earrings', '2025', 'White gold', 'Kite-cut green stones, emerald-cut white stones', array( array( 'Pieces', 'Pair' ) ), 'A pair of drop earrings: an emerald-cut white stone above a long kite-cut green stone.', 1 ),
		array( 'emerald-kite-drop', 'Emerald Kite Drop', 'Kite', 'Earrings', '2025', 'White gold', 'Kite-cut green stone, emerald-cut white stone', array( array( 'Pieces', 'Single earring' ) ), 'The single drop from the Kite pair, shown on its own to read the cut and the setting.', 0 ),
		array( 'ruby-ribbon-set', 'Ruby Ribbon Set', 'Ribbon', 'Set', '2025', 'White gold, satin ribbon', 'Red stones, white stones', array( array( 'Pieces', 'Necklace, earrings, bracelet' ) ), 'The full set: a necklace tied with a satin ribbon, blossom earrings and a matching bracelet.', 1 ),
		array( 'ruby-ribbon-necklace', 'Ruby Ribbon Necklace', 'Ribbon', 'Necklace', '2025', 'White gold, satin ribbon', 'Red stones, white stones', array( array( 'Closure', 'Ribbon bow' ) ), 'A graduated line of white stones closing in a red blossom, finished with a ribbon bow.', 0 ),
		array( 'ruby-blossom-earrings', 'Ruby Blossom Earrings', 'Ribbon', 'Earrings', '2025', 'White gold', 'Red stones, white stones', array( array( 'Pieces', 'Pair' ) ), 'Clustered red blossoms with falling lines of white stones.', 0 ),
		array( 'ruby-ribbon-bracelet', 'Ruby Ribbon Bracelet', 'Ribbon', 'Bracelet', '2025', 'White gold', 'Red stones, white stones', array( array( 'Motif', 'Single blossom' ) ), 'A line bracelet of white stones with one red blossom at its centre.', 0 ),
		array( 'blue-bow-set', 'Blue Bow Set', 'Bow', 'Set', '2024', 'Yellow gold, white gold', 'Blue stones', array( array( 'Pieces', 'Necklace, studs, bracelet' ) ), 'The full set: a fine collar, a pair of studs and a bracelet, each tied with a two-tone bow.', 1 ),
		array( 'blue-bow-necklace', 'Blue Bow Necklace', 'Bow', 'Necklace', '2024', 'Yellow gold, white gold', 'Blue stone', array( array( 'Form', 'Rigid collar' ) ), 'A fine gold collar carrying a two-tone bow and a single blue stone.', 0 ),
		array( 'blue-bow-studs', 'Blue Bow Studs', 'Bow', 'Earrings', '2024', 'Yellow gold, white gold', 'Blue stones', array( array( 'Pieces', 'Pair' ) ), 'Stud earrings: a round blue stone above a bow in yellow and white gold.', 0 ),
		array( 'blue-bow-bracelet', 'Blue Bow Bracelet', 'Bow', 'Bracelet', '2024', 'Yellow gold, white gold', 'Blue stones', array( array( 'Motif', 'Centre bow' ) ), 'A beaded gold bracelet with a centre bow and three blue stones.', 0 ),
	);
}

/** Copy a bundled picture into the Media Library once and return its attachment id. */
function ay_asset_attachment( $rel, $alt = '', $sample = true ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_ay_asset', 'meta_value' => $rel ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $found ) {
		return (int) $found[0];
	}
	$src = AY_DIR . 'assets/' . $rel;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$up = wp_upload_dir();
	if ( ! empty( $up['error'] ) ) {
		return 0;
	}
	$name = wp_unique_filename( $up['path'], 'arezoo-' . basename( $rel ) );
	$dest = trailingslashit( $up['path'] ) . $name;
	if ( ! copy( $src, $dest ) ) {
		return 0;
	}
	$type = wp_check_filetype( $dest );
	$id   = wp_insert_attachment( array( 'post_mime_type' => $type['type'], 'post_title' => $alt ? $alt : preg_replace( '/\.[^.]+$/', '', basename( $rel ) ), 'post_status' => 'inherit' ), $dest );
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $dest ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_ay_asset', $rel );
	if ( $sample ) {
		update_post_meta( $id, '_ay_sample', '1' );
	}
	return (int) $id;
}

function ay_sample_term( $name, $tax, $order ) {
	$t = term_exists( $name, $tax );
	if ( $t ) {
		return (int) $t['term_id'];
	}
	$t = wp_insert_term( $name, $tax );
	if ( is_wp_error( $t ) ) {
		return 0;
	}
	update_term_meta( $t['term_id'], '_ay_order', $order );
	update_term_meta( $t['term_id'], '_ay_sample', '1' );
	return (int) $t['term_id'];
}

/** Import the ten sample works. Safe to run twice: existing samples are kept. */
function ay_import_samples() {
	$done = 0;
	$cols = array();
	$typs = array();
	foreach ( array( 'Kite', 'Ribbon', 'Bow' ) as $i => $n ) {
		$cols[ $n ] = ay_sample_term( $n, 'ay_collection', $i + 1 );
	}
	foreach ( array( 'Earrings', 'Necklace', 'Bracelet', 'Set' ) as $i => $n ) {
		$typs[ $n ] = ay_sample_term( $n, 'ay_type', $i + 1 );
	}
	$imgs = array();
	foreach ( ay_sample_works() as $w ) {
		$imgs[ $w[0] ] = ay_asset_attachment( 'img/works/' . $w[0] . '.webp', $w[1] );
	}
	foreach ( ay_sample_works() as $i => $w ) {
		$exists = get_posts( array( 'post_type' => 'ay_work', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_ay_sample', 'meta_value' => $w[0] ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $exists ) {
			continue;
		}
		$id = wp_insert_post( array( 'post_type' => 'ay_work', 'post_status' => 'publish', 'post_title' => $w[1], 'post_name' => $w[0], 'post_content' => $w[8], 'menu_order' => $i + 1 ) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_ay_sample', $w[0] );
		wp_set_object_terms( $id, array( $cols[ $w[2] ] ), 'ay_collection' );
		wp_set_object_terms( $id, array( $typs[ $w[3] ] ), 'ay_type' );
		update_post_meta( $id, '_ay_year', $w[4] );
		update_post_meta( $id, '_ay_materials', $w[5] );
		update_post_meta( $id, '_ay_gemstones', $w[6] );
		update_post_meta( $id, '_ay_technique', 'Digital rendering' );
		update_post_meta( $id, '_ay_specs', wp_slash( wp_json_encode( $w[7] ) ) );
		update_post_meta( $id, '_ay_featured', $w[9] ? '1' : '' );
		if ( ! empty( $imgs[ $w[0] ] ) ) {
			set_post_thumbnail( $id, $imgs[ $w[0] ] );
		}
		$gal = array();
		foreach ( ay_sample_works() as $o ) {
			if ( $o[2] === $w[2] && $o[0] !== $w[0] && ! empty( $imgs[ $o[0] ] ) && count( $gal ) < 2 ) {
				$gal[] = $imgs[ $o[0] ];
			}
		}
		update_post_meta( $id, '_ay_gallery', implode( ',', $gal ) );
		$done++;
	}
	return $done;
}

/** Remove every sample work, picture and (now empty) sample category. */
function ay_remove_samples() {
	$n = 0;
	foreach ( get_posts( array( 'post_type' => 'ay_work', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_ay_sample' ) ) as $id ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		wp_delete_post( $id, true );
		$n++;
	}
	foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_ay_sample' ) ) as $id ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		$rel = (string) get_post_meta( $id, '_ay_asset', true );
		if ( 0 === strpos( $rel, 'img/works/' ) ) {
			wp_delete_attachment( $id, true );
		}
	}
	foreach ( array( 'ay_collection', 'ay_type' ) as $tax ) {
		foreach ( get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'meta_key' => '_ay_sample' ) ) as $t ) { // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( ! is_wp_error( $t ) && 0 === (int) $t->count ) {
				wp_delete_term( $t->term_id, $tax );
			}
		}
	}
	return $n;
}

function ay_sample_count() {
	return count( get_posts( array( 'post_type' => 'ay_work', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_ay_sample' ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
}
