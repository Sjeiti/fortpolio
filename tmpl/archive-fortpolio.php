<?php
/* Template Name: Fortpolio archive */
get_header();
?><section id="primary" class="content-area">
	<main id="main" class="site-main" role="main"><?php
		if ( have_posts() ) :
			?><header class="page-header"><?php
				the_archive_title( '<h1 class="page-title">', '</h1>' );
				the_archive_description( '<div class="taxonomy-description">', '</div>' );
			?></header><?php
			while ( have_posts() ) : the_post();
				echo $wp_fortpolio->getFortpolioContent($post);
			endwhile;

			// Previous/next page navigation.
			the_posts_pagination( array(
				'prev_text'          => __( 'Previous page', 'twentyfifteen' ),
				'next_text'          => __( 'Next page', 'twentyfifteen' ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>',
			) );

		else :
			get_template_part( 'content', 'none' );
		endif;
	?></main>
</section><?php
get_footer();