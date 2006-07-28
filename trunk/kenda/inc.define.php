<?
	define('KENDA_VERSION', 	$kenda['version']);

	define('HOME_URI', 				$app['uri']);
	define('VAR_PATH', 				'./var/');
	define('MODULE_PATH', 		'./mod/');
	define('MODULE_DEFAULT', 	$app['module']);
	define('METHOD_DEFAULT',	$app['method']);
	define('PATH_LOCAL', 			str_replace ('/index.php', '', $_SERVER["SCRIPT_FILENAME"]));

	define ('FATAL',E_USER_ERROR);
	define ('ERROR',E_USER_WARNING);
	define ('WARNING',E_USER_NOTICE);
	
	define('NL',"\n");
?>