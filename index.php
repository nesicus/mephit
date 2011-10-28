<?php
/*
 *      index.php 2011-10-28
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


	/* here we define a global constant which all modules and referenced files will check to ensure they are being
	 called properly. this prevents any tampering with the site backend by malicious users who may try to call PHP
	 files individually, outside of the context of the "engine." */
	define('IN_RECOVERY', TRUE);

	// absolute path for bootstrapping
	define('BASE_DIR', dirname(__FILE__));
	
	// relative paths
	define('PREFIX', BASE_DIR . '/engine/');
	define('SYSCONF_DIR', PREFIX . 'etc/');
	define('INCLUDE_DIR', PREFIX . 'include/');
	define('MODULES_DIR', PREFIX . 'modules/');
	define('TEMPLATES_DIR', PREFIX . 'templates/');
	
	// the path to h2o.php
	define('H2OPATH', PREFIX . 'h2o/');
	
	// a site configuration file must be present before we can do anything
	if (file_exists(SYSCONF_DIR . 'main.conf.php'))
		require_once(SYSCONF_DIR . 'main.conf.php');
	
	// set default time zone
	ini_set('date.timezone', DEFAULT_TZ);
	
	// for security purposes we'd prefer not to divulge configuration details and runtime errors
	if (DEBUG == TRUE) {
		ini_set('display_errors', TRUE);
		error_reporting(E_ALL);
	} else {
		ini_set('display_errors', FALSE);
	}
	
	// these checks are to ensure potential attackers aren't poisoning critical globals before we even get started
	if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBAL'])) die();
	if (isset($_SESSION) && !is_array($_SESSION)) die();
	
	// this check accounts for varying server conditions and ensures the security of global variables assigned in our
	// application
	if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on') {
		$_safe = array('_REQUEST', '_GET', '_POST', '_COOKIE', '_SERVER', '_SESSION', '_ENV', '_FILES', 'GLOBALS');

		// here we make sure that critical global arrays have not been poisoned by malicious web input
		if (!isset($_SESSION) || !is_array($_SESSION)) $_SESSION = array();
			$_test = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_SESSION, $_ENV, $_FILES);
			
			// ensure our test arrays aren't unset by the following code block
			unset($_test['_test']);
			unset($_test['_safe']);
			
			// unset registered globals
			while (list($var,) = @each($_test)) {
				// globals are poisoned--die
				if (in_array($var, $_safe)) die();
				unset($$var);
			}

			unset($_safe);
			unset($_test);
	}
	
	// hopefully this system doesn't have magic quotes enabled, but we don't live in a perfect world
	if (function_exists('get_magic_quotes_gpc') && @get_magic_quotes_gpc()) {
		function stripslashes_deep($value) {
			$value = is_array($value) ?
			array_map('stripslashes_deep', $value) :
			stripslashes($value);
			return $value;
		}
		stripslashes_deep($_GET);
		/*foreach($_GET as $key => $value) $_GET[$key] = stripslashes($value);
		foreach($_POST as $key => $value) $_POST[$key] = stripslashes($value);
		foreach($_COOKIE as $key => $value) $_COOKIE[$key] = stripslashes($value);
		foreach($_ENV as $key => $value) $_ENV[$key] = stripslashes($value);
		foreach($_COOKIE as $key => $value) $_COOKIE[$key] = stripslashes($value);
		foreach($_REQUEST as $key => $value) $_REQUEST[$key] = stripslashes($value);
		*/
	}
	
	// bootstrap all the core functionality of the site to be used by modules
	require_once(INCLUDE_DIR . 'site.lib.php');
	require_once(INCLUDE_DIR . 'crypto.lib.php');
	require_once(INCLUDE_DIR . 'sql.lib.php');
	require_once(INCLUDE_DIR . 'sessions.lib.php');
	require_once(INCLUDE_DIR . 'user.lib.php');
	require_once(INCLUDE_DIR . 'content.lib.php');
	
	// modules need the h2o template library
	require_once(H2OPATH . 'h2o.php');
	
	// begin the installation procedure if necessary
	if (!defined('INSTALLED')) {
		$_SESSION = array('user' => 
							array('id' => 1,
									'name' => 'admin',
									'level' => 1)
						);
		include MODULES_DIR . 'install.mod.php';
		exit;
	}
	
	// global SQL instance to be used by modules
	$_sql = new sqlClass();
	$_sql->connect($sqlInfo);

	// we need to be connected to a database
	if (TRUE != $_sql->connected) die();
	
	// initialize a session for modules
	$_session = new sessionsClass();
	session_start();
	
	// initialize a user instance for modules
	$_user = new userClass();
	
	// populate session if necessary
	if (!isset($_SESSION['user']['id'])) $_SESSION['user'] = $_user->get();
	
	// assign anonymous privileges
	if (defined('ALLOW_ANONYMOUS') && @ALLOW_ANONYMOUS == 1) {
		$USER_LEVELS_ARRAY[0]['name'] = "anonymous";
		$USER_LEVELS_ARRAY[0]['privileges'] = defined('ANON_PRIVS') ? @ANON_PRIVS : 0;
	}
	
	/* this is basically where stuff starts happening. any content to be rendered by the site will be loaded from 
	 modules, which are all accessed through the engine. we want to make sure to prevent any kind of directory
	 traversal attacks, buffer overflows, or what have you by limiting the characters allowed for module names. if the
	 site is accessed without referencing a module, or a module name is determined to be illegal or nonexistent, the
	 default module page will be loaded, as determined by $module */
	$module = 'main';
	
	if (isset($_GET['module']) && strlen(@$_GET['module']) <= 15) {
		$module = preg_match('/[^a-zA-Z0-9]/', @$_GET['module']) ? 'main' : @$_GET['module'];
	}
	
	$module = MODULES_DIR . $module . '.mod.php';
	
	if (file_exists($module)) {
		include $module;
		exit;
	} else {
		include MODULES_DIR . 'main.mod.php';
		exit;
	}
?>
