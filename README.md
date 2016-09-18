# PHP Developer Utilities

A collection of classes useful for code under construction.

## Unimplemented Exception

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
