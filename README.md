##  Mini ORM — Lightweight Laravel-like ORM for PHP

**Mini ORM** is a lightweight, extensible, and framework-independent ORM library inspired by Laravel’s *Eloquent*.  
It provides a simple, fluent, and secure interface for database interactions using **pure PHP (PDO)** — built following **SOLID principles** and modern design patterns.

###  Key Features
-  **Fluent Query Builder** — chainable methods like `where()`, `orderBy()`, `limit()`, `get()`
-  **ActiveRecord-style Models** — easy CRUD via `create()`, `find()`, `update()`, `delete()`
-  **SQL Injection Safe** — all queries are parameterized with PDO
-  **Relations Support** — `hasMany`, `belongsTo`
-  **Collections** — iterable, countable, and JSON-serializable results
-  **Multiple Drivers** — MySQL, SQLite (extendable for PostgreSQL, etc.)
-  **Unit Tested** — PHPUnit tests with in-memory SQLite
-  **Extensible & Clean** — easy to add new features or database drivers

###  Example Usage
```php
use MiniORM\Database\DatabaseManager;
use App\Models\User;

DatabaseManager::addConnection('default', [
    'driver' => 'sqlite',
    'database' => ':memory:',
]);

User::create(['name' => 'Ali', 'email' => 'ali@example.com', 'status' => 'active']);

$users = User::where('status', '=', 'active')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

foreach ($users as $user) {
    echo $user->name . PHP_EOL;
}
