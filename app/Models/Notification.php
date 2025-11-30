<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'notification_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
    ];

    /**
     * Disable updated_at timestamp
     */
    const UPDATED_AT = null;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * Notification thuộc về một user (Many-to-One)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    /**
     * Scope: Lấy thông báo chưa đọc
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Lấy thông báo đã đọc
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope: Lấy theo loại thông báo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Lấy CSS class theo loại thông báo
     */
    public function getTypeClassAttribute()
    {
        return match($this->type) {
            'success' => 'alert-success',
            'warning' => 'alert-warning',
            'error' => 'alert-danger',
            default => 'alert-info'
        };
    }

    /**
     * Lấy icon theo loại thông báo
     */
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'success' => 'fas fa-check-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'error' => 'fas fa-times-circle',
            default => 'fas fa-info-circle'
        };
    }
}
