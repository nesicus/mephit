<?php
	/* site.lib.php
	 * date: 2011-10-25
	 * description: general site functions
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
