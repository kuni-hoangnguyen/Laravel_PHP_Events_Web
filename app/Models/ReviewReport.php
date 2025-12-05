<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewReport extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'review_reports';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'report_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'review_id',
        'reporter_id',
        'reason',
        'status',
    ];

    /**
     * Disable updated_at timestamp
     */
    const UPDATED_AT = null;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];


    /**
     * ReviewReport thuộc về một review (Many-to-One)
     */
    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id', 'review_id');
    }

    /**
     * ReviewReport được tạo bởi user (Many-to-One)
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id', 'user_id');
    }


    /**
     * Scope: Lấy report pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Lấy report đã review
     */
    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    /**
     * Scope: Lấy report đã resolve
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
