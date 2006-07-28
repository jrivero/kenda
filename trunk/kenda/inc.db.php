<?
	if (!empty($dbo['driver'])) {
	 if (file_exists ('./kenda/lib/dbal/' . $dbo['driver'] . '.php')) {
			include_once './kenda/lib/dbal/' . $dbo['driver'] . '.php';

			$dbi = new $sql_db();
			$dbi->sql_connect($dbo['server'],$dbo['login'],$dbo['password'],$dbo['database'],$dbo['port'],true);

			unset($dbo['password']);

			if(!$dbi->db_connect_id) {
				trigger_error('ERROR: No pude conectar con el servidor de SQL', E_USER_ERROR);
			}
		} else {
		  trigger_error('ERROR: No existe el driver de DB (' . $dbo['driver'] . ') solicitado SQL', E_USER_ERROR);
		}
	}
?>