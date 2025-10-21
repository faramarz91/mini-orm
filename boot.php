<?php


$root = __DIR__;
$autoload = $root . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

if (class_exists(\Dotenv\Dotenv::class)) {
    \Dotenv\Dotenv::createImmutable($root)->safeLoad();
}

use MiniORM\Database\DatabaseManager;
// register an in-memory sqlite for demo/testing
try {
    DatabaseManager::addConnection('default', require 'app/Config/database.php');
} catch (\MiniORM\Exceptions\ORMException $e) {
    die("Database connection error: " . $e->getMessage());
}
