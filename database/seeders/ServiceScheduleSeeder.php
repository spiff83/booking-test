<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceSchedule;

class ServiceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();
        foreach ($services as $service) {
            // Пн–Сб 10:00–20:00, Вс закрыто
            for ($w = 0; $w <= 6; $w++) {
                $closed = ($w === 0); // 0 = воскресенье
                ServiceSchedule::updateOrCreate(
                    ['service_id' => $service->id, 'weekday' => $w],
                    [
                        'is_closed'   => $closed,
                        'start_local' => $closed ? null : '10:00',
                        'end_local'   => $closed ? null : '20:00',
                    ]
                );
            }
        }
    }
}
