<?php
/**
 * @package Arezoo
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="ay-skip" href="#ay-main">Skip to content</a>
<?php
if ( ! arezoo_part( 'header' ) ) {
	get_template_part( 'template-parts/header', 'fallback' );
}
?>
<main id="ay-main" class="ay-main" tabindex="-1">
