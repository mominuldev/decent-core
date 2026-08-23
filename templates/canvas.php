<?php
/**
 * Fallback canvas.
 *
 * Used only when neither the theme nor Elementor supplies one. It is the
 * smallest document that can host a builder template: no header, no footer, no
 * container — just the two hooks every stylesheet and script on the page is
 * registered against.
 *
 * @package PixelomaticCore
 */

defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'is-canvas' ); ?>>
<?php wp_body_open(); ?>

<main id="content">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>

<?php wp_footer(); ?>
</body>
</html>
