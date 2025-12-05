<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTag extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'event_tags';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'tag_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tag_name',
    ];

    /**
     * Disable timestamps since the table doesn't have them
     */
    public $timestamps = false;


    /**
     * Tag có nhiều events (Many-to-Many)
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_tag_map', 'tag_id', 'event_id');
    }
}
