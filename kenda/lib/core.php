<?
function vendor ($_vendor) {
	$_vendor_file = './vendor/' . $_vendor . '.php';
	if (file_exists ($_vendor_file)) include_once($_vendor_file);
	else kenda_rpt('La clase "' . $_vendor . '" de "vendor" no se pudo cargar.','error');
}

function local_load ($_file) {
  global $kenda, $app;
	$_file = './app/' . strtolower($kenda['app']) . '/' . $_file . '.php';
	if (file_exists ($_file)) require_once($_file);
}

function appVar ($_var) {
  global $app;

  if (isset($app[$_var])) { return $app[$_var]; }
  else { return false; }
}

function kendaVar ($_var) {
  global $kenda;

  if (isset($kenda[$_var])) { return $kenda[$_var]; }
  else { return false; }
}

function uses_controller ($_module) {
	$_controller_file = MODULE_PATH . $_module . '/' . $_module . '_controller.php';

	if (file_exists ($_controller_file)) {
		uses_model ($_module);
  	include_once($_controller_file);
		kenda_rpt('Controlador "' . $_module . '" cargado.');
  	array_push($tpl->template_dir, './mod/' . $_module . '/view/');
	}	else {
		kenda_rpt('Controlador "' . $_module . '" no encontrado.','error');
	}
}

function uses_model ($_module) {
	$_model_file = MODULE_PATH . $_module . '/' . $_module . '_model.php';

	if (file_exists ($_model_file)) {
		include_once($_model_file);
		kenda_rpt('Funciones del controlador "' . $_module . '" cargados');
	}	else {
		kenda_rpt('El modelo de "' . $_module . '" no se pudo cargar.','error');
	}
}

/*
* @function kenda_rpt
* @param $msg = array(mensaje,url), $type
*/

function kenda_rpt ($msg = NULL, $tipo = 'core') {
	global $app, $module, $_KENDA_RPT;

	$tipos_error = array();

  if (isset($msg)) {

    if ($tipo == 'redirect') {
      $html = '<div id="message">'. $msg[0].'. <br />Por favor, espere... (<a href="'.$_SERVER['PHP_SELF'].'?mod='.$msg[1].'">Continuar</a>)</div>';
      $html .= '<script language="JavaScript">
      var sTargetURL = "'.$_SERVER['PHP_SELF'].'?mod='.$msg[1].'";
      setTimeout( "window.location.href = sTargetURL; new Effect.Fade(document.getElementById(\'message\'));", 2*1000 );
      </script>';
      $module['msg'] = $html;
      return;
    }

    if (!is_array($app['debug'])) { $tipos_error = explode(',',$app['debug']); }
    else { $tipos_error = $app['debug']; }

		if (in_array ($tipo, $tipos_error)) {

	    if (!isset($_KENDA_RPT)) { $_KENDA_RPT = array(); }
	    if (!isset($_KENDA_RPT[$tipo])) { $_KENDA_RPT[$tipo] = array(); }

			$_KENDA_RPT[$tipo][] = (is_utf8($msg)) ? htmlentities(utf8_encode($msg)) : htmlentities($msg);

			return;
		} else {
			return;
		}
  } else {
		$return = (!empty($_KENDA_RPT)) ? $_KENDA_RPT : '';
		unset($_KENDA_RPT);

  	return $return;
	}
}

function echo_rpt($array) {
 $recursion=__FUNCTION__;
 if (empty($array)) return '';

 $out='<ul>'."\n";
 if (is_array($array)) {
	 foreach ($array as $key => $elem) {
		$out .= '<li class="'.$key.'">';
		$out .= (!is_integer($key)) ? '<h2>' . $key  . '</h2>' : '';
		$out .= (is_array($elem)) ? $recursion($elem) : str_replace("\n",'<br />',$elem);
		$out .= '</li>'."\n";
	 }
 } else {
	$out .= '<li>' . str_replace('\n','<br />', $array) . '</li>'."\n";
 }
 $out .= '</ul>'."\n";

 return $out;
}

function kenda_rpt_php ($num_err, $cadena_err, $archivo_err, $linea_err) {
  switch ($num_err) {
    case FATAL:
      kenda_rpt('FATAL: ' . $cadena_err . '. Linea ' . $linea_err . ' de ' . basename($archivo_err),'php');
    	break;

		case ERROR:
    	kenda_rpt('ERROR: ' . $cadena_err . '. Linea ' . $linea_err . ' de ' . basename($archivo_err),'php');
    	break;

		case WARNING:
		  kenda_rpt('WARNING: ' . $cadena_err . '. Linea ' . $linea_err . ' de ' . basename($archivo_err),'php');
    	break;

		default:
		  kenda_rpt($cadena_err . '. Linea ' . $linea_err . ' de ' . basename($archivo_err),'php');
    	break;
  }
}

function guardar_log ($_line, $_file = 'core'){
	$fp = fopen('./var/log/' . $_file . '.log','a');
	fwrite($fp,'['.date('Y-m-d H:i:s')."] $_line\r\n");
	fclose($fp);
}

function getmicrotime(){
 list($usec, $sec) = explode(" ",microtime());
 return ((float)$usec + (float)$sec);
}

function debug($arr) {
  $mode = '';
  $out	= '';

	if (is_array($arr) || is_object ($arr)) {
	  foreach($arr as $k => $v){
			$mode = (empty($mode) && is_numeric($k)) ? 'numeric' : 'string';

			if ($mode == 'numeric' && is_numeric($k)) {
			 $out[] = '[ ' . $k . ' ] => ' . $v;
			} else {
			 $k = (!empty($k)) ? trim($k) : '-- variable vacia --';
			 $out[] = '[ ' . $k . ' ] => ' . $v;
			}

	  }
	} else {
		$k = 'var';
		$v = (!empty($arr)) ? $arr : 'sin valor';
		$out[] = '[ ' . $k . ' ] => ' . $v;
	}

  if(is_array($out)) { kenda_rpt(implode("\n", $out),'debug'); } else {	kenda_rpt($out,'debug'); }
}

?>