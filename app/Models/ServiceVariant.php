<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceVariant extends Model
{
    use SoftDeletes;

    protected $fillable = ['service_id','name','duration_min','padding_min','is_active'];

    public function scopeActive($q){ return $q->where('is_active', 1); }

    public function service(){
        return $this->belongsTo(Service::class);
    }

    public function bookings(){
        return $this->hasMany(Booking::class);
    }
}
