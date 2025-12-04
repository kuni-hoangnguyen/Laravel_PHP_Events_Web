<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'phone',
        'avatar_url',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'password',
        'remember_token',
    ];

    /**
     * Get the password for the user (Laravel auth expects 'password' field)
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Accessor: Lấy password từ password_hash field
     */
    public function getPasswordAttribute()
    {
        return $this->password_hash;
    }

    /**
     * Mutator: Set password_hash khi assign password
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = bcrypt($value);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * User có nhiều vai trò (Many-to-Many)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withPivot('assigned_at');
    }

    /**
     * User tổ chức nhiều sự kiện (One-to-Many)
     */
    public function organizedEvents()
    {
        return $this->hasMany(Event::class, 'organizer_id', 'user_id');
    }

    /**
     * User mua nhiều vé (One-to-Many)
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'attendee_id', 'user_id');
    }

    /**
     * User có nhiều đánh giá (One-to-Many)
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'user_id');
    }

    /**
     * User có nhiều sự kiện yêu thích (Many-to-Many)
     */
    public function favoriteEvents()
    {
        return $this->belongsToMany(Event::class, 'favorites', 'user_id', 'event_id')
            ->withPivot('created_at');
    }

    /**
     * User có nhiều favorites records (One-to-Many)
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id', 'user_id');
    }

    /**
     * User nhận nhiều thông báo (One-to-Many)
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Kiểm tra user có phải admin không
     */
    public function isAdmin()
    {
        return $this->roles()->where('role_name', 'admin')->exists();
    }

    /**
     * Kiểm tra user có phải organizer không
     */
    public function isOrganizer()
    {
        return $this->roles()->where('role_name', 'organizer')->exists();
    }

    /**
     * Kiểm tra user có phải attendee không
     */
    public function isAttendee()
    {
        return $this->roles()->where('role_name', 'attendee')->exists();
    }

    /**
     * Kiểm tra user có thể tạo sự kiện không
     */
    public function canCreateEvent()
    {
        return $this->roles()->whereIn('role_name', ['admin', 'organizer'])->exists();
    }

    /**
     * Lấy URL avatar đầy đủ
     */
    public function getFullAvatarUrlAttribute()
    {
        return $this->avatar_url ? asset($this->avatar_url) : asset('images/default-avatar.png');
    }

    /**
     * Lấy tên hiển thị
     */
    public function getDisplayNameAttribute()
    {
        return $this->full_name ?: 'User #'.$this->user_id;
    }

    /**
     * Accessor: Lấy name từ full_name
     */
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Mutator: Set full_name khi assign name
     */
    public function setNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
    }
}
