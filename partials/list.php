<ul class="fortpolio-list"><?php
	foreach ($posts as $oPost) {
		?><li class="fortpolio-excerpt"><?php
			//echo $this->getFortpolioItem($oPost,$thumb,$excerpt,$media);
			$this->template('item.php',array(
				'this'=>$this
				,'post'=>$oPost
				,'thumb'=>$thumb
				,'excerpt'=>$excerpt
				,'media'=>$media
			));
		?></li><?php
	}
?></ul>