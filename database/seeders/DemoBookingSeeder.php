<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceVariant;
use App\Models\Booking;
use Carbon\CarbonImmutable;

class DemoBookingSeeder extends Seeder
{
    public function run(): void
    {
        $tz = 'Europe/Moscow';
        $year = now($tz)->year;

        $put = function (string $serviceName, int $duration, string $dateYmd, string $timeHi) use ($tz) {
            $service = Service::where('name', $serviceName)->first();
            if (!$service) return;

            $variant = ServiceVariant::where('service_id', $service->id)
                ->where('duration_min', $duration)->first();
            if (!$variant) return;

            $startLocal = CarbonImmutable::createFromFormat('Y-m-d H:i', "$dateYmd $timeHi", $tz);
            $total = $variant->duration_min + $variant->padding_min;
            $endLocal = $startLocal->addMinutes($total);

            $exists = Booking::where('service_variant_id', $variant->id)
                ->where('start_at_utc', $startLocal->utc())
                ->exists();

            if (!$exists) {
                Booking::create([
                    'service_variant_id' => $variant->id,
                    'start_at_utc'       => $startLocal->utc(),
                    'end_at_utc'         => $endLocal->utc(),
                    'client_name'        => 'Тест',
                    'client_phone'       => '+7 900 000-00-00',
                    'status'             => 'active',
                ]);
            }
        };

        $d16 = sprintf('%04d-10-16', $year);
        $d17 = sprintf('%04d-10-17', $year);

        foreach (['13:00','16:00'] as $t) $put('Поездка на квадроцикле', 30, $d16, $t);
        foreach (['10:00']             as $t) $put('Поездка на квадроцикле', 60, $d16, $t);

        foreach (['10:00','11:00','13:00','18:00'] as $t) $put('Поездка на квадроцикле', 30, $d17, $t);

        foreach (['10:00','11:30','18:30'] as $t) $put('Тур на эндуро-мотоцикле', 60, $d16, $t);
        $put('Тур на эндуро-мотоцикле', 120, $d17, '14:00');
    }
}
