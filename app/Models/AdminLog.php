<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'admin_logs';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'log_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'admin_id',
        'action',
        'target_table',
        'target_id',
        'old_values',
        'new_values',
        'ip_address',
    ];

    /**
     * Disable updated_at timestamp
     */
    const UPDATED_AT = null;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * AdminLog thuộc về một admin (Many-to-One)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    /**
     * Scope: Lấy log theo action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Lấy log theo bảng target
     */
    public function scopeByTable($query, $table)
    {
        return $query->where('target_table', $table);
    }

    /**
     * Scope: Lấy log theo admin
     */
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Tạo log cho action
     */
    public static function logAction($adminId, $action, $targetTable = null, $targetId = null, $oldValues = null, $newValues = null)
    {
        return self::create([
            'admin_id' => $adminId,
            'action' => $action,
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Lấy mô tả action dễ hiểu
     */
    public function getActionDescriptionAttribute()
    {
        $actions = [
            'create_event' => 'Tạo sự kiện mới',
            'approve_event' => 'Duyệt sự kiện',
            'reject_event' => 'Từ chối sự kiện',
            'delete_event' => 'Xóa sự kiện',
            'create_user' => 'Tạo người dùng',
            'ban_user' => 'Khóa người dùng',
            'unban_user' => 'Mở khóa người dùng',
            'process_refund' => 'Xử lý hoàn tiền',
        ];

        return $actions[$this->action] ?? $this->action;
    }
}
