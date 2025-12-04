<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'coupon_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'discount_percent',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_to',
        'status',
    ];

    /**
     * Disable timestamps since the table doesn't have them
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'discount_percent' => 'integer',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * Coupon được sử dụng trong nhiều tickets (One-to-Many)
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'coupon_id', 'coupon_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    /**
     * Scope: Lấy coupon active
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Lấy coupon còn hiệu lực
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where('valid_from', '<=', $now)
                    ->where('valid_to', '>=', $now)
                    ->where('status', 'active');
    }

    /**
     * Scope: Lấy coupon còn lượt sử dụng
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('used_count < max_uses');
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Kiểm tra coupon có thể sử dụng không
     */
    public function canUse()
    {
        return $this->status === 'active'
            && $this->valid_from <= now()
            && $this->valid_to >= now()
            && $this->used_count < $this->max_uses;
    }

    /**
     * Sử dụng coupon (tăng used_count)
     */
    public function use()
    {
        if ($this->canUse()) {
            $this->increment('used_count');
            
            if ($this->used_count >= $this->max_uses) {
                $this->update(['status' => 'expired']);
            }
            
            return true;
        }
        return false;
    }

    /**
     * Tính số tiền giảm giá
     */
    public function calculateDiscount($originalAmount)
    {
        return $originalAmount * ($this->discount_percent / 100);
    }

    /**
     * Lấy phần trăm đã sử dụng
     */
    public function getUsagePercentageAttribute()
    {
        if ($this->max_uses == 0) return 0;
        return ($this->used_count / $this->max_uses) * 100;
    }

    /**
     * Kiểm tra sắp hết hạn (trong 7 ngày)
     */
    public function isExpiringSoon()
    {
        return $this->valid_to <= now()->addDays(7);
    }
}
