<?php
namespace jpuck\phpdev\Exceptions;

use Exception;

class Unimplemented extends Exception {
	public function __construct(String $method) {
		parent::__construct("Method '$method' Not Implemented.");
	}
}
