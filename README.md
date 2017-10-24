# Lepre DI

A simple Dependency Injection Container, [PSR-11][psr11] compliant.

## Installation

Install the latest version with [Composer][composer]:

```
$ composer require lepre/di
```

### Requirements

This project works with PHP 7.0 or above.

## Basic Usage

```php
use Lepre\Component\DI\Container;

$container = new Container();

// register a service:
$container->set('my service', function () {
    return new MyService();
});

// register a service with dependencies:
$container->set('other service', function (Container $container) {
    return new OtherService(
        $container->get('my service')
    );
});
```

## License

This project is licensed under the MIT License. See the LICENSE file for details.

[composer]: https://getcomposer.org/
[psr11]: http://www.php-fig.org/psr/psr-11/