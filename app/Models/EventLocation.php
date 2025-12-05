<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLocation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'event_locations';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'location_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'capacity',
    ];

    /**
     * Disable timestamps since the table doesn't have them
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'capacity' => 'integer',
    ];


    /**
     * Location có nhiều events (One-to-Many)
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'location_id', 'location_id');
    }


    /**
     * Lấy địa chỉ đầy đủ
     */
    public function getFullAddressAttribute()
    {
        return $this->address . ', ' . $this->city;
    }
}