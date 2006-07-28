<?
	vendor('smarty/smarty.class');

	$tpl = & new Smarty;

	$tpl->template_dir 	= array('./thm/view/');
	$tpl->use_sub_dirs 	= true;
	$tpl->compile_id 		= $reqModule;
	$tpl->compile_dir 	= './var/tpl_c/';
	$tpl->cache_dir 		= './var/cache/';

	kenda_rpt('Motor de plantillas cargado');
?>