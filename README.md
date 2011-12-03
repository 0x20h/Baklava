Baklava Plugin
==================

Use Baklava to transparently compress, combine and cache
your css and javascripts.

In app/Config/bootstrap.php:

either

```php
CakePlugin::loadAll()
``` 
or

```php
CakePlugin::loadAll(array(
	...
	'Baklava' => array(),
));

```

In your AppController:

```php
	...
	public $helpers = array(
		'Html' => array(
			'className' => 'Baklava.BaklavaHtmlHelper',
		),
		'Js' => array(
			'className' => 'Baklava.BaklavaJsHelper',
		),
	);
	...
```

Options
-------
...


Extended Usage
--------------
...

Custom Compressors
------------------
...
