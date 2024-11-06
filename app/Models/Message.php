<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Message extends Model
{
    protected $fillable = ['user_id', 'chatroom_id', 'text', 'attachment_path'];
    public $incrementing = false;
    protected $keyType = 'string';

    public static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public function chatroom(): BelongsTo
    {
        return $this->belongsTo(Chatroom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
