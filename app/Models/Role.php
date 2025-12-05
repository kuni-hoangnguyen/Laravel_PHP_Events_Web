<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'role_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'role_name',
        'description',
    ];

    /**
     * Disable timestamps since the table doesn't have them
     */
    public $timestamps = false;


    /**
     * Role có nhiều users (Many-to-Many)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Accessor: Lấy name từ role_name
     */
    public function getNameAttribute()
    {
        return $this->role_name;
    }
}
