<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonExpense extends Model
{
    use HasFactory;
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    protected $fillable = [
        'description',
        'amount',
        'currency_type'
    ];
}
