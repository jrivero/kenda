<?php
/**
 * Generate a md5 hash that will be the same for $timeout minutes
 *
 * @param $timeout - Timeout in minutes
 * @param $optional - An optional string to include to in the md5 string
 * @return
 */
function valid_for_x_minutes($timeout,$optional){
   if($timeout != "0"){
   $hours = date("H");
   $minutes = date("i");
   $tmpval = ceil($minutes/$timeout)*$timeout;
   if(!empty($optional)){
       return md5("$tmpval$optional");
   }else{
       return md5("$tmpval");
   }
   }else{
   return md5(time());
   }
}

?>