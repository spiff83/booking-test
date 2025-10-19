<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

	protected $fillable = ['name','timezone','is_active','image_path','teaser'];

    public function scopeActive($q){ return $q->where('is_active', 1); }

    public function variants(){
        return $this->hasMany(ServiceVariant::class);
    }

    public function schedules(){
        return $this->hasMany(ServiceSchedule::class);
    }
}
