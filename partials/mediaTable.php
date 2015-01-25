<table cellspacing="0" class="wp-list-table widefat" id="fortpolio-media-table">
	<thead><tr>
		<th><?php _e('Type','fortpolio'); ?></th>
		<th><?php _e('Title','fortpolio'); ?></th>
		<th><?php _e('Medium','fortpolio'); ?></th>
		<th></th>
	</tr></thead>
	<tbody>
	<?php
		global $post;
		$sTbody = '';
		foreach($json as $oMedium) {
			/* todo: fix like post :: add_filter('redirect_post_location',...
			 * hack media.php ln 39 : wp_redirect($location);
			 * to : wp_redirect(isset($_GET['redir'])?str_replace('&amp;','&',$_GET['redir']):$location);
			 */
			if ($oMedium->type=='vimeo') {
//							$sUri = 'http://vimeo.com/api/v2/video/'.$oMedium->id.'.json';
//							$aVimeoJson = array_pop(json_decode(curl_get($sUri)));
				//
				$sTbody .= sprintf(
					$tr
					,$oMedium->id
					,$oMedium->type
					,'javascript:function(){}'
					,$oMedium->title
					,$this->getMediumHtml($oMedium)//'<img src="'.$oMedium->thumb.'" />'
				);
			} else {
				$sEditPostLink = get_edit_post_link($oMedium->id).'&redir='.urlencode(get_edit_post_link($post->ID));
				//dump($sEditPostLink);
				$sTbody .= sprintf(
					$tr
					,$oMedium->id
					,$oMedium->type
					,$sEditPostLink
					,get_the_title($oMedium->id)
					,$this->getMediumHtml($oMedium)
				);
				//wp_get_attachment_url($oMedium->id)
				//wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
				//wp_get_attachment_metadata( $post_id, $unfiltered );
				//wp_get_attachment_url( $id );
				//wp_get_attachment_thumb_file( $post_id );
				//wp_get_attachment_thumb_url( $post_id );
				//dump(wp_get_attachment_metadata($oMedium->id)); // use this for image
				//dump(wp_get_attachment_url($oMedium->id)); // use this for non-image
				//dump(wp_get_attachment_thumb_url($oMedium->id));
			}
		}
		echo $sTbody;
	?>
	</tbody>
</table>