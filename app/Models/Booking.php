<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'service_variant_id','start_at_utc','end_at_utc',
        'client_name','client_phone','status'
    ];

    public function variant(){ return $this->belongsTo(ServiceVariant::class, 'service_variant_id'); }

    // Проверка пересечения (ручная — в транзакции)
}
