<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceSchedule;
use App\Models\ServiceVariant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SlotService
{
    /**
     * Возвращает доступные старты и занятые интервалы для таймлайна.
     * Слоты считаются по УСЛУГЕ целиком (все варианты), но "вмещение"
     * проверяется для выбранного варианта (duration+padding).
     */
    public function getServiceSlots(int $serviceId, int $variantId, string $dateYmd): array
    {
        // 1) Проверим активность услуги и варианта
        /** @var Service $service */
        $service = Service::active()->with(['schedules'])->findOrFail($serviceId);
        /** @var ServiceVariant $variant */
        $variant = ServiceVariant::active()->where('service_id', $serviceId)->findOrFail($variantId);

        $tz = $service->timezone ?? 'Europe/Moscow';
        $day = CarbonImmutable::createFromFormat('Y-m-d', $dateYmd, $tz);
        $weekday = (int)$day->dayOfWeek; // 0..6

        // 2) График работы на день (если закрыто — пусто)
        /** @var ServiceSchedule|null $sch */
        $sch = $service->schedules()->where('weekday', $weekday)->first();
        if (!$sch || $sch->is_closed) {
            return ['available' => [], 'busy' => []];
        }

        // 3) Рабочие границы (локальное время)
        $workStart = $day->setTimeFromTimeString($sch->start_local); // 10:00
        $workEnd   = $day->setTimeFromTimeString($sch->end_local);   // 20:00

        // 4) Соберём все брони для услуги за день (все её варианты!)
        //    диапазон UTC для всей рабочей области
        $workStartUtc = $workStart->utc();
        $workEndUtc   = $workEnd->utc();

        // Список variant_id этой услуги
        $variantIds = ServiceVariant::where('service_id', $service->id)->pluck('id');

        $bookings = Booking::whereIn('service_variant_id', $variantIds)
            ->where('status', 'active')
            ->where('start_at_utc', '<', $workEndUtc)
            ->where('end_at_utc',   '>', $workStartUtc)
            ->get(['start_at_utc','end_at_utc']);

        // 5) Преобразуем брони в локальные интервалы для таймлайна
        $busy = [];
        foreach ($bookings as $b) {
            $start = CarbonImmutable::parse($b->start_at_utc)->tz($tz);
            $end   = CarbonImmutable::parse($b->end_at_utc)->tz($tz);

            // Ограничим рабочими границами на всякий
            if ($start->lt($workStart)) $start = $workStart;
            if ($end->gt($workEnd))     $end   = $workEnd;

            $busy[] = ['start' => $start->format('H:i'), 'end' => $end->format('H:i')];
        }

        // 6) Дискретизация по 30 минутам и проверка "вмещаемости"
        $totalMin = $variant->duration_min + $variant->padding_min; // например 60 + 30 = 90
        $stepMin  = 30;

        $available = [];
        // итерация по всем возможным стартам (локальное время)
        for ($t = $workStart; $t->addMinutes($totalMin)->lte($workEnd); $t = $t->addMinutes($stepMin)) {
            $end = $t->addMinutes($totalMin);

            // проверка пересечения по всем броням услуги (в UTC)
            $startUtc = $t->utc();
            $endUtc   = $end->utc();

            $overlapExists = Booking::whereIn('service_variant_id', $variantIds)
                ->where('status', 'active')
                ->where('start_at_utc', '<', $endUtc)
                ->where('end_at_utc',   '>', $startUtc)
                ->exists();

            if (!$overlapExists) {
                $available[] = $t->format('H:i'); // показываем как HH:MM
            }
        }

        return ['available' => $available, 'busy' => $busy];
    }
}
