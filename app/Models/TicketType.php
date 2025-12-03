<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ticket_types';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'ticket_type_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_id',
        'name',
        'price',
        'total_quantity',
        'remaining_quantity',
        'sale_start_time',
        'sale_end_time',
        'description',
        'is_active',
    ];

    /**
     * Disable timestamps since the table doesn't have them
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'total_quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'sale_start_time' => 'datetime',
        'sale_end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * TicketType thuộc về một event (Many-to-One)
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    /**
     * TicketType có nhiều tickets đã bán (One-to-Many)
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'ticket_type_id', 'ticket_type_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    /**
     * Scope: Chỉ lấy loại vé đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Lấy loại vé còn vé
     */
    public function scopeAvailable($query)
    {
        return $query->where('remaining_quantity', '>', 0);
    }

    /**
     * Scope: Lấy loại vé đang trong thời gian bán
     */
    public function scopeOnSale($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('sale_start_time')
              ->orWhere('sale_start_time', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('sale_end_time')
              ->orWhere('sale_end_time', '>=', $now);
        });
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Kiểm tra có thể mua vé không
     */
    public function canPurchase()
    {
        return $this->is_active 
            && $this->remaining_quantity > 0
            && $this->isOnSale();
    }

    /**
     * Kiểm tra có đang trong thời gian bán không
     */
    public function isOnSale()
    {
        $now = now();
        
        $startOk = is_null($this->sale_start_time) || $this->sale_start_time <= $now;
        $endOk = is_null($this->sale_end_time) || $this->sale_end_time >= $now;
        
        return $startOk && $endOk;
    }

    /**
     * Lấy số vé đã bán
     */
    public function getSoldQuantityAttribute()
    {
        return $this->total_quantity - $this->remaining_quantity;
    }

    /**
     * Lấy phần trăm đã bán
     */
    public function getSoldPercentageAttribute()
    {
        if ($this->total_quantity == 0) return 0;
        return ($this->sold_quantity / $this->total_quantity) * 100;
    }

    /**
     * Accessor: Lấy id từ ticket_type_id
     */
    public function getIdAttribute()
    {
        return $this->ticket_type_id;
    }
}
