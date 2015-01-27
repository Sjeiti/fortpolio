<video<?php (isset($data['uriPoster'])?' poster="'.$data['uriPoster'].'"':'') ?> controls="true"><?php
if (isset($data['uriWebm']))	echo '<source type="video/webm" src="'.$data['uriWebm'].'"></source>';
if (isset($data['uriMp4']))		echo '<source type="video/mp4" src="'.$data['uriMp4'].'"></source>';
if (isset($data['uriOgg']))		echo '<source type="video/ogg" src="'.$data['uriOgg'].'"></source>';
?></video>