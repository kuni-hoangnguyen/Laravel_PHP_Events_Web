<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'ticket_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ticket_type_id',
        'attendee_id',
        'purchase_time',
        'payment_status',
        'coupon_id',
        'qr_code',
    ];

    /**
     * Disable timestamps since we use custom purchase_time
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'purchase_time' => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * Ticket thuộc về một loại vé (Many-to-One)
     */
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id', 'ticket_type_id');
    }

    /**
     * Ticket thuộc về một attendee (Many-to-One)
     */
    public function attendee()
    {
        return $this->belongsTo(User::class, 'attendee_id', 'user_id');
    }

    /**
     * Ticket có thể sử dụng một coupon (Many-to-One)
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'coupon_id');
    }

    /**
     * Ticket có một payment (One-to-One)
     */
    public function payment()
    {
        return $this->hasOne(Payment::class, 'ticket_id', 'ticket_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    /**
     * Scope: Lấy vé đã thanh toán
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope: Lấy vé pending
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope: Lấy vé đã hủy
     */
    public function scopeCancelled($query)
    {
        return $query->where('payment_status', 'cancelled');
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Lấy event qua ticket type
     */
    public function getEventAttribute()
    {
        return $this->ticketType->event ?? null;
    }

    /**
     * Kiểm tra vé đã thanh toán chưa
     */
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Tạo QR code cho vé
     */
    public function generateQrCode()
    {
        if (! $this->qr_code) {
            $this->qr_code = 'TICKET_'.$this->ticket_id.'_'.uniqid();
            $this->save();
        }

        return $this->qr_code;
    }
}
