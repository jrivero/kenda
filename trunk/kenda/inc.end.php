<?php
	$kenda_end  = getmicrotime();
	$kenda_time = $kenda_end - $kenda_start;
	$kenda_time = round($kenda_time,3);

	kenda_rpt('Tiempo de renderizado ' . $kenda_time .' segundos');

	if(isset($dbi) AND $dbi->db_connect_id) { $dbi->sql_close(); }

	if ($KENDA_RPT = kenda_rpt()) {
		$tpl->assign('KENDA_RPT', '<div id="kenda_rpt">' . echo_rpt($KENDA_RPT) . '</div>');
	} else {
		$tpl->assign('KENDA_RPT', '');
	}

  foreach($modulo as $key => $val){
   $val = (is_utf8 ($val)) ? $val : utf8_encode($val);
	 $tpl->assign('MOD_' . strtoupper($key),$val);
	}
	$tpl->assign('MOD',$mod);

  foreach($app as $key => $val){
   $val = (is_utf8 ($val)) ? $val : utf8_encode($val);
	 $tpl->assign('APP_' . strtoupper($key),$val);
	}

	if (!empty($modulo['mensaje'])):
    $tpl->assign('CONTENIDO', $modulo['mensaje']);
  else:
  	if (empty($modulo['vista'])):
      if (file_exists ($c_file)):
        $tpl->assign('CONTENIDO', $tpl->fetch($reqMethod.'.tpl'));
      endif;
    else:
      $tpl->assign('CONTENIDO', $tpl->fetch($modulo['vista'].'.tpl'));
    endif;
  endif;

	$tpl->display($app['wrapper']);
?>