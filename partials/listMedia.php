<ul class="fortpolio-media"><?php
	foreach($media as $oMedium) {
		$sMediumId = $oMedium->id;
		$oMediumData = get_post($sMediumId);
		$bIsWp = isset($oMediumData);
		$sTitle = $bIsWp?$oMediumData->post_title:$oMedium->title;
		$sContent = $bIsWp?$oMediumData->post_content:'';
		?><li class="item_<?php echo $sMediumId; ?>">
			<div class="item-content">
				<h4><?php echo $sTitle; ?></h4><?php
				echo $sContent;
			?></div><?php
			echo $this->getMediumHtml($oMedium);
		?></li><?php
	}
?></ul>