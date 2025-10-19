<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceVariant;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
	public function run(): void
	{
		// Услуга 1
		$quad = Service::firstOrCreate(
			['name' => 'Поездка на квадроцикле'],
			['timezone' => 'Europe/Moscow', 'is_active' => 1]
		);

		ServiceVariant::updateOrCreate(
			['service_id' => $quad->id, 'name' => '30 минут'],
			['duration_min' => 30, 'padding_min' => 30, 'is_active' => 1]
		);
		ServiceVariant::updateOrCreate(
			['service_id' => $quad->id, 'name' => '60 минут'],
			['duration_min' => 60, 'padding_min' => 30, 'is_active' => 1]
		);

		// Услуга 2
		$enduro = Service::firstOrCreate(
			['name' => 'Тур на эндуро'],
			['timezone' => 'Europe/Moscow', 'is_active' => 1]
		);

		ServiceVariant::updateOrCreate(
			['service_id' => $enduro->id, 'name' => '60 минут'],
			['duration_min' => 60, 'padding_min' => 30, 'is_active' => 1]
		);
		ServiceVariant::updateOrCreate(
			['service_id' => $enduro->id, 'name' => '120 минут'],
			['duration_min' => 120, 'padding_min' => 30, 'is_active' => 1]
		);
	}
}
