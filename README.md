# PHP Developer Utilities

A collection of classes useful for code under construction.

## Functions

### `mixed print_rt ( mixed $expression [, bool $return = false ] )`

Tabulator wrapper for [`print_r`][1] replaces 8 spaces with a tab.

```php
use jpuck\phpdev\Functions as jp;

$array = [
	'first' =>
	[
		'second' =>
		[
			'third' =>
			[
				'forth' =>
				[
					'fifth' => 5
				]
			]
		]
	]
];

print_r($array);

jp::print_rt($array);
```

Example displayed on console with tabs set to 4 spaces:

>     Array
>     (
>         [first] => Array
>             (
>                 [second] => Array
>                     (
>                         [third] => Array
>                             (
>                                 [forth] => Array
>                                     (
>                                         [fifth] => 5
>                                     )
>
>                             )
>
>                     )
>
>             )
>
>     )
>     Array
>     (
>         [first] => Array
>         (
>             [second] => Array
>             (
>                 [third] => Array
>                 (
>                     [forth] => Array
>                     (
>                         [fifth] => 5
>                     )
>
>                 )
>
>             )
>
>         )
>
>     )

## Exceptions

### `Unimplemented` Method

```php
use jpuck\phpdev\Exceptions\Unimplemented;

class MyClass {
	public function foo() {
		// completed code
		return true;
	}

	/**
	 * @throws Unimplemented
	 */
	public function bar() {
		// work in progress

		throw new Unimplemented(__METHOD__);

		return true;
	}
}
```

  [1]:http://php.net/manual/en/function.print-r.php