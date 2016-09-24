<?php
namespace jpuck\phpdev;

class Functions {
	public static function print_rt($array, Bool $return = false){
		$tabulated = str_replace('        ', '	', print_r($array, true));
		if($return){
			return $tabulated;
		}
		echo $tabulated;
	}
	public static function print_r_tabs($array, Bool $return = false){
		trigger_error(__METHOD__." is DEPRECATED: use print_rt instead.", E_USER_NOTICE);
		$tabulated = str_replace('        ', '	', print_r($array, true));
		if($return){
			return $tabulated;
		}
		echo $tabulated;
	}

	public static function arr_export(Array $array, Bool $return = false){
		$result = var_export($array, true);

		// replace 2-space indentations with a tab
		// http://stackoverflow.com/a/39682092/4233593
		$result = preg_replace ( '~(?:^|\G)\h{2}~m',  "\t",     $result);

		// open array
		$result = str_replace  ( "array (\n",         "[\n",    $result);
		// close array
		$result = str_replace  ( "\t),\n",            "\t],\n", $result);
		// close final array
		$result = preg_replace ( "/\)$/",             "]",      $result);

		// arrows without trailing spaces
		$result = str_replace  ( "=> \n",             "=>\n",   $result);

		if($return){
			return $result;
		}
		echo $result;
	}
}
