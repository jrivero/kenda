<?
/**
 * Return is $email compliant RFC 2822
 *
 * @param $_string - string with a possible email
 * @return
 */
function is_valid_email( $_string ) {
	return (preg_match( "/^[-^!#$%&'*+\/=?`{|}~.\w]+@[a-z0-9]+([.-][a-z0-9]+)*$/i", $_string ) > 0 );
}

/**
 * Return is $_string a md5 hash
 *
 * @param $_string - string with a possible md5
 * @return
 */
function is_md5($_string) {
	return preg_match('/^[A-Fa-f0-9]{32}$/', $_string);
}

/**
 * Return is $_string encode in UTF-8
 *
 * @param $_string - string or array with a possible utf-8 encode
 * @return
 */
function is_utf8($_string) {
   if (is_array($_string)) {
      $enc = implode(' ', $_string);
      return @!((ord($enc[0]) != 239) && (ord($enc[1]) != 187) && (ord($enc[2]) != 191));
   } else {
      return (utf8_encode(utf8_decode($_string)) == $_string);
   }
}

?>