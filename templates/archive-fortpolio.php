<?php
/* Template Name: Fortpolio archive */

get_header();

echo '<section id="container" class="portfolio">';

//	if ( have_posts() ) :
//		while ( have_posts() ) : the_post();
//			//get_template_part( 'content', get_post_format() );
//			echo '<h1 class="entry-header">'.get_the_title().'</h1>';
//			the_content();
//			echo '<footer class="entry-meta">';
//			edit_post_link(__("Edit"), '');
//			echo '</footer>';
//		endwhile;
//	endif;

	$iTagId = getTagId('hide');

	$iPage = get_query_var('paged')?get_query_var('paged'):1;
	//$iPage = get_query_var('page')?get_query_var('page'):1;

	$loop = new WP_Query( array(
		 'post_type'		=> 'fortpolio'
		,'posts_per_page'	=> 10
		,'paged'			=> $iPage
		,'tag__not_in'		=> array($iTagId),
	));
	$iPageMax = $loop->max_num_pages;

	$i = 0;
	$iNumPosts = count($loop->posts);

//dump($iNumPosts);
//dump($loop);
	while ( $loop->have_posts() ) : $loop->the_post();
        $iPostId = get_the_ID();
        echo '<div class="entry">';
//            $oPortfolio->drawVideo($iPostId);
            echo '<h2 class="entry-title">'.get_the_title().'</h2><br/>';
            echo '<div class="entry-content">'.get_the_content().'</div>';

            $sExtraMeta = '';
            $aLines = explode("\n",get_post_meta($iPostId,'extraMeta', true ));
            foreach($aLines as $sLine){
                if ($sLine!='') {
                    $aLine = explode(":",$sLine);
                    $sExtraMeta .= '<div><span>'.implode('</span><span>',$aLine).'</span></div>';
                }
            }
            echo '<div class="entry-extra-meta">'.$sExtraMeta.'</div><br/>';
        echo '</div>';
	endwhile;

//	wp_pagenavi(array(
//		'query' => $loop
//		,'before'=>'<nav>'
//		,'after'=>'</nav>'
//	));
//	wp_nav_menu();
//	wp_page_menu();

	$pagenum_link = html_entity_decode(get_pagenum_link());

	$format = $GLOBALS['wp_rewrite']->using_index_permalinks()&&!strpos($pagenum_link,'index.php')?'index.php/':'';
	$format .= $GLOBALS['wp_rewrite']->using_permalinks()?user_trailingslashit('page/%#%','paged'):'?paged=%#%';

	$paged = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;

	$query_args   = array();

	$links = paginate_links( array(
		'base'     => $pagenum_link,
		'format'   => $format,
		'total'    => $GLOBALS['wp_query']->max_num_pages,
		'current'  => $paged,
		'mid_size' => 1,
		'add_args' => array_map( 'urlencode', $query_args ),
		'prev_text' => __( '&larr; Previous', 'fortpolio' ),
		'next_text' => __( 'Next &rarr;', 'fortpolio' ),
//		'before_page_number' => '<li>',
//		'after_page_number' => '</li>'
	));

	echo '<nav>'.$links.'</nav>';




echo '</section>';

get_footer();

?>