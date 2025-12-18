<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id', 
        'receiver_id', 
        'message', 
        'is_read', 
        'images', 
        'videos', 
        'status', 
        'shared_post_id'
    ];

    protected $casts = [
        'images' => 'array',
        'videos' => 'array'
    ];

    protected $with = ['sharedPost']; 

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id')
            ->with(['user']); 
    }
}
