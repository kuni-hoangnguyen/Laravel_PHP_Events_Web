<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemReport extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'system_reports';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'report_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'generated_by',
        'title',
        'content',
        'report_type',
    ];

    /**
     * Disable updated_at timestamp
     */
    const UPDATED_AT = null;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'content' => 'array', // Nếu content là JSON
        'created_at' => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * SystemReport được tạo bởi user (Many-to-One)
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    /**
     * Scope: Lấy report theo loại
     */
    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope: Lấy report hàng ngày
     */
    public function scopeDaily($query)
    {
        return $query->where('report_type', 'daily');
    }

    /**
     * Scope: Lấy report hàng tuần
     */
    public function scopeWeekly($query)
    {
        return $query->where('report_type', 'weekly');
    }

    /**
     * Scope: Lấy report hàng tháng
     */
    public function scopeMonthly($query)
    {
        return $query->where('report_type', 'monthly');
    }

    /**
     * Scope: Lấy report custom
     */
    public function scopeCustom($query)
    {
        return $query->where('report_type', 'custom');
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Tạo báo cáo tự động
     */
    public static function generateReport($type, $generatedBy, $title, $content)
    {
        return self::create([
            'generated_by' => $generatedBy,
            'title' => $title,
            'content' => $content,
            'report_type' => $type,
        ]);
    }

    /**
     * Lấy tên loại báo cáo dễ hiểu
     */
    public function getTypeNameAttribute()
    {
        $types = [
            'daily' => 'Báo cáo hàng ngày',
            'weekly' => 'Báo cáo hàng tuần', 
            'monthly' => 'Báo cáo hàng tháng',
            'custom' => 'Báo cáo tùy chọn'
        ];

        return $types[$this->report_type] ?? $this->report_type;
    }

    /**
     * Kiểm tra content có phải JSON không
     */
    public function isJsonContent()
    {
        return is_array($this->content);
    }
}
