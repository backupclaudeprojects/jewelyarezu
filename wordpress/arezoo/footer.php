<?php
/**
 * @package Arezoo
 */
?>
</main>
<?php
if ( ! arezoo_part( 'footer' ) ) {
	get_template_part( 'template-parts/footer', 'fallback' );
}
wp_footer();
?>
</body>
</html>
