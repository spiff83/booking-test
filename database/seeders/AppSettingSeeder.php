<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::set('hide_closed_days', 0); // по умолчанию показывать закрытые для бронирования дни
        AppSetting::set('weeks_depth', 2);      // 0..4 (тут 0 - только текущая, а например 2 это текущая + 2 недели вперёд)
		AppSetting::set('weeks_depth_past', 2); // 0..4 — листать назад (по умолчанию 2)
		\App\Models\AppSetting::set('weeks_depth_past', 2); // 0..4: сколько недель назад можно листать

    }
}
