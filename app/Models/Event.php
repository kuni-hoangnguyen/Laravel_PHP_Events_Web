<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'event_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organizer_id',
        'category_id',
        'location_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'banner_url',
        'status',
        'max_attendees',
        'approved',
        'approved_at',
        'approved_by',
        'cancellation_requested',
        'cancellation_reason',
        'cancellation_requested_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'approved_at' => 'datetime',
        'approved' => 'integer', // -1 = rejected, 0 = pending, 1 = approved
        'cancellation_requested' => 'boolean',
        'cancellation_requested_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    /**
     * Event thuộc về một organizer (Many-to-One)
     */
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id', 'user_id');
    }

    /**
     * Event thuộc về một category (Many-to-One)
     */
    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id', 'category_id');
    }

    /**
     * Event thuộc về một location (Many-to-One)
     */
    public function location()
    {
        return $this->belongsTo(EventLocation::class, 'location_id', 'location_id');
    }

    /**
     * Event được approve bởi admin (Many-to-One)
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    /**
     * Event có nhiều loại vé (One-to-Many)
     */
    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class, 'event_id', 'event_id');
    }

    /**
     * Event có nhiều đánh giá (One-to-Many)
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'event_id', 'event_id');
    }

    /**
     * Event có nhiều tags (Many-to-Many)
     */
    public function tags()
    {
        return $this->belongsToMany(EventTag::class, 'event_tag_map', 'event_id', 'tag_id');
    }

    /**
     * Event có nhiều users yêu thích (Many-to-Many)
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'event_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Event có nhiều favorites records (One-to-Many)
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'event_id', 'event_id');
    }

    /**
     * Event có nhiều bản đồ (One-to-Many)
     */
    public function maps()
    {
        return $this->hasMany(EventMap::class, 'event_id', 'event_id');
    }


    /**
     * Scope: Chỉ lấy events đã được approve
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', 1);
    }

    /**
     * Scope: Lấy events chờ duyệt
     */
    public function scopePending($query)
    {
        return $query->where('approved', 0);
    }

    /**
     * Scope: Lấy events đã bị từ chối
     */
    public function scopeRejected($query)
    {
        return $query->where('approved', -1);
    }

    /**
     * Scope: Lấy events theo status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Lấy events sắp diễn ra
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
            ->where('start_time', '>', now());
    }


    /**
     * Kiểm tra event có còn vé không
     */
    public function hasAvailableTickets()
    {
        return $this->ticketTypes()
            ->where('is_active', true)
            ->where('remaining_quantity', '>', 0)
            ->exists();
    }

    /**
     * Lấy rating trung bình
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Đếm số người tham gia
     */
    public function getAttendeesCountAttribute()
    {
        return $this->ticketTypes()
            ->withSum('tickets', 'id')
            ->get()
            ->sum('tickets_sum_id') ?? 0;
    }

    /**
     * Accessor: Lấy id từ event_id
     */
    public function getIdAttribute()
    {
        return $this->event_id;
    }

    /**
     * Đếm số lượng thanh toán tiền mặt chờ xác nhận
     */
    public function getPendingCashPaymentsCountAttribute(): int
    {
        return \App\Models\Payment::with(['ticket.ticketType', 'paymentMethod'])
            ->whereHas('ticket.ticketType', function ($query) {
                $query->where('event_id', $this->event_id);
            })
            ->whereHas('paymentMethod', function ($query) {
                $query->where('name', 'Tiền mặt');
            })
            ->where('status', 'failed')
            ->whereHas('ticket', function ($query) {
                $query->where('payment_status', 'pending');
            })
            ->count();
    }
}
