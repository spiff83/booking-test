<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceVariant;

class ServiceVariantSeeder extends Seeder
{
    public function run(): void
    {
        // Квадроциклы
        if ($quad = Service::where('name', 'Поездка на квадроцикле')->first()) {
            $this->variant($quad->id, '30 минут', 30, 30, true);
            $this->variant($quad->id, '60 минут', 60, 30, true);
        }

        // Эндуро
        if ($enduro = Service::where('name', 'Тур на эндуро-мотоцикле')->first()) {
            $this->variant($enduro->id, '60 минут', 60, 30, true);
            $this->variant($enduro->id, '120 минут', 120, 30, true);
        }
    }

    private function variant(int $serviceId, string $name, int $duration, int $padding, bool $active): void
    {
        ServiceVariant::updateOrCreate(
            ['service_id' => $serviceId, 'name' => $name],
            ['duration_min' => $duration, 'padding_min' => $padding, 'is_active' => $active ? 1 : 0]
        );
    }
}
