<?php

require_once './boot.php';



use MiniORM\Database\DatabaseManager;
use App\Models\User;
// create schema quickly
$pdo = DatabaseManager::connection()->getPdo();
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    name TEXT, 
    email VARCHAR(255), 
    status VARCHAR(255), 
    age INTEGER, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// create
//$user = User::create(['name' => 'Ali', 'email' => 'ali@gmail.com', 'status' => 'active', 'age' => 30]);
//echo "Created user id: " . $user->toArray()['id'] . PHP_EOL;

// find
$user2 = User::find(2);
echo $user2->toJson() . PHP_EOL;

// update
//$user2->update(['status' => 'inactive']);

// delete
//User::find(4)->remove();
//User::delete(5);

$all = User::all();
echo '<pre>';
print_r($all->toArray());
// query builder chain
$users = User::where('status', '=', 'active')->orderBy('created_at', 'desc')->limit(10)->get();
echo "Count: " . $users->count() . PHP_EOL;

$user = User::where('email','like','%gmail.com%')
    ->orderBy('id','desc')
    ->limit(10)
    ->get();

echo $user->toJson() . PHP_EOL;
echo '</pre>';
