<?php
/**
 * Builder body.
 *
 * Rendered in place of the theme's single template when a Single template's
 * conditions match this request — see Builder\Body, which is what put this
 * file in front of WordPress.
 *
 * The header and the footer are the theme's own, so a page built here keeps
 * the site's chrome, its landmarks and its design tokens; only what the theme
 * would have rendered between them comes from the builder.
 *
 * The loop still runs, and the builder content is echoed inside it: the
 * queried post has to be the global post for the widgets in the template to
 * read the product they are placed on.
 *
 * @package PixelomaticCore
 */

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Builder\Body;
use PixelomaticCore\Builder\Renderer;

$pixelomatic_core_template = Body::resolved();

get_header();

while ( have_posts() ) :
	the_post();

	// Elementor has already escaped its own output, and running it through
	// wp_kses here would strip the very markup an editor built.
	echo Renderer::content( $pixelomatic_core_template ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor-generated builder content.

endwhile;

get_footer();
