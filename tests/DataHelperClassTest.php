<?php

use Abather\CustomDataHelpers\DataHelper;
use Illuminate\Support\Collection;

describe('DataHelper::get', function () {
    it('retrieves values with default dot separator', function () {
        $array = ['users' => ['name' => ['Taylor', 'Otwell']]];

        expect(DataHelper::get($array, 'users.name'))->toBe(['Taylor', 'Otwell']);
        expect(DataHelper::get($array, 'users.name.0'))->toBe('Taylor');
    });

    it('retrieves values with custom forward slash separator', function () {
        $array = ['users' => ['name' => ['Taylor', 'Otwell']]];

        expect(DataHelper::get($array, 'users/name', '/'))->toBe(['Taylor', 'Otwell']);
        expect(DataHelper::get($array, 'users/name/0', '/'))->toBe('Taylor');
    });

    it('retrieves values with custom arrow separator', function () {
        $array = ['users' => ['profile' => ['name' => 'John']]];

        expect(DataHelper::get($array, 'users->profile->name', '->'))->toBe('John');
    });

    it('handles keys with dots using custom separator', function () {
        $array = [
            'user.name' => 'John Doe',
            'user.email' => 'john@example.com',
            'config.app.name' => 'My App',
        ];

        // Using / separator, 'user.name' is treated as a literal key
        expect(DataHelper::get($array, 'user.name', '/'))->toBe('John Doe');
        expect(DataHelper::get($array, 'user.email', '|'))->toBe('john@example.com');
        expect(DataHelper::get($array, 'config.app.name', '->'))->toBe('My App');
    });

    it('returns default value when key not found', function () {
        $array = ['users' => ['name' => 'Taylor']];

        expect(DataHelper::get($array, 'users/email', '/', 'default@example.com'))->toBe('default@example.com');
        expect(DataHelper::get($array, 'users->age', '->', 25))->toBe(25);
    });

    it('supports callable default values', function () {
        $array = ['users' => ['name' => 'Taylor']];

        $result = DataHelper::get($array, 'users/email', '/', fn () => 'computed@example.com');
        expect($result)->toBe('computed@example.com');
    });

    it('retrieves values with wildcards and custom separator', function () {
        $array = [
            'users' => [
                ['name' => 'Taylor', 'email' => 'taylor@laravel.com'],
                ['name' => 'Abigail', 'email' => 'abigail@laravel.com'],
            ],
        ];

        expect(DataHelper::get($array, 'users/*/name', '/'))->toBe(['Taylor', 'Abigail']);
        expect(DataHelper::get($array, 'users->*->email', '->'))->toBe(['taylor@laravel.com', 'abigail@laravel.com']);
    });

    it('retrieves values with double nested wildcards', function () {
        $array = [
            'posts' => [
                ['comments' => [['author' => 'Taylor'], ['author' => 'Abigail']]],
                ['comments' => [['author' => 'Dries'], ['author' => 'Mohamed']]],
            ],
        ];

        $result = DataHelper::get($array, 'posts/*/comments/*/author', '/');
        expect($result)->toBe(['Taylor', 'Abigail', 'Dries', 'Mohamed']);
    });

    it('supports first and last placeholders with custom separator', function () {
        $array = [
            'users' => [
                ['name' => 'Taylor'],
                ['name' => 'Abigail'],
                ['name' => 'Dries'],
            ],
        ];

        expect(DataHelper::get($array, 'users/{first}/name', '/'))->toBe('Taylor');
        expect(DataHelper::get($array, 'users->{last}->name', '->'))->toBe('Dries');
    });

    it('supports multi-character separators', function () {
        $array = ['users' => ['profile' => ['name' => 'Taylor']]];

        expect(DataHelper::get($array, 'users::profile::name', '::'))->toBe('Taylor');
    });

    it('works with Laravel Collections', function () {
        $collection = new Collection([
            'users' => [
                ['name' => 'Taylor'],
                ['name' => 'Abigail'],
            ],
        ]);

        expect(DataHelper::get($collection, 'users/*/name', '/'))->toBe(['Taylor', 'Abigail']);
    });
});

describe('DataHelper::set', function () {
    it('sets values with custom forward slash separator', function () {
        $data = [];

        DataHelper::set($data, 'users/profile/name', 'Taylor', true, '/');
        expect($data)->toBe(['users' => ['profile' => ['name' => 'Taylor']]]);
    });

    it('sets values with custom arrow separator', function () {
        $data = [];

        DataHelper::set($data, 'products->desk->price', 200, true, '->');
        expect($data)->toBe(['products' => ['desk' => ['price' => 200]]]);
    });

    it('handles keys with dots using custom separator', function () {
        $data = [];

        // Using / separator, 'config.app.name' is treated as a literal key
        DataHelper::set($data, 'config.app.name', 'My App', true, '/');
        expect($data)->toBe(['config.app.name' => 'My App']);
    });

    it('overwrites existing values by default', function () {
        $data = ['users' => ['name' => 'Taylor']];

        DataHelper::set($data, 'users/name', 'Otwell', true, '/');
        expect($data['users']['name'])->toBe('Otwell');
    });

    it('respects overwrite flag', function () {
        $data = ['users' => ['name' => 'Taylor']];

        DataHelper::set($data, 'users/name', 'Otwell', false, '/');
        expect($data['users']['name'])->toBe('Taylor');

        DataHelper::set($data, 'users/email', 'taylor@laravel.com', false, '/');
        expect($data['users']['email'])->toBe('taylor@laravel.com');
    });

    it('sets values with wildcards and custom separator', function () {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 100],
                ['name' => 'Chair', 'price' => 50],
            ],
        ];

        DataHelper::set($data, 'products/*/price', 0, true, '/');
        expect($data['products'][0]['price'])->toBe(0);
        expect($data['products'][1]['price'])->toBe(0);
    });

    it('sets values with double nested wildcards', function () {
        $data = [
            'posts' => [
                ['comments' => [['score' => 0], ['score' => 0]]],
                ['comments' => [['score' => 0]]],
            ],
        ];

        DataHelper::set($data, 'posts/*/comments/*/score', 5, true, '/');
        expect($data['posts'][0]['comments'][0]['score'])->toBe(5);
        expect($data['posts'][0]['comments'][1]['score'])->toBe(5);
        expect($data['posts'][1]['comments'][0]['score'])->toBe(5);
    });
});

describe('DataHelper::has', function () {
    it('checks if key exists with custom separator', function () {
        $data = ['users' => ['profile' => ['name' => 'Taylor']]];

        expect(DataHelper::has($data, 'users/profile/name', '/'))->toBeTrue();
        expect(DataHelper::has($data, 'users->profile->name', '->'))->toBeTrue();
        expect(DataHelper::has($data, 'users|profile|email', '|'))->toBeFalse();
    });

    it('handles keys with dots using custom separator', function () {
        $data = [
            'user.name' => 'John Doe',
            'user.email' => 'john@example.com',
        ];

        // Using / separator, 'user.name' is treated as a literal key
        expect(DataHelper::has($data, 'user.name', '/'))->toBeTrue();
        expect(DataHelper::has($data, 'user.email', '->'))->toBeTrue();
        expect(DataHelper::has($data, 'user.phone', '|'))->toBeFalse();
    });

    it('returns false for null or empty key', function () {
        $data = ['users' => ['name' => 'Taylor']];

        expect(DataHelper::has($data, null))->toBeFalse();
        expect(DataHelper::has($data, []))->toBeFalse();
    });

    it('works with objects and custom separator', function () {
        $object = (object) [
            'user' => (object) [
                'profile' => (object) ['name' => 'Taylor'],
            ],
        ];

        expect(DataHelper::has($object, 'user/profile/name', '/'))->toBeTrue();
        expect(DataHelper::has($object, 'user->profile->email', '->'))->toBeFalse();
    });
});

describe('DataHelper::forget', function () {
    it('removes keys with custom separator', function () {
        $data = ['users' => ['name' => 'Taylor', 'email' => 'taylor@laravel.com']];

        DataHelper::forget($data, 'users/email', '/');
        expect($data)->toBe(['users' => ['name' => 'Taylor']]);
    });

    it('removes keys with arrow separator', function () {
        $data = ['products' => ['desk' => ['price' => 100, 'stock' => 50]]];

        DataHelper::forget($data, 'products->desk->stock', '->');
        expect($data)->toBe(['products' => ['desk' => ['price' => 100]]]);
    });

    it('handles keys with dots using custom separator', function () {
        $data = [
            'user.name' => 'John Doe',
            'user.email' => 'john@example.com',
        ];

        // Using / separator, 'user.email' is treated as a literal key
        DataHelper::forget($data, 'user.email', '/');
        expect($data)->toBe(['user.name' => 'John Doe']);
    });

    it('removes keys with wildcards and custom separator', function () {
        $data = [
            'products' => [
                ['name' => 'Desk', 'price' => 100, 'discount' => 10],
                ['name' => 'Chair', 'price' => 50, 'discount' => 5],
            ],
        ];

        DataHelper::forget($data, 'products/*/discount', '/');
        expect($data)->toBe([
            'products' => [
                ['name' => 'Desk', 'price' => 100],
                ['name' => 'Chair', 'price' => 50],
            ],
        ]);
    });

    it('removes keys with double nested wildcards', function () {
        $data = [
            'posts' => [
                ['comments' => [['text' => 'Great', 'score' => 5], ['text' => 'Nice', 'score' => 4]]],
                ['comments' => [['text' => 'Good', 'score' => 3]]],
            ],
        ];

        DataHelper::forget($data, 'posts/*/comments/*/score', '/');
        expect($data)->toBe([
            'posts' => [
                ['comments' => [['text' => 'Great'], ['text' => 'Nice']]],
                ['comments' => [['text' => 'Good']]],
            ],
        ]);
    });

    it('works with objects and custom separator', function () {
        $data = (object) ['user' => (object) ['name' => 'Taylor', 'email' => 'taylor@laravel.com']];

        DataHelper::forget($data, 'user/email', '/');
        expect(isset($data->user->email))->toBeFalse();
        expect($data->user->name)->toBe('Taylor');
    });
});

describe('Global helper functions', function () {
    it('provides _data_get function', function () {
        $array = ['users' => ['name' => 'John']];

        expect(_data_get($array, 'users/name', '/'))->toBe('John');
    });

    it('provides _data_set function', function () {
        $array = [];
        _data_set($array, 'users/name', 'John', true, '/');

        expect($array)->toBe(['users' => ['name' => 'John']]);
    });

    it('provides _data_has function', function () {
        $array = ['users' => ['name' => 'John']];

        expect(_data_has($array, 'users/name', '/'))->toBeTrue();
        expect(_data_has($array, 'users/email', '/'))->toBeFalse();
    });

    it('provides _data_forget function', function () {
        $array = ['users' => ['name' => 'John', 'email' => 'john@example.com']];
        _data_forget($array, 'users/email', '/');

        expect($array)->toBe(['users' => ['name' => 'John']]);
    });
});
