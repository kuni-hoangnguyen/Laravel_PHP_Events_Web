<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'review_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'rating',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * Review thuộc về một event (Many-to-One)
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    /**
     * Review thuộc về một user (Many-to-One)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Review có thể bị report nhiều lần (One-to-Many)
     */
    public function reports()
    {
        return $this->hasMany(ReviewReport::class, 'review_id', 'review_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    /**
     * Scope: Lấy review theo rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope: Lấy review có rating cao (4-5 sao)
     */
    public function scopeHighRating($query)
    {
        return $query->where('rating', '>=', 4);
    }

    /**
     * Scope: Lấy review có comment
     */
    public function scopeWithComment($query)
    {
        return $query->whereNotNull('comment')
                    ->where('comment', '!=', '');
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Lấy mảng sao để hiển thị
     */
    public function getStarsArrayAttribute()
    {
        return array_fill(0, $this->rating, true) + array_fill($this->rating, 5 - $this->rating, false);
    }

    /**
     * Kiểm tra có comment không
     */
    public function hasComment()
    {
        return !empty($this->comment);
    }
}
