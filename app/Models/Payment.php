<?php

namespace App\Models;

use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'currency',
        'payment_type',
        'amount',
        'calculable_amount',
        'image',
        'description',
    ];
}
