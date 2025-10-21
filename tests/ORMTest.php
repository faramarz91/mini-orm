<?php

namespace tests;

use App\Models\User;
use MiniORM\Collections\Collection;
use MiniORM\Database\DatabaseManager;
use MiniORM\Exceptions\ORMException;
use PHPUnit\Framework\TestCase;

final class ORMTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Set up SQLite in-memory connection
        DatabaseManager::addConnection('default', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $pdo = DatabaseManager::connection()->getPdo();

        // Create users and posts tables
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                email TEXT,
                status TEXT,
                age INTEGER,
                created_at TEXT
            );
        ");

        $pdo->exec("
            CREATE TABLE posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                title TEXT,
                body TEXT,
                created_at TEXT,
                FOREIGN KEY(user_id) REFERENCES users(id)
            );
        ");
    }

    public function testCreateUserAndFind()
    {
        $user = User::create([
            'name' => 'Ali',
            'email' => 'ali@example.com',
            'status' => 'active',
            'age' => 30
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->toArray()['id']);

        $found = User::find($user->toArray()['id']);
        $this->assertEquals('Ali', $found->name);
        $this->assertEquals('active', $found->status);
    }

    public function testAllMethodReturnsCollection()
    {
        User::create(['name' => 'Veli', 'email' => 'veli@example.com', 'status' => 'active', 'age' => 28]);
        $all = User::all();

        $this->assertInstanceOf(Collection::class, $all);
        $this->assertGreaterThanOrEqual(1, $all->count());
    }

    public function testWhereAndGetMethods()
    {
        $users = User::where('status', '=', 'active')->orderBy('age', 'desc')->get();

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertGreaterThanOrEqual(1, $users->count());

        $first = $users->first();
        $this->assertInstanceOf(User::class, $first);
    }

    public function testUpdateAndDelete()
    {
        $user = User::create(['name' => 'Temp', 'email' => 'temp@example.com', 'status' => 'inactive', 'age' => 40]);
        $id = $user->toArray()['id'];

        $updated = $user->update(['status' => 'active']);
        $this->assertTrue($updated);

        $found = User::find($id);
        $this->assertEquals('active', $found->status);

        $deleted = User::delete($id);
        $this->assertTrue($deleted);
        $this->assertNull(User::find($id));
    }

    public function testQueryBuilderCountAndExists()
    {
        $count = User::where('status', '=', 'active')->count();
        $this->assertIsInt($count);
        $this->assertTrue($count >= 1);

        $exists = User::where('status', '=', 'active')->exists();
        $this->assertTrue($exists);
    }

    public function testCollectionMethods()
    {
        $collection = User::all();

        $this->assertIsArray($collection->toArray());
        $this->assertJson($collection->toJson());

        $mapped = $collection->map(fn($user) => strtoupper($user->name));
        $this->assertInstanceOf(Collection::class, $mapped);
        $this->assertTrue(is_string($mapped->first()));
    }

    public function testHasManyAndBelongsToRelations()
    {
        $user = User::create([
            'name' => 'HasManyUser',
            'email' => 'hm@example.com',
            'status' => 'active',
            'age' => 25
        ]);

        $userId = $user->toArray()['id'];

        $pdo = DatabaseManager::connection()->getPdo();
        $pdo->prepare("INSERT INTO posts (user_id, title, body) VALUES (?, ?, ?)")
            ->execute([$userId, 'Post 1', 'Body 1']);
        $pdo->prepare("INSERT INTO posts (user_id, title, body) VALUES (?, ?, ?)")
            ->execute([$userId, 'Post 2', 'Body 2']);

        // hasMany
        $posts = $user->posts()->get();
        $this->assertInstanceOf(Collection::class, $posts);
        $this->assertGreaterThanOrEqual(2, $posts->count());

        // belongsTo
        $post = $posts->first();
        $owner = $post->user()->get();
        $this->assertInstanceOf(User::class, $owner);
        $this->assertEquals('HasManyUser', $owner->name);
    }

    public function testInvalidUpdateThrowsException()
    {
        $this->expectException(ORMException::class);
        $user = new User();
        $user->update(['name' => 'no_id']);
    }
}
