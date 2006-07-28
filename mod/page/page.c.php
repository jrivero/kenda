<?
if (!defined('IN_APP')) { die ("Acceso denegado"); }

$$reqModule = new modelo;

/* 
* Configuracion modulo
* @param array (titulo, descripcion)
*/
$modulo = array(
	'titulo'			=>	'Page',
	'descripcion'	=>	'Modulo de gestión de Paginas');

switch ($reqMethod) {

  case 'index':
  	$tpl->assign('POST', $page->listar());
  break;

  case 'nuevo':
    if (isset($_POST['xs'])):
      // Insertar
    else:
      // Formulario
    endif;
  break;

  case 'editar':
    if (isset($_POST['xs'])):
      // Actualizar
      // kenda_rpt(array('Mensaje...','url'),'redirect');
    else:
	    if (empty($_GET['id'])) {
	      kenda_rpt('Es necesario un id...','error');
	    } else {
	      if ($post = $page->ver($_GET['id'])) {
	    		foreach($post as $key => $val){ $tpl->assign(strtoupper($key),trim($val)); }
				}
			}
    endif;
  break;

  case 'ver':
    if (empty($_GET['id'])) {
      kenda_rpt('Es necesario un id...','error');
    } else {
      if ($post = $page->ver($_GET['id'])) {
    		foreach($post as $key => $val){ $tpl->assign(strtoupper($key),trim($val)); }
			}
		}
  break;
  
  case 'eliminar':
    // Eliminar
  break;

}

?>