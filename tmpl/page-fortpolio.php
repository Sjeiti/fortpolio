<?php
/* Template Name: Fortpolio archive */
get_header();
?><div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<article class="page type-page status-publish hentry">
			<header class="entry-header"><h1 class="entry-title"><?php the_title() ?></h1></header>
			<section id="container" class="portfolio entry-content">
			<?php
				echo $wp_fortpolio->fortpolio(array(
//					'media' => true
				));
			?>
			</section>
		</article>
	</main>
</div><?php
get_footer();