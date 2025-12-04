<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'payment_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ticket_id',
        'method_id',
        'amount',
        'status',
        'transaction_id',
        'paid_at',
    ];

    /**
     * Enable timestamps to track created_at for payment expiration
     */
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * Payment thuộc về một ticket (Many-to-One)
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    /**
     * Payment sử dụng một payment method (Many-to-One)
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id', 'method_id');
    }

    /**
     * Payment có thể có refund (One-to-Many)
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class, 'payment_id', 'payment_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    /**
     * Scope: Lấy payment thành công
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Lấy payment thất bại
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Lấy payment đã refund
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Kiểm tra payment thành công
     */
    public function isSuccess()
    {
        return $this->status === 'success';
    }

    /**
     * Kiểm tra có thể refund không
     */
    public function canRefund()
    {
        return $this->status === 'success' && $this->refunds()->where('status', 'approved')->count() === 0;
    }

    /**
     * Lấy định dạng tiền tệ
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', '.').' VND';
    }

    /**
     * Accessor: Lấy id từ payment_id
     */
    public function getIdAttribute()
    {
        return $this->payment_id;
    }
}
