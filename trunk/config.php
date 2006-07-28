<?php
if (!defined('IN_APP')) {
	$dbo = array(
		'driver'   => '', // mysql, mssql
		'server'   => '',
		'login'    => '',
		'password' => '',
		'database' => ''
	);

	$app	= array (
	  'titulo'			=> 'Aplicación de ejemplo',
	  'descripcion'	=> 'Descripción de la aplicación de ejemplo',
	  'creditos'    => '2006 (tiS) Servinet',
		'uri'					=> 'localhost',
		'wrapper'			=> 'layout-default.tpl',
		'module'			=> 'home',
		'method'			=> 'index',
		'debug'				=> 'core,error,debug', // core / error / sql / php / debug / log
	);

 define('IN_APP',$app['titulo']);
}
?>