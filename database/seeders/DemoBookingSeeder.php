<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\ServiceVariant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoBookingSeeder extends Seeder
{

	public function run(): void
	{
		$tz = 'Europe/Moscow';
		$year = now($tz)->year; // текущий год

		// Помощник: вставка, если нет
		$put = function (string $serviceName, int $duration, string $dateYmd, string $timeHi) use ($tz) {
			$service = \App\Models\Service::where('name', $serviceName)->first();
			if (!$service) return;

			$variant = \App\Models\ServiceVariant::where('service_id', $service->id)
				->where('duration_min', $duration)->first();
			if (!$variant) return;

			$startLocal = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $dateYmd.' '.$timeHi, $tz);
			$endLocal   = $startLocal->copy()->addMinutes($variant->duration_min + $variant->padding_min);

			$exists = \App\Models\Booking::where('service_variant_id', $variant->id)
				->where('start_at_utc', $startLocal->clone()->utc())->exists();

			if (!$exists) {
				\App\Models\Booking::create([
					'service_variant_id' => $variant->id,
					'start_at_utc'       => $startLocal->clone()->utc(),
					'end_at_utc'         => $endLocal->clone()->utc(),
					'client_name'        => 'Тест',
					'client_phone'       => '+7 900 000-00-00',
					'status'             => 'active',
				]);
			}
		};

		// 16 и 17 октября текущего года
		$d16 = sprintf('%04d-10-16', $year);
		$d17 = sprintf('%04d-10-17', $year);

		// Поездка на квадроцикле 30 минут (16.10 в 13:00 и 16:00; 17.10 10:00, 11:00, 13:00, 18:00)
		foreach (['13:00','16:00'] as $t) $put('Поездка на квадроцикле', 30, $d16, $t);
		foreach (['10:00','11:00','13:00','18:00'] as $t) $put('Поездка на квадроцикле', 30, $d17, $t);

		// Поездка на квадроцикле 60 минут (16.10 в 10:00)
		$put('Поездка на квадроцикле', 60, $d16, '10:00');

		// Тур на эндуро 60 минут (16.10 в 10:00, 11:30, 18:30)
		foreach (['10:00','11:30','18:30'] as $t) $put('Тур на эндуро', 60, $d16, $t);

		// Тур на эндуро 120 минут (17.10 14:00)
		$put('Тур на эндуро', 120, $d17, '14:00');
	}

}
