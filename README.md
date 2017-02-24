# Keygen
> A fluent PHP random key generator.

Keygen is a PHP package that generates random character sequences known as *keys*. The package ships with built-in key generators for four key types namely: *numeric*, *alphanumeric*, *token* and *byte*. Its implementation effectively combines simplicity and expressiveness.

## Installation

### With Composer
The Keygen package can be installed easily with [Composer] - require the `gladcodes/keygen` package from the command line.

```shell
$ composer require gladcodes/keygen
```

Alternatively, you can manually add the Keygen package to the `composer.json` file of your project and then run `composer install` from the command line as follows:

```json
{
    "require": {
        "gladcodes/keygen": "~1.1"
    }
}
```

```shell
$ composer install
```

You can use it in your PHP code like this:

```php
<?php

require __DIR__ . '/vendor/autoload.php';
use Keygen\Keygen;

printf("Your appID is %.0f", Keygen::numeric(12)->generate()); // Your appID is 878234290135
```

## Todos
- Write tests

## License
The Keygen package is covered by the `MIT` License.

[Composer]: <https://getcomposer.org>
