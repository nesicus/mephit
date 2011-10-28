<?php
/*
 *      main.conf.php 2011-10-28
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
	
	/* security */
	
	// determine whether to report verbose error messages, for development
	define('DEBUG', FALSE);
	
	// number of iterations to use for password stretching, expressed as base 2 logarithm; must be between 4 and 31
	define('BCRYPT_ROUNDS', 8);
	
	// include the dynamic configuration options (these can be set at runtime)
	include 'dynamic.conf.php';
?>
