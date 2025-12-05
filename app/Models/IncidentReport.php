<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'incident_reports';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'incident_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_id',
        'reporter_id',
        'description',
        'status',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];


    /**
     * IncidentReport thuộc về một event (Many-to-One)
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    /**
     * IncidentReport được báo cáo bởi user (Many-to-One)
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id', 'user_id');
    }


    /**
     * Scope: Lấy incident đang mở
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope: Lấy incident đang xử lý
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope: Lấy incident đã resolve
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope: Lấy incident đã đóng
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }


    /**
     * Đánh dấu incident đã resolve
     */
    public function resolve()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);
    }

    /**
     * Đóng incident
     */
    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Kiểm tra có thể resolve không
     */
    public function canResolve()
    {
        return in_array($this->status, ['open', 'in_progress']);
    }
}