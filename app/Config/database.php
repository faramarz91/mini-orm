<?php
$root = dirname(__DIR__, 2);

$env = function (string $key, $default = null) {
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? $default;
    }
    return $val;
};

$driver = strtolower($env('DB_CONNECTION', 'sqlite'));

if ($driver === 'mysql') {
    $config = [
        'driver'    => 'mysql',
        'host'      => $env('DB_HOST', 'localhost'),
        'port'      => (int) $env('DB_PORT', 3306),
        'database'  => $env('DB_DATABASE', 'test'),
        'username'  => $env('DB_USERNAME', 'root'),
        'password'  => $env('DB_PASSWORD', ''),
        'charset'   => $env('DB_CHARSET', 'utf8mb4'),
        'collation' => $env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    ];
} else {
    $database = $env('DB_DATABASE', $root . '/database.sqlite');
    if ($database !== ':memory:' && !preg_match('#^(?:/|[A-Za-z]:\\\\)#', $database)) {
        $database = $root . '/' . ltrim($database, '/');
    }
    $config = [
        'driver'  => 'sqlite',
        'database'=> $database,
        'charset' => $env('DB_CHARSET', 'utf8mb4'),
    ];
}

return $config;
