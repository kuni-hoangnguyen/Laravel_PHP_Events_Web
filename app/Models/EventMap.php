<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMap extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'event_maps';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'map_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_id',
        'map_image_url',
        'note',
    ];

    /**
     * Disable timestamps since the table doesn't have them
     */
    public $timestamps = false;

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * EventMap thuộc về một event (Many-to-One)
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }
}
