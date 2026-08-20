<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'employee_code',
        'phone',
        'position',
        'status',
    ];

    /**
     * Employee belongs to a User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    // Di dalam model Employee
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
}