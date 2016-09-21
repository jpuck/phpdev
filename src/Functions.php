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
}
