<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceSchedule;
use Illuminate\Database\Seeder;

class ServiceScheduleSeeder extends Seeder
{
	public function run(): void
	{
		$services = Service::all();

		foreach ($services as $s) {
			// СNUMнить старое расписание, чтобы не ловить UNIQUE-конфликт
			ServiceSchedule::where('service_id', $s->id)->delete();

			for ($w = 0; $w <= 6; $w++) {
				ServiceSchedule::create([
					'service_id'  => $s->id,
					'weekday'     => $w,
					'start_local' => $w === 0 ? null : '10:00:00', // Вс закрыто
					'end_local'   => $w === 0 ? null : '20:00:00',
					'is_closed'   => $w === 0,
				]);
			}
		}
	}
}
