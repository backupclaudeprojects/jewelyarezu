<?php
/**
 * Plain header, used until the Elementor header exists.
 *
 * @package Arezoo
 */
?>
<header class="ay-plain-head">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
	<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'depth' => 1, 'fallback_cb' => '__return_empty_string' ) ); ?>
</header>
