<?php
namespace App\Models;

use MiniORM\Model\Model;
use MiniORM\Model\Relations\BelongsTo;

class Post extends Model
{
    protected string $table = 'posts';
    protected array $fillable = ['user_id', 'title', 'body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
