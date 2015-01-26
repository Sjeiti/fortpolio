<ul class="fortpolio-list"><?php
	foreach ($posts as $oPost) {
		?><li class="fortpolio-excerpt"><?php
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