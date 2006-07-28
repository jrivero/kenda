<?
/**
 * Return files from $dir
 *
 * @param   string  $_folder     Folder for get folders
 * @return  array
 */
function filesFromDir($_folder) {
  $files  = Array();
  $handle = opendir($_folder);
  
  while ($file = readdir ($handle)) {
     if ($file != "." && $file != ".." && !is_dir($_folder . '/' . $file)) {
         array_push($files, $file);
     }
  }
  
  closedir($handle);
  
  return $files;
}

/**
 * Return folders from $_folder
 *
 * @param   string  $_folder     Folder for get folders
 * @return  array
 */
function FoldersFromDir($_folder) {
  $files  = Array();
  $handle = opendir($_folder);
  
  while ($file = readdir ($handle)) {
     if ($file != "." && $file != ".." && is_dir($_folder."/".$file)) {
         array_push($files, $file);
     }
  }
  
  closedir($handle);
  
  return $files;
}
?>