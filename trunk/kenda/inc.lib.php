<?
	$handle = opendir('./kenda/lib/');
	while ($file = readdir ($handle)) {
	  if ($file != '.' && $file != '..' && !is_dir('./kenda/lib/' . $file)) {
	      include_once './kenda/lib/' . $file;
	  }
	}
	closedir($handle);
?>