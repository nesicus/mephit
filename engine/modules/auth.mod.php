<?php
/*
 *      auth.mod.php 2011-10-27
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
	$action = 'login';

	$status = '';
	$username = '';
	
	if (isset($_GET['action'])) $action = $_GET['action'];
	
	// logout
	if ("logout" == $action && @$_SESSION['user']['id']) {
		// log the user out
		
		// store the session ID before closing the session so we can destroy it later
		$sid = session_id();
		
		// make sure to close the session so it doesn't get rewritten after destruction
		session_write_close();
		
		// destroy the session
		$_session->destroy($sid);
		
		$cookie = session_get_cookie_params();
		setcookie(COOKIE_NAME, FALSE, $cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'],
			$cookie['httponly']);
		
		doRedirect("?module=main", TRUE);

	// login
	} else if ("login" == $action) {
		// is this a post request? if not, just skip the logic and display our login template
		if ("POST" == $_SERVER['REQUEST_METHOD']) {
			// is the user already logged in?
			if (!@$_SESSION['user']['id']) {
				if (!@empty($_POST['username']) && !@empty($_POST['password'])) {
					// attempt to authenticate the user
					if ($uid = $_user->login($_POST['username'], $_POST['password'])) {
						$status = "Login successful";
						
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
						$status = "Login failed";
						$username = htmlentities($_POST['username']);
					}
				} else {
					$status = "You must enter a username and password.";
				}
			}
		}

		$auth = array(
			'error' => $status, // if there's an error
			'username' => $username, // set this with posted username
		);

		$templates[] = array('name' => 'auth.login',
							'vars' => array('auth' => $auth,
											'user' => $_SESSION['user'])
							);
		renderPage($templates);

	// password reset
	} else if ("reset" == $action) {
		if ("POST" == $_SERVER['REQUEST_METHOD']) {
			if (!@empty($_POST['username'])) {
				$_user->resetPassword(@$_POST['username']);
				if (NULL != $_user->error) {
					$status = $_user->error;
				} else {
					$status = "Your request was successful.";
				}
			} else {
				$status = "You must enter a username.";
			}
		}
	}
?>
