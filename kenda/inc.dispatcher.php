<?
	if (empty($_REQUEST['ds'])) {
		$reqModule = (empty($_REQUEST['mod']))	? MODULE_DEFAULT : addslashes($_REQUEST['mod']);
		$reqMethod = (empty($_REQUEST['exec'])) ? METHOD_DEFAULT : addslashes($_REQUEST['exec']);
	}
	
	/* else {
		$ds_argv = explode('/',$_REQUEST['ds']);
		$ds_argc = count($ds_argv);

		list($mod,$exec) = explode ('.', $ds_argv[0]);
		$mod  = (empty($mod))  ? MODULE_DEFAULT : strtolower(clean_var($mod));
		$exec = (empty($exec)) ? 'index' : strtolower(clean_var($exec));

		$ds_params = array();
		for ($i = 1; $i < $ds_argc; $i++) {
			if (1 == $i && is_numeric($ds_argv[$i]) && 'index' == $exec) { $exec = 'ver'; }
			array_push ($ds_params, $ds_argv[$i]);
		}

		$v = 0; foreach ($app['params'] as $new_var) { $$new_var = $ds_params[$v]; $v++; }
	}*/
?>