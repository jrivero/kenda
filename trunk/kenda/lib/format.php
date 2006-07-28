<?
/**
 * Return strign without acentos
 *
 * @param   string  $str     String with acentos
 * @return  string
 */
function sin_acentos ($str) {
  $str = (!is_utf8 ($str)) ? $str : utf8_decode($str);
	$str = htmlentities($str);
	$str = preg_replace('/&(\w{1})(\w+);/i', '\\1', $str);
	$str = preg_replace('/&.+?;/', '', $str);
	return $str;
}

function strtotimefix($strtotime) {
  return time() + (strtotime($strtotime) - strtotime('now'));
}

function clean_var ($clean_var) {
	$clean_var = trim($clean_var);
	$clean_var = addslasshes($clean_var);

	return $clean_var;
}

function utf8encode ($data) {
  if(!is_array($data)) {
    $data = (is_utf8 ($data)) ? $data : utf8_encode($data);
  } else {
    foreach($data as $key => $val){
     $data[$key] = (is_utf8 ($val)) ? $val : utf8_encode($val);
  	}
  }
  return $data;
}

/**
* Format a number of bytes into a human readable format.
* Optionally choose the output format and/or force a particular unit
*
* @param   int     $bytes      The number of bytes to format. Must be positive
* @param   string  $format     Optional. The output format for the string
* @param   string  $force      Optional. Force a certain unit. B|KB|MB|GB|TB
* @return  string              The formatted file size
*/
function getfilesize($bytes, $format = '', $force = '') {
  $force = strtoupper($force);
  $defaultFormat = '%01d %s';
  if (strlen($format) == 0)
      $format = $defaultFormat;

  $bytes = max(0, (int) $bytes);

  $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

  $power = array_search($force, $units);

  if ($power === false)
      $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

  return sprintf($format, $bytes / pow(1024, $power), $units[$power]);
}

?>