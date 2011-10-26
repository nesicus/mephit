<?php
	/*	main.conf.php
	 *	date: 2011-10-21
	 *	description: main configuration file
	*/
	
	if (!defined('IN_RECOVERY')) die();
	
	// you must uncomment the line below before proceeding
	//define('INSTALLED', TRUE);
	
	// the URL to your website
	define('WEBSITE_URL', 'http://www.example.com/');
	
	// your domain name
	define('DOMAIN_NAME', 'example.com');
	
	// the e-mail address notices sent from this software should use
	define('SITE_EMAIL', 'example@example.com');
	
	// SQL connection information
	$sqlInfo = array("hostname" => "localhost",
						"username" => "username",
						"password" => "password",
						"database" => "database"
					);

	// store session IDs here
	define('COOKIE_NAME', 'mephit_sess');
	
	// the domain to set cookies for
	define('COOKIE_DOMAIN', DOMAIN_NAME);
	
	// the path to set cookies for
	define('COOKIE_PATH', '/');
	define('COOKIE_LIFETIME', '31536000');
	define('COOKIE_SECURE', FALSE);
	define('COOKIE_HTTPONLY', TRUE);
	
	/* caching */
	
	// enable caching
	define('USE_CACHING', FALSE);
	
	// directory for storing cache file. this should be writable by the webserver.
	define('CACHE_DIR', PREFIX . 'cache/');
	
	// cache expiration time in seconds
	define('CACHE_EXPIRE', 300);
	
	/* timezones */
	
	// the default timezone to use for displaying times on the site
	// set this to ?? to attempt determining an appropriate timezone through geolocation
	define('DEFAULT_TZ', 'UTC');
	
	// determine whether to report verbose error messages, for development
	define('DEBUG', FALSE);
	
	// include the dynamic configuration options (these can be set at runtime)
	include 'dynamic.conf.php';
?>
