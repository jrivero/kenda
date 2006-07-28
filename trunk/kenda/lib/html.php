<?
function paginacion ($pagina, $total, $limite = '10', $criterio = '') {
 $_op = '';

 // especifica pagina
 $desde = (isset($pagina)) ? ($pagina - 1) * $limite : 0;
 $pagina_enlace = (isset($pagina_enlace)) ? $pagina_enlace : '';
 $pagina = ($pagina) ? $pagina : 1;

 // Calcula numero de paginas
 $total_paginas = ceil($total / $limite);

 $siguiente = $pagina + 1;
 $anterior = $pagina - 1;

 // Control de modulo inicial
 if (isset($_REQUEST['mod'])) { $_op = '&amp;mod='.$_REQUEST['mod']; }

 // Control de ejecucion
 $_exec = (isset($_REQUEST['exec'])) ? '&amp;exec='.$_REQUEST['exec'] : '';

 $html = '<div id="paginacion"><div class="listado_paginas">';

  // Enlaces de paginacion. Siguiente / Anterior. Y informacion...
 if ($total > $limite) {
  $texto_pagina = 'P&aacute;gina:&nbsp;';
	$html .= $texto_pagina;
 }
 if ($anterior >= 1) {
  $pagina_anterior = '<a href="?pagina=' . $anterior . $_op . $_exec . $criterio . '" title="Ir a p&aacute;gina anterior"> <span class="anterior">&lt;</span> </a>';
 } else { $pagina_anterior = ' <span class="anterior">&lt;</span> ';	}
 $html .= $pagina_anterior;

 for ($num = 1; $num <= $total_paginas; $num++) {
  if ($pagina == $num) {
  $pagina_enlace .= '<strong>'.$num.'</strong>&nbsp;';
  }else{
   $pagina_enlace .= '<a href="?pagina=' . $num . $_op . $_exec . $criterio . '" title="Ir a p&aacute;gina: '.$num.'">'.$num.'</a>&nbsp;';
  }
 }
 $html .= '<span class="paginas">'. $pagina_enlace .'</span>';

 if ($siguiente <= $total_paginas) {
  $pagina_siguiente = '<a href="?pagina=' . $siguiente . $_op . $_exec . $criterio . '" title="Ir a p&aacute;gina siguiente"><span class="siguiente">&gt;</span></a>';
 } else { $pagina_siguiente = '<span class="siguiente">&gt;</span>';	}
 $html .= $pagina_siguiente;


 if ($desde + $limite > $total) { $mostrados = $total; } else { $mostrados = $desde + $limite ; }

 $html .= '</div>';

 if ($total > 0) {
	$desde = $desde + 1;
  $info_pagina = '<span class="detalles_paginacion">P&aacute;gina <strong>'.$pagina.'</strong> de <strong>'.$total_paginas.'</strong>. Mostrando <strong>'.$desde.'</strong>-<strong>'.$mostrados.'</strong> de <strong>'.$total.'</strong> resultados </span>';
 	$html .= $info_pagina;
 }

 $html .= '</div>';

 return $html;
}

function javascript() {

  $html = '<script language="javascript" type="text/javascript">';
//  $html.= '<!--';
  $html.= ' function confirmar(theURL,message) { ';
  $html.= '   var msg = new app.alert(); ';
  $html.= ' 	msg.title = "Confirmaci&oacute;n"; ';
  $html.= " 	msg.message = '&iquest;Estas seguro de '+message+'?'; ";
  $html.= ' 	msg.ok = function() { ';
  $html.= ' 		msg.hide(); ';
  $html.= '     window.location.href=theURL; ';
  $html.= ' 	}; ';
  $html.= ' 	msg.show(); ';
  $html.= ' 	return false; ';
  $html.= ' }';
//  $html.= '//-->';
  $html.= '</script>';

	return $html;
}

function ArrayToUL($array) {
 $recursion=__FUNCTION__;
 if (empty($array)) return '';

 $out='<ul>'."\n";
 if (is_array($array)) {
	 foreach ($array as $key => $elem)
			$out .= '<li>' . $key . $recursion($elem) . '</li>'."\n";
 } else {
		$out .= '<li>'.$array.'</li>'."\n";
 }
 $out .= '</ul>'."\n";
 return $out;
}
?>