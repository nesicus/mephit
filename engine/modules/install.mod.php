<?php
	/* install.mod.php
	 * date: 2011-10-21
	 * description: module for installing the CMS
	*/
	
	if (!defined('IN_RECOVERY')) die();

	// the software is already installed
	if (defined('INSTALLED')) $action = "installed";
	
	if ("GET" == $_SERVER['REQUEST_METHOD']) {
			$contentTemplate = new h2o(TEMPLATES_DIR . 'setup.html');
			$page = new h2o(TEMPLATES_DIR . 'index.html');
			echo $page->render(array(
				'content' => $contentTemplate->render(array('timezones' => timezone_identifiers_list(),
														'error' => $error,
														'status' => $status)))
				);
	}
?>
