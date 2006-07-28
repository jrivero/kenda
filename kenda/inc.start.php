<?php
	session_start();

	include_once './kenda/inc.config.php';
	include_once './config.php';

	if (version_compare(phpversion(), $kenda['php_version'], '<')) {
		trigger_error('The PHP version installed (' . phpversion() . ') is older than need (v' . $kenda['php_version'] . ')', E_USER_ERROR);
	}

	include_once './kenda/inc.lib.php';

	$kenda_start = getmicrotime();

	include_once './kenda/inc.define.php';
	include_once './kenda/inc.dispatcher.php';
	include_once './kenda/inc.class.php';
	include_once './kenda/inc.db.php';
 	include_once './kenda/inc.view.php';
	include_once './kenda/inc.assign.php';

	set_error_handler("kenda_rpt_php");
?>