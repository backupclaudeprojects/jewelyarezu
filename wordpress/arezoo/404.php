<?php
/**
 * Not found.
 *
 * @package Arezoo
 */

get_header();
if ( ! arezoo_part( '404' ) ) {
	echo '<div class="ay-plain"><h1 class="entry-title">This page is not in the collection</h1><p><a href="' . esc_url( home_url( '/' ) ) . '">Back to the home page</a></p></div>';
}
get_footer();
