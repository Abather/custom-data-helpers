<?php

/*
 * NOTE: Due to Laravel's built-in _data_get/_data_set/_data_has/_data_forget helpers being loaded first,
 * the custom helpers in src/helpers.php are never loaded because of function_exists() checks.
 * These tests currently test Laravel's default helpers which only support '.' as separator.
 *
 * To properly test custom separators, the package should provide:
 * 1. Class methods instead of global functions (e.g., CustomDataHelpers::get())
 * 2. Or use different function names (e.g., custom__data_get())
 */

// ============================================================================
// _data_get() Tests - Default Separator
// ============================================================================

test('_data_get with default dot separator', function () {
    $array = ['user' => ['name' => 'John', 'age' => 30]];

    expect(_data_get($array, 'user.name'))->toBe('John');
    expect(_data_get($array, 'user.age'))->toBe(30);
});

test('_data_get with single level key', function () {
    $array = ['user' => 'John', 'age' => 30];

    expect(_data_get($array, 'user'))->toBe('John');
    expect(_data_get($array, 'age'))->toBe(30);
});

test('_data_get with three levels and default separator', function () {
    $array = ['user' => ['profile' => ['email' => 'john@example.com']]];

    expect(_data_get($array, 'user.profile.email'))->toBe('john@example.com');
});

test('_data_get returns default when key not found', function () {
    $array = ['user' => ['name' => 'John']];

    expect(_data_get($array, 'user.email', '.', 'default@example.com'))->toBe('default@example.com');
    expect(_data_get($array, 'missing.key', '.', 'default'))->toBe('default');
});

test('_data_get with wildcard and default separator', function () {
    $array = [
        'users' => [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ],
    ];

    expect(_data_get($array, 'users.*.name'))->toBe(['John', 'Jane']);
    expect(_data_get($array, 'users.*.age'))->toBe([30, 25]);
});

test('_data_get with nested wildcard and default separator', function () {
    $array = [
        'categories' => [
            ['products' => [['name' => 'A'], ['name' => 'B']]],
            ['products' => [['name' => 'C'], ['name' => 'D']]],
        ],
    ];

    expect(_data_get($array, 'categories.*.products.*.name'))->toBe(['A', 'B', 'C', 'D']);
});

test('_data_get with first placeholder and default separator', function () {
    $array = ['items' => ['first' => 'A', 'second' => 'B', 'third' => 'C']];

    expect(_data_get($array, 'items.{first}'))->toBe('A');
});

test('_data_get with last placeholder and default separator', function () {
    $array = ['items' => ['first' => 'A', 'second' => 'B', 'third' => 'C']];

    expect(_data_get($array, 'items.{last}'))->toBe('C');
});

test('_data_get with last placeholder on numeric array', function () {
    $array = ['scores' => [10, 20, 30, 40]];

    expect(_data_get($array, 'scores.{last}'))->toBe(40);
});

test('_data_get with first placeholder on nested structure', function () {
    $array = ['users' => ['john' => ['age' => 30], 'jane' => ['age' => 25]]];

    $result = _data_get($array, 'users.{first}');
    expect($result)->toBeArray();
    expect($result['age'])->toBe(30);
});

test('_data_get with escaped placeholders', function () {
    $array = ['data' => ['{first}' => 'value1', '{last}' => 'value2']];

    expect(_data_get($array, 'data.\{first}'))->toBe('value1');
    expect(_data_get($array, 'data.\{last}'))->toBe('value2');
});

test('_data_get with escaped wildcard', function () {
    $array = ['data' => ['*' => 'asterisk value']];

    expect(_data_get($array, 'data.\*'))->toBe('asterisk value');
});

test('_data_get with null key returns whole array', function () {
    $array = ['user' => ['name' => 'John']];

    expect(_data_get($array, null))->toBe($array);
});

test('_data_get with Laravel Collection', function () {
    $collection = collect([
        'users' => [
            ['name' => 'John'],
            ['name' => 'Jane'],
        ],
    ]);

    expect(_data_get($collection, 'users.*.name'))->toBe(['John', 'Jane']);
});

test('_data_get with object properties', function () {
    $object = (object) [
        'user' => (object) ['name' => 'John', 'age' => 30],
    ];

    expect(_data_get($object, 'user.name'))->toBe('John');
    expect(_data_get($object, 'user.age'))->toBe(30);
});

test('_data_get deep nesting', function () {
    $array = ['a' => ['b' => ['c' => ['d' => ['e' => 'value']]]]];

    expect(_data_get($array, 'a.b.c.d.e'))->toBe('value');
});

test('_data_get with multiple wildcards in sequence', function () {
    $array = [
        'level1' => [
            ['level2' => [['value' => 'A'], ['value' => 'B']]],
            ['level2' => [['value' => 'C']]],
        ],
    ];

    expect(_data_get($array, 'level1.*.level2.*.value'))->toBe(['A', 'B', 'C']);
});

// ============================================================================
// _data_set() Tests - Default Separator
// ============================================================================

test('_data_set with default dot separator', function () {
    $array = [];
    _data_set($array, 'user.name', 'John');

    expect($array)->toBe(['user' => ['name' => 'John']]);
});

test('_data_set creates deeply nested structure', function () {
    $array = [];
    _data_set($array, 'config.database.host', 'localhost');

    expect($array)->toBe(['config' => ['database' => ['host' => 'localhost']]]);
});

test('_data_set with wildcard and default separator', function () {
    $array = [
        'users' => [
            ['name' => 'John'],
            ['name' => 'Jane'],
        ],
    ];
    _data_set($array, 'users.*.active', true);

    expect($array)->toBe([
        'users' => [
            ['name' => 'John', 'active' => true],
            ['name' => 'Jane', 'active' => true],
        ],
    ]);
});

test('_data_set replaces all values with wildcard at end', function () {
    $array = [
        'items' => ['a', 'b', 'c'],
    ];
    _data_set($array, 'items.*', 'new');

    expect($array)->toBe([
        'items' => ['new', 'new', 'new'],
    ]);
});

test('_data_set with overwrite false preserves existing value', function () {
    $array = ['user' => ['name' => 'John']];
    _data_set($array, 'user.name', 'Jane', false);

    expect($array['user']['name'])->toBe('John');
});

test('_data_set with overwrite true replaces value', function () {
    $array = ['user' => ['name' => 'John']];
    _data_set($array, 'user.name', 'Jane', true);

    expect($array['user']['name'])->toBe('Jane');
});

test('_data_set updates existing nested values', function () {
    $array = ['config' => ['database' => ['host' => 'localhost']]];
    _data_set($array, 'config.database.port', 3306);

    expect($array)->toBe([
        'config' => [
            'database' => [
                'host' => 'localhost',
                'port' => 3306,
            ],
        ],
    ]);
});

test('_data_set with array key parameter', function () {
    $array = [];
    _data_set($array, ['config', 'database', 'host'], 'localhost');

    expect($array)->toBe(['config' => ['database' => ['host' => 'localhost']]]);
});

test('_data_set creates path when overwrite is false for new keys', function () {
    $array = ['config' => ['debug' => true]];
    _data_set($array, 'config.cache', 'redis', false);

    expect($array['config']['cache'])->toBe('redis');
});

test('_data_set with nested wildcard', function () {
    $array = [
        'categories' => [
            ['products' => [['price' => 100], ['price' => 200]]],
            ['products' => [['price' => 150]]],
        ],
    ];

    _data_set($array, 'categories.*.products.*.discounted', true);

    expect($array['categories'][0]['products'][0]['discounted'])->toBeTrue();
    expect($array['categories'][0]['products'][1]['discounted'])->toBeTrue();
    expect($array['categories'][1]['products'][0]['discounted'])->toBeTrue();
});

// ============================================================================
// _data_has() Tests - Default Separator
// ============================================================================

test('_data_has with default dot separator', function () {
    $array = ['user' => ['name' => 'John', 'age' => 30]];

    expect(_data_has($array, 'user.name'))->toBeTrue();
    expect(_data_has($array, 'user.email'))->toBeFalse();
});

test('_data_has with deeply nested structure', function () {
    $array = ['a' => ['b' => ['c' => ['d' => ['e' => 'value']]]]];

    expect(_data_has($array, 'a.b.c.d.e'))->toBeTrue();
    expect(_data_has($array, 'a.b.c.d.f'))->toBeFalse();
});

test('_data_has with null or empty key', function () {
    $array = ['user' => ['name' => 'John']];

    expect(_data_has($array, null))->toBeFalse();
    expect(_data_has($array, []))->toBeFalse();
});

test('_data_has with object properties', function () {
    $object = (object) [
        'user' => (object) ['name' => 'John'],
    ];

    expect(_data_has($object, 'user.name'))->toBeTrue();
    expect(_data_has($object, 'user.age'))->toBeFalse();
});

test('_data_has with null values', function () {
    $array = ['user' => ['name' => null]];

    expect(_data_has($array, 'user.name'))->toBeTrue();
});

test('_data_has with false and zero values', function () {
    $array = [
        'active' => false,
        'count' => 0,
    ];

    expect(_data_has($array, 'active'))->toBeTrue();
    expect(_data_has($array, 'count'))->toBeTrue();
});

test('_data_has with array key parameter', function () {
    $array = ['user' => ['profile' => ['email' => 'john@example.com']]];

    expect(_data_has($array, ['user', 'profile', 'email']))->toBeTrue();
    expect(_data_has($array, ['user', 'profile', 'phone']))->toBeFalse();
});

test('_data_has with single level key', function () {
    $array = ['key' => 'value'];

    expect(_data_has($array, 'key'))->toBeTrue();
    expect(_data_has($array, 'missing'))->toBeFalse();
});

// ============================================================================
// _data_forget() Tests - Default Separator
// ============================================================================

test('_data_forget with default dot separator', function () {
    $array = ['user' => ['name' => 'John', 'age' => 30]];
    _data_forget($array, 'user.name');

    expect($array)->toBe(['user' => ['age' => 30]]);
});

test('_data_forget with deeply nested structure', function () {
    $array = ['a' => ['b' => ['c' => 'value', 'd' => 'keep']]];
    _data_forget($array, 'a.b.c');

    expect($array)->toBe(['a' => ['b' => ['d' => 'keep']]]);
});

test('_data_forget with wildcard and default separator', function () {
    $array = [
        'users' => [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ],
    ];
    _data_forget($array, 'users.*.age');

    expect($array)->toBe([
        'users' => [
            ['name' => 'John'],
            ['name' => 'Jane'],
        ],
    ]);
});

test('_data_forget on object', function () {
    $object = (object) [
        'user' => (object) ['name' => 'John', 'age' => 30],
    ];
    _data_forget($object, 'user.age');

    expect(isset($object->user->age))->toBeFalse();
    expect($object->user->name)->toBe('John');
});

test('_data_forget non-existent key does nothing', function () {
    $array = ['user' => ['name' => 'John']];
    $original = $array;
    _data_forget($array, 'user.email');

    expect($array)->toBe($original);
});

test('_data_forget entire nested branch', function () {
    $array = ['user' => ['profile' => ['email' => 'test@example.com']]];
    _data_forget($array, 'user.profile');

    expect($array)->toBe(['user' => []]);
});

test('_data_forget with array key parameter', function () {
    $array = ['user' => ['name' => 'John', 'age' => 30]];
    _data_forget($array, ['user', 'name']);

    expect($array)->toBe(['user' => ['age' => 30]]);
});

test('_data_forget single level key', function () {
    $array = ['key1' => 'value1', 'key2' => 'value2'];
    _data_forget($array, 'key1');

    expect($array)->toBe(['key2' => 'value2']);
});

test('_data_forget with nested wildcard', function () {
    $array = [
        'categories' => [
            ['products' => [['name' => 'A', 'stock' => 5], ['name' => 'B', 'stock' => 10]]],
            ['products' => [['name' => 'C', 'stock' => 3]]],
        ],
    ];

    _data_forget($array, 'categories.*.products.*.stock');

    expect($array)->toBe([
        'categories' => [
            ['products' => [['name' => 'A'], ['name' => 'B']]],
            ['products' => [['name' => 'C']]],
        ],
    ]);
});

// ============================================================================
// Edge Cases and Complex Scenarios
// ============================================================================

test('keys containing dots are treated as nested paths', function () {
    // With default separator, 'user.name' is treated as nested path user->name, not literal key
    $array = ['user' => ['name' => 'John']];

    expect(_data_get($array, 'user.name'))->toBe('John');
    expect(_data_has($array, 'user.name'))->toBeTrue();
});

test('mixed arrays and objects', function () {
    $data = [
        'user' => (object) [
            'profile' => ['email' => 'john@example.com'],
        ],
    ];

    expect(_data_get($data, 'user.profile.email'))->toBe('john@example.com');
});

test('empty string values are preserved', function () {
    $array = ['user' => ['name' => '']];

    expect(_data_get($array, 'user.name', '.', 'default'))->toBe('');
    expect(_data_has($array, 'user.name'))->toBeTrue();
});

test('numeric keys with dot notation', function () {
    $array = [
        'users' => [
            0 => ['name' => 'John'],
            1 => ['name' => 'Jane'],
        ],
    ];

    expect(_data_get($array, 'users.0.name'))->toBe('John');
    expect(_data_get($array, 'users.1.name'))->toBe('Jane');
});

test('wildcard with empty arrays', function () {
    $array = ['users' => []];

    expect(_data_get($array, 'users.*.name'))->toBe([]);
});

test('closure as default value', function () {
    $array = ['user' => ['name' => 'John']];
    $default = fn () => 'default value';

    expect(_data_get($array, 'user.email', '.', $default))->toBe('default value');
});

test('boolean and zero values are preserved', function () {
    $array = [
        'active' => false,
        'count' => 0,
        'balance' => 0.0,
    ];

    expect(_data_get($array, 'active', true))->toBeFalse();
    expect(_data_get($array, 'count', 100))->toBe(0);
    expect(_data_get($array, 'balance', 100.0))->toBe(0.0);
});

test('Collection with wildcard', function () {
    $collection = collect([
        'items' => [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ],
    ]);

    expect(_data_get($collection, 'items.*.id'))->toBe([1, 2]);
});

test('_data_set with numeric string keys', function () {
    $array = [];
    _data_set($array, '0.1.2', 'value');

    expect($array['0']['1']['2'])->toBe('value');
});

test('_data_get with placeholder in middle of path', function () {
    $array = [
        'groups' => [
            'admins' => ['users' => [['name' => 'Admin1'], ['name' => 'Admin2']]],
            'users' => ['users' => [['name' => 'User1']]],
        ],
    ];

    $firstGroup = _data_get($array, 'groups.{first}.users.*.name');
    expect($firstGroup)->toBe(['Admin1', 'Admin2']);
});

test('_data_set on non-existent deeply nested path', function () {
    $array = [];
    _data_set($array, 'a.b.c.d.e.f.g', 'deep value');

    expect(_data_get($array, 'a.b.c.d.e.f.g'))->toBe('deep value');
});
