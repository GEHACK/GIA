<?php

return [
    'default'     => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST'),
            'port'      => env('DB_PORT', 3306),
            'database'  => env('DB_DATABASE'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'domjudge'  => [
            'driver'    => 'mysql',
            'host'      => env('DB_DJ_HOST', env('DB_HOST')),
            'port'      => env('DB_DJ_PORT', 3306),
            'database'  => env('DB_DJ_DATABASE', env('DB_DATABASE')),
            'username'  => env('DB_DJ_USERNAME', env('DB_USERNAME')),
            'password'  => env('DB_DJ_PASSWORD', env('DB_PASSWORD')),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
    'migrations'  => 'migrations',
];
