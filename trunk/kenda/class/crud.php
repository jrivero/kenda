<?
class crud
{
  function crud ()
  {
    global $dbi;

		if ( is_object($dbi) )
    	$this->dbi = $dbi;
		else {
		  die('You try to use this function without having a database declared? Bye baby, bye...');
		}
  }

  function find_by_id ($_table, $_id) {
		$sql = 'SELECT * FROM ' . $_table . ' WHERE id = ' . intval($_id) . ' LIMIT 1';

		$rs  = $this->dbi->sql_query($sql);

		if ($this->dbi->sql_numrows($rs) > 0) {
			//return utf8_decode_array($this->dbi->sql_fetchrow($rs));
			return utf8encode($this->dbi->sql_fetchrow($rs));
    } else {
			return false;
    }
  }

  function find_all ($_table)
  {
    $sql = 'SELECT * FROM ' . $_table;
    $rs  = $this->dbi->sql_query($sql);

    if ($this->dbi->sql_numrows($rs) > 0)
    {
      $arr = array();
      while ($row = $this->dbi->sql_fetchrow($rs)) { array_push ($arr, utf8encode($row)); }
      return $arr;
    } else {
      return false;
    }
  }


  function find_by_sql ($sql) {
    $arr = array();

    $rs  = $this->dbi->sql_query($sql);

    if ($this->dbi->sql_numrows($rs) > 0) {
      while ($row = $this->dbi->sql_fetchrow($rs)) { array_push ($arr, utf8encode($row)); }
      return $arr;
    } else {
      return false;
    }
  }

  function delete_by_id ($_table, $_id) {
    $sql = "DELETE FROM " . $_table . " WHERE id = '" . intval($_id) . "'";
    $rs  = $this->dbi->sql_query($sql);

    return ($this->dbi->sql_affectedrows($rs) > 0) ? true : false;
  }

  function delete_by_sql($_sql) {
    $rs  = $this->dbi->sql_query($_sql);

    return ($this->dbi->sql_affectedrows($rs) > 0) ? true : false;
  }

  function save($_table, $param) {
    foreach ($param as $key => $val) {
    //$val = utf8encode($val);
    if (is_numeric($val)) { $arr_params[$key] = $val; }
    if (!empty($val)) { $arr_params[$key] = $val; }
    }

    if(isset($arr_params['id'])) {
      $sql = 'UPDATE ' . $_table . ' SET ';
      foreach($arr_params as $key => $val) {
        $val = trim(stripslashes($val));

        if($key != 'id') {
          if(is_numeric($val)) { $keys_params[] = $key . " = " . intval($val); }
          else { $keys_params[] = $key . " = " . "'$val'"; }
        }

      }
      $sql .= implode(', ', $keys_params) . ' WHERE id = ' . intval($param['id']);
    } else {
      $sql = 'INSERT INTO ' . $_table;
      $sql .= " (".implode(", ", array_keys($arr_params)).")";
      $sql .= " VALUES ('".implode("', '", $arr_params)."') ";
    }
    $rs = $this->dbi->sql_query($sql);

    return ($this->dbi->sql_affectedrows($rs) > 0) ? true : false;
  }

  function last_id(){
  	return $this->dbi->sql_lastid();
  }
}
?>