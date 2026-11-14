<?php
/**
 * Type archive.
 *
 * @package Arezoo
 */

get_header();
if ( ! arezoo_part( 'archive' ) ) {
	echo '<div class="ay-plain"><h1 class="entry-title">' . esc_html( single_term_title( '', false ) ) . '</h1>';
	while ( have_posts() ) {
		the_post();
		the_title( '<h2><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
	}
	echo '</div>';
}
get_footer();
