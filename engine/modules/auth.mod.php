<?php
	/* auth.mod.php
	 * date: 2011-02-25
	 * description: handles user authentication (logging in and logging out)
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

		$contentTemplate = new h2o(TEMPLATES_DIR . 'auth/login.html');
	
		$page = new h2o(TEMPLATES_DIR . 'index.html');
	
		echo $page->render(array(
			'content' => $contentTemplate->render(array('auth' => $auth, 'user' => $_SESSION['user'])),
			'user' => $_SESSION['user']
			)
		);	
	}
?>
