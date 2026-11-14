<?php
/**
 * A single work: the Elementor template assigned in «آرزو».
 *
 * @package Arezoo
 */

get_header();
if ( ! arezoo_part( 'single' ) ) {
	echo '<div class="ay-plain">';
	while ( have_posts() ) {
		the_post();
		the_title( '<h1 class="entry-title">', '</h1>' );
		the_post_thumbnail( 'large' );
		the_content();
	}
	echo '</div>';
}
get_footer();
