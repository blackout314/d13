<?php

//========================================================================================
//
// CONFIG.INC
//
// # Author......................: Andrei Busuioc (Devman)
// # Author......................: Tobias Strunz (Fhizban)
// # Download & Updates..........: https://sourceforge.net/projects/d13/
// # Project Documentation.......: https://sourceforge.net/p/d13/wiki/Home/
// # Bugs & Suggestions..........: https://sourceforge.net/p/d13/tickets/
// # License.....................: https://creativecommons.org/licenses/by/4.0/
//
//========================================================================================

//- - - - MAIN SETTINGS
define("CONST_GAME_TITLE", 			"d13 browser game engine");				// game title
define("CONST_PREFIX", 				"d13");									// prefix for cookies, sessions etc.

//- - - - DATABASE SETTINGS
define("CONST_DB_HOST", 			"localhost");							// ex. localhost
define("CONST_DB_USER", 			"database_user");						// database user
define("CONST_DB_PASS", 			"database_password");					// database password
define("CONST_DB_NAME", 			"database_name");						// database name

//- - - - - DIRECTORY SETTINGS
define("CONST_DOMAIN", 				"yourdomain.com");						// game domain, without http, www
define("CONST_DIRECTORY", 			"/d13/");								// directory the game is located in
define("CONST_INCLUDE_PATH", 		$_SERVER['DOCUMENT_ROOT'] . CONST_DIRECTORY);
define("CONST_BASE_PATH", 			"http://www.".CONST_DOMAIN.CONST_DIRECTORY);

//- - - - - EMAIL SETTINGS
define("CONST_EMAIL", 				"support@yourdomain.com");				// reply address shown when sending email

//- - - - - OTHER SETTINGS
define("CONST_FLAG_PROFILER", 		TRUE);
define("CONST_SESSION_LIFETIME", 	2592000);
define("CONST_COOKIE_LIFETIME", 	2592000);

//- - - - - DEFAULT TEMPLATE SETTINGS
define("CONST_DEFAULT_COLOR", 		"red");
define("CONST_DEFAULT_TEMPLATE", 	"default");
define("CONST_DEFAULT_LOCALE", 		"en");

//=====================================================================================EOF

?>