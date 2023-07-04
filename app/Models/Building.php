<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function units()
    {
        return $this->hasMany(Unit::class, 'building_id', 'id');
    }
    public function unit_types()
    {
        return $this->hasMany(UnitType::class, 'building_id', 'id');
    }
    protected $fillable = [
        'name',
        'floor_qty',
        'units_qty',
    ];
}
