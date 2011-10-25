<?php
	/* dynamic.conf.php
	 * date: 2011-10-21
	 * description: dynamic configuration file	
	*/
	
	/* user levels and privileges */

	/*   this is a bitmask for delegation of user privileges to custom levels.
		sum the numbers corresponding to the value for each privilege you want to 
	assign your level.                              */

	/*
		1: able to post replies
		2: able to make new posts
		4: able to upgrade level 1 users, able to delete posts
	*/

	define('CAN_MAKE_COMMENTS', 1);
	define('CAN_MAKE_NEW', 2);
	define('CAN_DELETE', 4);
	define('CAN_UPGRADE', 4);

	// some sensible defaults

	$USER_LEVELS_ARRAY = array( 
		array('name' => 'reserved', 'privileges' => 0), // reserved or anonymous -- see below
		array('name' => 'basic', 'privileges' => 1),   // able to post replies
		array('name' => 'medium', 'privileges' => 3),  // able to post replies and make new posts
		array('name' => 'admin', 'privileges' => 7)  // able to post replies, make new posts, upgrade users, and delete posts
	);

	// the default user level for new accounts
	define('DEFAULT_USERLEVEL', 1);
	
	// set the following to "1" if you wish to have an anonymous user level
	// note: the anonymous user will ALWAYS have user id "0"

	define('ALLOW_ANONYMOUS', 0);

	// set this to change the anonymous user's privilege level, as above

	define('ANON_PRIVS', 0);

	/* content */

	// format to display time/date strings
	define('DATE_TIME_FORMAT', 'F d, Y @g:iA');
	
	// some sensible defaults
	$contentTypes = array(
		array('name' => 'news')
	);
	
	// maximum length for post heading (set to FALSE to disable)
	define('MAX_TITLE_LEN', 100);
	
	// maximum length for post content (set to FALSE to disable)
	define('MAX_BODY_LEN', FALSE);
	
	/* main page */
	
	// number of posts to display
	define('MAX_NEWS_POSTS', 5);
	
	define('COMMENT_SPILL', 0);
	define('CHRONO_ORDER', 0);
?>
