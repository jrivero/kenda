<?
  $tpl->assign('KENDA_VERSION',KENDA_VERSION);

	$tpl->assign('THEMEDIR','thm/');
	$tpl->assign('THEMECSS','thm/load.css');
	$tpl->assign('THEMEJS','thm/func.js');
	
	$tpl->assign('CONTENT','');

	$tpl->assign('MOD',ucfirst(strtolower($reqModule)));
	$tpl->assign('MOD_METHOD',ucfirst(strtolower($reqMethod)));
	$tpl->assign('MOD_DEFAULT',ucfirst(strtolower(MODULE_DEFAULT)));

	$tpl->assign('MODULE_LIST', FoldersFromDir(MODULE_PATH));
?>