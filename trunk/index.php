<?php
	// VERSION: v0.87
	
	require './kenda/inc.start.php';

	if (isset($reqModule)) {
		$c_file	= MODULE_PATH . $reqModule . '/' . $reqModule . '.c.php';
		$m_file	= MODULE_PATH . $reqModule . '/' . $reqModule . '.m.php';

		if (file_exists ($c_file) && file_exists ($m_file)) {
			array_push($tpl->template_dir, './mod/' . $reqModule . '/view/');
			include ($m_file); kenda_rpt('Funciones del controlador "' . $reqModule . '" cargados');
			include ($c_file); kenda_rpt('Controlador "' . $reqModule . '" cargado.');
			
			kenda_rpt('Accion "' . $reqMethod . '" solicitada');
		} else {
			kenda_rpt('El controlador "' . $reqModule . '" no se pudo cargar.','error');
		}
	}

	require './kenda/inc.end.php';
?>