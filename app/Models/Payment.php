<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payment'; // Laravel's default pluralization
    protected $primaryKey = 'payment_id'; // Assuming this is your primary key for payments

    // Laravel automatically handles 'created_at' and 'updated_at' if timestamps are true
    public $timestamps = false; // Changed to false, as 'payment_date' is explicitly provided

    protected $fillable = [
        'owner_id',
        'payment_mode',         // Ensure this is in your fillable array
        'payment_acc_number',   // Ensure this is in your fillable array
        'payment_amount',       // Ensure this is in your fillable array
        'payment_date',         // Ensure this is in your fillable array
        // 'status',             // Uncomment and ensure this is in your payments table if used
        // 'transaction_id',     // Uncomment and ensure this is in your payments table if used
    ];

    /**
     * Get the subscription that owns the payment.
     */


    /**
     * Get the owner that made the payment.
     */
    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id', 'owner_id'); // Assuming 'owner_id' is primary key in Owner
    }
}