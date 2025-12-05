<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'payment_methods';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'method_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Disable timestamps since the table doesn't have them
     */
    public $timestamps = false;


    /**
     * PaymentMethod được sử dụng trong nhiều payments (One-to-Many)
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'method_id', 'method_id');
    }

    /**
     * Accessor: Lấy id từ method_id
     */
    public function getIdAttribute()
    {
        return $this->method_id;
    }
}
