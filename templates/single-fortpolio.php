<?php
get_header();
?><div id="primary" class="content-area">
	<main id="main" class="site-main" role="main"><?php
	while ( have_posts() ) : the_post();

		global $FPL;
		echo $FPL->getFortpolioContent($post);

		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

		the_post_navigation( array(
			'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'twentyfifteen' ) . '</span> ' .
				'<span class="screen-reader-text">' . __( 'Next post:', 'twentyfifteen' ) . '</span> ' .
				'<span class="post-title">%title</span>',
			'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'twentyfifteen' ) . '</span> ' .
				'<span class="screen-reader-text">' . __( 'Previous post:', 'twentyfifteen' ) . '</span> ' .
				'<span class="post-title">%title</span>',
		) );

	endwhile;
	?></main>
</div><?php
get_footer();
