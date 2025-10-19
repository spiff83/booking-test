<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSchedule extends Model
{
    protected $fillable = ['service_id','weekday','start_local','end_local','is_closed'];

    public function service(){ return $this->belongsTo(Service::class); }
}
