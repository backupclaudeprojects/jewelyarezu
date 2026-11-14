<?php
/**
 * Default template (pages not built with Elementor, search, blog).
 *
 * @package Arezoo
 */

get_header();
echo '<div class="ay-plain">';
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		echo '<article>';
		the_title( is_singular() ? '<h1 class="entry-title">' : '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', is_singular() ? '</h1>' : '</a></h2>' );
		if ( is_singular() ) {
			the_content();
		} else {
			the_excerpt();
		}
		echo '</article>';
	}
	the_posts_pagination();
} else {
	echo '<h1 class="entry-title">Nothing here yet</h1>';
}
echo '</div>';
get_footer();
