<?php
// http://php.net/manual/en/function.var-export.php
class A
{
	public $var1;
	public $var2;

	public static function __set_state($an_array)
	{
		$obj = new A;
		$obj->var1 = $an_array['var1'];
		$obj->var2 = $an_array['var2'];
		return $obj;
	}
}

$a = new A;
$a->var1 = 5;
$a->var2 = 'foo';

return [$a];
