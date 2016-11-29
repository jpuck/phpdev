<?php
namespace jpuck\phpdev;

use InvalidArgumentException;

class ConfigurationContainer {
	static protected $registry = [];

	static public function set($key, $value){
		static::$registry[$key] = $value;
		return $value;
	}

	static public function get($key){
		return static::$registry[$key] ?? null;
	}

	static public function load(String $configuration_filename) : Bool {
		$configuration_filename = realpath($configuration_filename);
		if(!is_readable($configuration_filename)){
			throw new InvalidArgumentException(
				"$configuration_filename is not readable."
			);
		}

		require $configuration_filename;
		$variables = get_defined_vars();

		foreach($variables as $key => $value){
			static::set($key, $value);
		}

		return true;
	}

	static public function dump() : Array {
		return static::$registry;
	}

	static public function closures(&$get, &$set = null){
		$get = function($key){ return static::get($key); };
		$set = function($key, $val){ return static::set($key, $val); };
	}
}
