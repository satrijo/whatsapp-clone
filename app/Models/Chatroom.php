<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Chatroom extends Model
{
    protected $fillable = ['name', 'max_members'];

    public $incrementing = false;

    protected $keyType = 'string';

    public static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chatroom_users');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
