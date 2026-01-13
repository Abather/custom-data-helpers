# Custom Data Helpers for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/abather/custom-data-helpers.svg?style=flat-square)](https://packagist.org/packages/abather/custom-data-helpers)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/abather/custom-data-helpers/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/abather/custom-data-helpers/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/abather/custom-data-helpers/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/abather/custom-data-helpers/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/abather/custom-data-helpers.svg?style=flat-square)](https://packagist.org/packages/abather/custom-data-helpers)

## Description

This Laravel package extends the default data helper functions (`data_get`, `data_set`, `data_forget`, `data_has`) to support **custom separators** instead of the hardcoded dot (`.`) notation.

Laravel's built-in data helpers are fantastic, but they use a fixed dot separator which can cause issues when your data keys contain actual dots. This package solves that problem by allowing you to specify any separator you want.


## Installation

You can install the package via composer:

```bash
composer require abather/custom-data-helpers
```

That's it! The package will auto-register itself in Laravel and the helper functions will be available globally.

## Usage

The package provides **three ways** to use custom data helpers:

### 1. Using the DataHelper Class (Recommended)
```php
use Abather\CustomDataHelpers\DataHelper;

$data = ['users' => ['profile' => ['name' => 'John']]];

// Get value with custom separator
DataHelper::get($data, 'users/profile/name', '/'); // 'John'

// Get with default value
DataHelper::get($data, 'users/profile/age', '/', 25); // 25 (if key doesn't exist)

// Set value
DataHelper::set($data, 'users/profile/email', 'john@example.com', true, '/');

// Check existence
DataHelper::has($data, 'users/profile/name', '/'); // true

// Remove key
DataHelper::forget($data, 'users/profile/email', '/');
```

### 2. Using Global Helper Functions
```php
// All DataHelper methods are also available as global helper functions with _ prefix
_data_get($data, 'users/profile/name', '/'); // 'John'
_data_set($data, 'users/profile/email', 'john@example.com', true, '/');
_data_has($data, 'users/profile/name', '/'); // true
_data_forget($data, 'users/profile/email', '/');
```

---

### `DataHelper::get()` - Retrieve Values

Retrieve a value from a nested array or object using a custom separator.

```php
use Abather\CustomDataHelpers\DataHelper;

// Basic usage with default dot separator (backward compatible)
$data = ['products' => ['desk' => ['price' => 100]]];
DataHelper::get($data, 'products.desk.price'); // 100

// Using custom separator
$data = ['products' => ['desk' => ['price' => 100]]];
DataHelper::get($data, 'products/desk/price', '/'); // 100

// With default value
DataHelper::get($data, 'products/desk/discount', '/', 0); // 0

// Using wildcards to get multiple values
$data = [
    'products' => [
        ['name' => 'Desk', 'price' => 100],
        ['name' => 'Chair', 'price' => 50]
    ]
];
DataHelper::get($data, 'products/*/price', '/'); // [100, 50]

// Using placeholders
$flight = [
    'segments' => [
        ['from' => 'LHR', 'to' => 'IST'],
        ['from' => 'IST', 'to' => 'PKX'],
    ]
];
DataHelper::get($flight, 'segments->{first}->from', '->'); // 'LHR'
DataHelper::get($flight, 'segments->{last}->to', '->'); // 'PKX'

// Using different separators for different data structures
$config = ['app' => ['database' => ['host' => 'localhost']]];
DataHelper::get($config, 'app|database|host', '|'); // 'localhost'
DataHelper::get($config, 'app->database->host', '->'); // 'localhost'
DataHelper::get($config, 'app/database/host', '/'); // 'localhost'
```

### `DataHelper::set()` - Set Values

Set a value within a nested array or object using a custom separator.

```php
use Abather\CustomDataHelpers\DataHelper;

$data = [];

// Basic usage
DataHelper::set($data, 'products|desk|price', 200, true, '|');
// Result: ['products' => ['desk' => ['price' => 200]]]

// Using wildcards for bulk updates
$products = [
    ['name' => 'Desk', 'price' => 100],
    ['name' => 'Chair', 'price' => 50]
];
DataHelper::set($products, '*/price', 0, true, '/');
// Sets all product prices to 0

// Conditional setting (don't overwrite existing values)
$data = ['products' => ['desk' => ['price' => 100]]];
DataHelper::set($data, 'products.desk.price', 200, false);
// Price remains 100

// Overwrite existing values (default behavior)
DataHelper::set($data, 'products.desk.price', 200, true);
// Price is now 200

// Create nested structures automatically
$data = [];
DataHelper::set($data, 'api/v1/endpoints/users', '/api/v1/users', true, '/');
// Result: ['api' => ['v1' => ['endpoints' => ['users' => '/api/v1/users']]]]

// Using object notation separator
$settings = [];
DataHelper::set($settings, 'app->theme->colors->primary', '#007bff', true, '->');
// Result: ['app' => ['theme' => ['colors' => ['primary' => '#007bff']]]]
```

### `DataHelper::has()` - Check if Key Exists

Check if a key exists in an array or object using a custom separator.

```php
use Abather\CustomDataHelpers\DataHelper;

$data = ['products' => ['desk' => ['price' => 100]]];

// Basic usage
DataHelper::has($data, 'products.desk.price'); // true
DataHelper::has($data, 'products.desk.discount'); // false

// Using custom separator
DataHelper::has($data, 'products->desk->price', '->'); // true
DataHelper::has($data, 'products/desk/price', '/'); // true
DataHelper::has($data, 'products|desk|stock', '|'); // false

// Works with nested objects
$object = (object) [
    'user' => (object) [
        'profile' => (object) ['name' => 'John']
    ]
];
DataHelper::has($object, 'user/profile/name', '/'); // true
DataHelper::has($object, 'user/profile/email', '/'); // false

// Using with different separators
$routes = [
    'api' => [
        'v1' => ['users' => 'UsersController']
    ]
];
DataHelper::has($routes, 'api/v1/users', '/'); // true
DataHelper::has($routes, 'api->v1->users', '->'); // true
DataHelper::has($routes, 'api.v1.posts', '.'); // false
```

### `DataHelper::forget()` - Remove Values

Remove a value from a nested array or object using a custom separator.

```php
use Abather\CustomDataHelpers\DataHelper;

$data = ['products' => ['desk' => ['price' => 100, 'stock' => 50]]];

// Basic usage
DataHelper::forget($data, 'products.desk.price');
// Result: ['products' => ['desk' => ['stock' => 50]]]

// Using custom separator
DataHelper::forget($data, 'products|desk|stock', '|');
// Result: ['products' => ['desk' => []]]

// Using wildcards to remove from multiple items
$products = [
    ['name' => 'Desk', 'price' => 100, 'discount' => 10],
    ['name' => 'Chair', 'price' => 50, 'discount' => 5]
];
DataHelper::forget($products, '*/discount', '/');
// Removes 'discount' from all products

// Using different separators
$config = [
    'app' => [
        'debug' => true,
        'cache' => ['enabled' => true, 'ttl' => 3600]
    ]
];
DataHelper::forget($config, 'app/cache/ttl', '/');
// Result: ['app' => ['debug' => true, 'cache' => ['enabled' => true]]]

DataHelper::forget($config, 'app->cache->enabled', '->');
// Result: ['app' => ['debug' => true, 'cache' => []]]
```

## Real-World Use Cases

### 1. Working with Keys Containing Dots

```php
// API response with dotted keys
$config = [
    'app.name' => 'My Application',
    'app.env' => 'production',
    'database.host' => 'localhost'
];

// Access them safely using a different separator
$appName = _data_get($config, 'app.name', '|'); // 'My Application'
```

### 2. URL-Style Paths

```php
$routes = [
    'api' => [
        'v1' => [
            'users' => 'UsersController@index'
        ]
    ]
];

$controller = _data_get($routes, 'api/v1/users', '/');
// 'UsersController@index'
```

### 3. Object Notation Style

```php
$data = [
    'user' => [
        'profile' => [
            'settings' => [
                'theme' => 'dark'
            ]
        ]
    ]
];

$theme = _data_get($data, 'user->profile->settings->theme', '->');
// 'dark'
```

## Backward Compatibility

The DataHelper class and helper functions use dot notation by default, making them compatible with Laravel's conventions. The package uses underscore-prefixed global functions (`_data_*`) to avoid conflicts with Laravel's built-in helpers.

```php
// Using default dot separator (no conflicts with Laravel)
DataHelper::get($array, 'user.name');
DataHelper::set($array, 'user.email', 'john@example.com');
DataHelper::has($array, 'user.profile');
DataHelper::forget($array, 'user.settings');

// Or using global helpers
_data_get($array, 'user.name');
_data_set($array, 'user.email', 'john@example.com');
_data_has($array, 'user.profile');
_data_forget($array, 'user.settings');
```

## Features

- ✅ Custom separator support for all data helpers
- ✅ Wildcard operations (`*`)
- ✅ Placeholder support (`{first}`, `{last}`)
- ✅ Works with arrays and objects
- ✅ Laravel Collections support
- ✅ 100% backward compatible with Laravel's default helpers
- ✅ No configuration needed
- ✅ Auto-discovery enabled
- ✅ Fully tested

## Requirements

- PHP 8.4 or higher
- Laravel 11.x or 12.x

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Abather](https://github.com/Abather)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
