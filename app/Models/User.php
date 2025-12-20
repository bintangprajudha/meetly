<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Comment;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'banner',
        'bio',
        'location',
        'website',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get the avatar URL attribute
     * 
     * @return string|null
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            // Jika avatar sudah berupa full URL, return as is
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }
            // Jika avatar path dari storage
            return asset('storage/' . $this->avatar);
        }
        
        return null;
    }

    /**
     * Get display username (username or generated from name)
     * 
     * @return string
     */
    public function getDisplayUsernameAttribute(): string
    {
        return $this->username ?? '@' . str_replace(' ', '', strtolower($this->name));
    }

    /**
     * Get the banner URL attribute
     * 
     * @return string|null
     */
    public function getBannerUrlAttribute(): ?string
    {
        if ($this->banner) {
            if (str_starts_with($this->banner, 'http')) {
                return $this->banner;
            }
            return asset('storage/' . $this->banner);
        }
        
        return null;
    }
    // ==================== Relationships ====================

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function reposts(): HasMany
    {
        return $this->hasMany(Repost::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Users who follow this user
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_user_id', 'follower_user_id')
            ->withTimestamps();
    }

    /**
     * Users that this user follows
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_user_id', 'followed_user_id')
            ->withTimestamps();
    }

    /**
     * Check if this user is following another user
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('followed_user_id', $user->id)->exists();
    }

    /**
     * Follow another user
     */
    public function follow(User $user): void
    {
        if (!$this->isFollowing($user)) {
            $this->following()->attach($user->id);
        }
    }

    /**
     * Unfollow another user
     */
    public function unfollow(User $user): void
    {
        $this->following()->detach($user->id);
    }

    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'likes')->withTimestamps();
    }

    /**
     * Alias for likedPosts
     */
    public function likes(): BelongsToMany
    {
        return $this->likedPosts();
    }

    public function bookmarkedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'bookmarks')->withTimestamps();
    }

    /**
     * Alias for bookmarkedPosts
     */
    public function bookmarks(): BelongsToMany
    {
        return $this->bookmarkedPosts();
    }

    /**
     * Check if this user is liked by another user
     */
    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    // ==================== Scopes ====================

    /**
     * Scope to search users by name or username
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('username', 'like', "%{$search}%");
    }

    /**
     * Scope to get verified users (if you implement verification badge)
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }
}