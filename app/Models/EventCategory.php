<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'event_categories';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'category_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_name',
        'description',
    ];

    /**
     * Disable timestamps since the table doesn't have them
     */
    public $timestamps = false;

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * Category có nhiều events (One-to-Many)
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'category_id', 'category_id');
    }
}
