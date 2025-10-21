<?php
namespace App\Models;
use MiniORM\Model\Model;
use MiniORM\Model\Relations\HasMany;

class User extends Model {

    protected string $table = 'users';
    protected array $fillable = ['name', 'email', 'status'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id');
    }

}