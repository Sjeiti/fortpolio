<?php
// dump
if (!function_exists("dump")) {
	function dump($s) {
		echo "<pre>";
		print_r($s);
		echo "</pre>";
	}
}

// trace
if (!function_exists("trace")) {
	function trace($s) {
		$oFile = fopen("log.txt", "a");
		$sDump  = $s."\n";
		fputs ($oFile, $sDump );
		fclose($oFile);
	}
}

// isPage
if (!function_exists("isPage")) {
	function isPage($pageName) {
		return (isset($_GET['post'])&&get_post_type($_GET['post'])==$pageName)
			|| (isset($_GET['post_type'])&&$_GET['post_type']==$pageName);
	}
}

// isSettings
if (!function_exists("isSettings")) {
	function isSettings($pageName) {
		$aSlashed = explode('/',$_SERVER['PHP_SELF']);
		return array_pop($aSlashed)=='options-general.php'&&isset($_GET['page'])&&$_GET['page']==$pageName;
	}
}