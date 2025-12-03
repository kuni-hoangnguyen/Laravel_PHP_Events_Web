<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'favorites';

    /**
     * Indicates if the model should use timestamps.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'event_id',
    ];

    /**
     * Disable updated_at since table only has created_at
     */
    const UPDATED_AT = null;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     * Since favorites table doesn't have an id column, set to false
     */
    public $incrementing = false;

    /**
     * The primary key for the model.
     * Since favorites is a pivot table, we'll use composite key
     */
    protected $primaryKey = null;

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    /**
     * Favorite thuộc về một user (Many-to-One)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Favorite thuộc về một event (Many-to-One)
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    // ================================================================
    // HELPER METHODS
    // ================================================================

    /**
     * Toggle favorite cho user và event
     */
    public static function toggle($userId, $eventId)
    {
        $favorite = self::where('user_id', $userId)
                       ->where('event_id', $eventId)
                       ->first();

        if ($favorite) {
            // Use query builder to delete since there's no primary key
            self::where('user_id', $userId)
                ->where('event_id', $eventId)
                ->delete();
            return false; // Unfavorited
        } else {
            self::create([
                'user_id' => $userId,
                'event_id' => $eventId
            ]);
            return true; // Favorited
        }
    }

    /**
     * Kiểm tra user đã favorite event chưa
     */
    public static function isFavorited($userId, $eventId)
    {
        return self::where('user_id', $userId)
                  ->where('event_id', $eventId)
                  ->exists();
    }
}
