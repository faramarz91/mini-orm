##  Mini ORM — Lightweight Laravel-like ORM for PHP

**Mini ORM** is a lightweight, extensible, and framework-independent ORM library inspired by Laravel’s *Eloquent*.  
It provides a simple, fluent, and secure interface for database interactions using **pure PHP (PDO)** — built following **SOLID principles** and modern design patterns.

###  Key Features
-  **Fluent Query Builder** — chainable methods like `where()`, `orderBy()`, `limit()`, `get()`
-  **ActiveRecord-style Models** — easy CRUD via `create()`, `find()`, `update()`, `delete()`, `remove()`
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
```

## create a Model
```php
namespace App\Models;
use MiniORM\Models\Model;
class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'status'];
}
```

## use models with relations
```php
namespace App\Models;
use MiniORM\Models\Model;
class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['user_id', 'title', 'body'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

## query model
```php
use App\Models\Post;
$posts = Post::where('title', 'like', '%ORM%')->get();
```
```php
use App\Models\User;
$user = User::find(1);
$user->update(['email' => 'mfaramarz1991@gmail.com']);
```
```php
use App\Models\User;
User::delete(1);
// or
$user = User::find(2);
$user->remove();    
```



###  Installation
Install via Docker:

```bash
git clone faramarz91/mini-orm
cd mini-orm
docker-compose up -d --build
docker-compose exec app bash
# or
docker-compose run --rm -p 8000:8000 app php -S 0.0.0.0:8000 example.php -t /app
# goto http://localhost:8000 and see the example output
```     
Run tests:

```bash
vendor/bin/phpunit
```