<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			if (substr( $_SERVER['REQUEST_URI'], 0, 6 ) === '/tags/') {
				$trim = str_replace('/tags', '', $_SERVER['REQUEST_URI']);
				$replace = str_replace('/', '', $trim);
				$tags = str_replace('+', ', ', $replace);
			}
			?>

			<section class="no-results not-found">

				<header class="page-header">
					<h1 class="page-title"><?php _e( "Aucun event trouvé avec les tags suivants :(", 'twentyfifteen' ); ?></h1>
					<div class="page-title notfoundtags"><?php echo $tags;?></div>
				</header><!-- .page-header -->

			</section><!-- .no-results -->

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
