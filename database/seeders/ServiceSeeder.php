<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Копируем сидерные картинки в storage/app/public/services (если хочешь, положи их в database/seeders/seed-images/)
        $this->ensureImage('quad.jpg');
        $this->ensureImage('enduro.jpg');

        // создаём/обновляем услуги c teaser и image_path
        $quad = Service::updateOrCreate(
            ['name' => 'Поездка на квадроцикле'],
            [
                'teaser'    => 'Экстремальная поездка по пересечённой местности на мощном квадроцикле. Отлично для выброса адреналина!',
                'image_path'=> '/storage/services/quad.jpg',
                'timezone'  => 'Europe/Moscow',
                'is_active' => 1,
            ]
        );

        $enduro = Service::updateOrCreate(
            ['name' => 'Тур на эндуро-мотоцикле'],
            [
                'teaser'    => 'Свобода движения и ветер! Маршруты различной сложности и продолжительности.',
                'image_path'=> '/storage/services/enduro.jpg',
                'timezone'  => 'Europe/Moscow',
                'is_active' => 1,
            ]
        );
    }

    private function ensureImage(string $file): void
    {
        // ожидаем исходники в database/seeders/seed-images/{file}
        $src = base_path('database/seeders/seed-images/'.$file);
        if (!is_file($src)) return;

        $dst = 'services/'.$file; // в диске public
        if (!Storage::disk('public')->exists($dst)) {
            Storage::disk('public')->put($dst, file_get_contents($src));
        }
    }
}
