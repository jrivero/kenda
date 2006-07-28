<?
function utf8_encode_array (&$array) {
   if(is_array($array)) {
     array_walk ($array, 'utf8_encode_array');
   } else {
     $array = (!is_utf8 ($array)) ? $array : utf8_encode($array);
   }
   return $array;
}

function utf8_decode_array (&$array) {
   if(is_array($array)) {
     array_walk ($array, 'utf8_decode_array');
   } else {
      $array = (!is_utf8 ($array)) ? $array : utf8_decode($array);
   }
   return $array;
}

function addslashes_array( $arg ) {
    if ( is_array( $arg ) )
    {
        foreach ( $arg as $key => $val )
            $arg[ $key ] = addslashes_array( $val );
    }
    else
        $arg = addslashes( $arg );

    return $arg;
}

function stripslashes_array( $arg ) {
    if ( is_array( $arg ) )
    {
        foreach ( $arg as $key => $val )
            $arg[ $key ] = stripslashes_array( $val );
    }
    else
        $arg = stripslashes( $arg );

    return $arg;
}

function trim_array( $arg ) {
    if ( is_array( $arg ) )
    {
        foreach ( $arg as $key => $val )
            $arg[ $key ] = trim_array( $val );
    }
    else
        $arg = trim( $arg );

    return $arg;
}

function implode_with_key($assoc, $inglue = '=', $outglue = '&') {
   $return = null;
   foreach ($assoc as $tk => $tv) $return .= $outglue.$tk.$inglue.$tv;
   return substr($return,1);
}

?>
