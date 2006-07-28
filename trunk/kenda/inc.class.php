<?
	$handle = opendir('./kenda/class/');
	while ($file = readdir ($handle)) {
	  if ($file != '.' && $file != '..' && !is_dir('./kenda/class/' . $file)) {
	      include_once './kenda/class/' . $file;
	  }
	}
	closedir($handle);
	
	$cache = new cache();
?>