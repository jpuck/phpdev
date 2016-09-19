<?php
namespace jpuck\phpdev;

class Functions {
	public static function print_r_tabs($array, Bool $return = false){
		$tabulated = str_replace('        ', '	', print_r($array, true));
		if($return){
			return $tabulated;
		}
		echo $tabulated;
	}
}
