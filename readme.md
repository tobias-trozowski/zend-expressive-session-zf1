# Zend Expressive ZF1 Compatible Session
Provides an ZF1 compatible ext-session persistence adapter for use with [zend-expressive-session](https://docs.zendframework.com/zend-expressive-session).

Inspired and based on [zend-expressive-session-ext](https://docs.zendframework.com/zend-expressive-session-ext/).

## Installation

Run the following to install this library::
```
$ composer require tobias/zend-expressive-session-zf1
```

## Configuration
If your application uses the [zend-component-installer](https://docs.zendframework.com/zend-component-installer)
Composer plugin, your configuration is complete; the shipped
`Tobias\Zend\Expressive\Zf1Session\ConfigProvider` registers the
`Tobias\Zend\Expressive\Zf1Session\Persistence\SessionPersistence` service, as well as an alias
to `SessionPersistence` it under the name `Zend\Expressive\Session\SessionPersistenceInterface`.

You can add the `Tobias\Zend\Expressive\Zf1Session\ConfigProvider` manually to your `config/config.php` e.g.:
```php
$aggregator = new ConfigAggregator(
    [
        // ...
        
        \Zend\Expressive\Session\ConfigProvider::class,
        \Tobias\Zend\Expressive\Zf1Session\ConfigProvider::class,

        // ...
    ]);
```

Otherwise, you will need to map `Zend\Expressive\Session\SessionPersistenceInterface`
to `Tobias\Zend\Expressive\Zf1Session\Persistence\PhpSerializableSessionPersistence` in your dependency
injection container.

In addition to this you can configure all [parameters](http://php.net/manual/de/function.session-start.php#refsect1-function.session-start-parameters) 
passed to the session via configuration, e.g. `config/autoload/session-params.global.php`
```php
<?php
return [
    'session' => [
        'use_cookies' => false,
        'use_only_cookies' => true,
    ]
];

```
