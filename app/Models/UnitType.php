<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    use HasFactory;
    public function building()
    {
        return $this->belongsTo(Building::class);
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
        'description',
        'aliquot',
        'size',
    ];
}
