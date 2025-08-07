<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

use App\Models\Owner; // Import the Owner model

class Staff extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'staff'; // Assuming your staff table is named 'staff'
    protected $primaryKey = 'staff_id'; // Assuming staff_id is your primary key
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; // If you don't use Laravel's default timestamps

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'contact',// Assuming 'position' field exists
        'staff_pass', // Assuming 'password' is the column name for staff password
        'owner_id', // IMPORTANT: Ensure this foreign key exists in your 'staff' table
    ];

    protected $hidden = [
        'staff_pass',
        'verification_token',
    ];

    public function getAuthPassword()
    {
        return $this->staff_pass; // Return the value of your 'staff_pass' column
    }

    public function getAuthPasswordName()
    {
        return 'staff_pass'; // Explicitly return your password column name
    }
    public function owner()
    {
        // This defines a many-to-one relationship:
        // A staff member belongs to one owner.
        // 'owner_id' is the foreign key on the 'staff' table.
        // 'owner_id' (or 'id' if you named it 'id') is the primary key on the 'owners' table.
        return $this->belongsTo(Owner::class, 'owner_id', 'owner_id');
    }
}