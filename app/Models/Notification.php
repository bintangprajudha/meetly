<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'notifiable_id',
        'notifiable_type',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Relasi ke user penerima notifikasi
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke user yang melakukan aksi
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    // Relasi polymorphic ke object terkait
    public function notifiable()
    {
        return $this->morphTo();
    }

    // Scope untuk notifikasi belum dibaca
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // Scope untuk notifikasi sudah dibaca
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
}
