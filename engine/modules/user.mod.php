<?php
/*
 *      user.mod.php 2011-10-27
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
	
	// default action
	$action = 'view';
	if (isset($_GET['action'])) $action = $_GET['action'];
	
	if ("view" == $action) {
		// check for invalid user
		if (!isset($_GET['id']) || !$_user->get(@$_GET['id'])) {
			// redirect them to the main page
			doRedirect('', TRUE);
		} else {
			// obtain profile data
			$user = $_user->get($_GET['id']);
		}	
		
		// render UNIX timestamp; offset for GMT later. this is just a test.
		$user['created'] = date("h:i A, d F Y", $user['created']);
		
		$user['profile' ] = array(
			'level' => $USER_LEVELS_ARRAY[$user['level']]['name'],
			'favorite drink' => 'cat urine',
			'favorite club' => "don's disco and 24 hour diner",
			'age' => 30,
			'number of cats' => 30,
			'description' => 'this is a stupid description of a stupid person.',
		);
		
		$templates[] = array('name' => 'user.profile',
							 'vars' => array('user' => $user)
						);
		renderPage($templates);
		
	} else if ("new" == $action) {
		$error = FALSE;
		$status = '';
		
		// is this already a logged in user?
		if (!@$_SESSION['user']['id']) {
			if ("POST" == $_SERVER['REQUEST_METHOD']) {
				if (!@empty($_POST['username']) && !@empty($_POST['password'])) {
					if ($_user->newUser($_POST['username'], $_POST['password'], @$_POST['email'], @$_POST['timezone'])) {
						// try to log in as new user
						if ($uid = $_user->login($_POST['username'], $_POST['password'])) {
							// regenerate session ID upon authentication
							$sid = $_session->genID();
							
							// populate session data
							$_SESSION['user'] = $_user->get($uid);
							
							// update cookie with the new session ID
							$cookie = session_get_cookie_params();
							setcookie(COOKIE_NAME, $sid, $cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure']);
							
							// redirect the user to their profile
							doRedirect("?module=user&id=" . htmlentities($uid), TRUE);
						} else {
							echo "error was logging in.\n";
							$status = $_user->error;
						}
					} else {
						echo "error was creating account.\n";
						$status = $_user->error;
					}
				} else {
					$status = "You must enter a username and password.";
				}
			}
		} else {
			$error = TRUE;
			$status = "You are already logged in.";
		}
		
		$templates[] = array('name' => 'user.new',
								'vars' => array('timezones' => timezone_identifiers_list(),
											'error' => $error,
											'status' => $status)
							);
							
		renderPage($templates);
	}
?>
