<?php
/*
 *      site.lib.php 2011-10-27
 *      
 *      Copyright 2011 Daryl Fain <daryl@99years.com>
 *      
 *      Redistribution and use in source and binary forms, with or without
 *      modification, are permitted provided that the following conditions are
 *      met:
 *      
 *      * Redistributions of source code must retain the above copyright
 *        notice, this list of conditions and the following disclaimer.
 *      * Redistributions in binary form must reproduce the above
 *        copyright notice, this list of conditions and the following disclaimer
 *        in the documentation and/or other materials provided with the
 *        distribution.
 *      * Neither the name of the  nor the names of its
 *        contributors may be used to endorse or promote products derived from
 *        this software without specific prior written permission.
 *      
 *      THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 *      "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 *      LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 *      A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 *      OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 *      SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 *      LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 *      DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 *      THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *      (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *      OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *      
 *      
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
	
	function emailAdmin($envelope) {
		if (!is_array($envelope)) return FALSE;
		$headers = sprintf("From: %s", SITE_EMAIL);
		if (mail($envelope['dest'], $envelope['subject'], $envelope['message'], $headers))
			return TRUE;
		return FALSE;
	}
	
	function renderPage($template) {

		
		$content = '';
		foreach($template as $key => $value) {
			// try to mitigate directory traversal and remote inclusion
			if (preg_match('/[^a-zA-Z0-9\.]/', $template[$key]['name'])) die();
			
			$_file = TEMPLATES_DIR . str_replace('.', '/', $template[$key]['name']) . '.html';
			
			// render the content template
			$page = new h2o($_file);
			$content .= $page->render($template[$key]['vars']);
			unset($page);
		}
		
		// place templates into the context of the main page
		$index = new h2o(TEMPLATES_DIR . 'index.html');
		echo $index->render(array(
				'content' => $content,
				'user' => $_SESSION['user'],
			));
			
		exit;
	}
?>
