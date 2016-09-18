# PHP Developer Utilities

A collection of classes useful for code under construction.

## Unimplemented Method Exception

```php
<?php

use jpuck\phpdev\Unimplemented;

class MyClass {
	public function foo() {
		// completed code
		return true;
	}

	/**
	 * @throws Unimplemented
	 */
	protected function bar() {
		// work in progress

		throw new Unimplemented(__METHOD__);

		return true;
	}
}
```
