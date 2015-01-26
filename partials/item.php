<?php

$sReturn  = '';
$sPermalink = '<a title="'.__('Read more','fortpolio').'" class="thumb" href="'.get_permalink($post->ID).'">%s</a>';
sprintf( $sPermalink, preg_replace('/\s(width|height)=\"[0-9]*\"/','',get_the_post_thumbnail($post->ID,'thumbnail')) );
if ($thumb) {
	$sReturn .= sprintf( $sPermalink, preg_replace('/\s(width|height)=\"[0-9]*\"/','',get_the_post_thumbnail($post->ID,'thumbnail')) );
}
$sReturn .= '<a title="'.__('Edit item','fortpolio').'" class="post-edit-link" href="'.get_edit_post_link($post->ID).'">'.__('Edit item','fortpolio').'</a>';
$sReturn .= '<h3>'.sprintf( $sPermalink, apply_filters('the_title',$post->post_title) ).'</h3>';
if ($excerpt)	$sReturn .= '<p>'.apply_filters('the_content',$post->post_excerpt).'</p>';
else			$sReturn .= '<p>'.apply_filters('the_content',$post->post_content).'</p>';
if ($media)		$sReturn .= sprintf( $sPermalink, $this->getMediaHtml($post->ID) );
$sReturn .= '<a title="'.__('Read more','fortpolio').'" class="read-more" href="'.get_permalink($post->ID).'">'.__('Read more','fortpolio').'</a>';
echo $sReturn;