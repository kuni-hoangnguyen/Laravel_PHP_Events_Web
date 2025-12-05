<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'refund_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payment_id',
        'requester_id',
        'reason',
        'status',
        'processed_at',
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
        'processed_at' => 'datetime',
    ];


    /**
     * Refund thuộc về một payment (Many-to-One)
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    /**
     * Refund được yêu cầu bởi user (Many-to-One)
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id', 'user_id');
    }


    /**
     * Scope: Lấy refund pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Lấy refund approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Lấy refund rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }


    /**
     * Approve refund
     */
    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'processed_at' => now()
        ]);
    }

    /**
     * Reject refund
     */
    public function reject()
    {
        $this->update([
            'status' => 'rejected',
            'processed_at' => now()
        ]);
    }

    /**
     * Kiểm tra có thể process không
     */
    public function canProcess()
    {
        return $this->status === 'pending';
    }
}
