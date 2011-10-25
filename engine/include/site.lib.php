<?php
	/*
	 *      project: recovery
	 *      date: 2011-02-24
	 *      version: 0.0.1
	 *      
	 *      file: site.lib.php
	 *      date: 2011-02-24
	 *      version: 0.0.1
	 *      author: daryl
	*/
	
	if (!defined('IN_RECOVERY')) die();
	
	function doRedirect($location, $module = TRUE) {
		if ($module) {
			header("Location: " . WEBSITE_URL . $location);
			exit(TRUE);
		} else {
			// do nothing
		}
	}
	
	function getUserIP() {
		return sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
	}
?>
