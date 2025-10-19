<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceVariant;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\CarbonImmutable;

class ServiceController extends Controller
{
    public function index(): Response
    {
        $services = Service::where('is_active', 1)
            ->with(['variants' => fn($q) => $q->where('is_active', 1)])
            ->get(['id','name','image_path','teaser','timezone']);

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }

    public function show(Service $service, Request $request): Response
    {
        abort_unless($service->is_active, 404);

        $service->load(['variants' => fn($q) => $q->where('is_active', 1), 'schedules']);

        $hideClosed  = AppSetting::getBool('hide_closed_days', true);
        $weeksFuture = AppSetting::getInt('weeks_depth', 2);
        $weeksPast   = AppSetting::getInt('weeks_depth_past', 2);

        $tz = $service->timezone ?? 'Europe/Moscow';
        $today = CarbonImmutable::now($tz);
        $w = (int) $request->query('w', 0);
        $w = max(-$weeksPast, min($w, $weeksFuture));
        $monday = $today->startOfWeek(CarbonImmutable::MONDAY)->addWeeks($w);

        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $monday->addDays($i);
            $weekday = $d->dayOfWeek;
            $sch = $service->schedules->firstWhere('weekday', $weekday);
            $isClosed = !$sch || $sch->is_closed;
            if ($hideClosed && $isClosed) continue;

            $week[] = [
                'ymd'      => $d->format('Y-m-d'),
                'label_ru' => $d->locale('ru')->isoFormat('dd, D MMM'),
                'date_ru'  => $d->format('d.m.Y'),
                'weekday'  => $weekday,
                'closed'   => $isClosed,
            ];
        }

        return Inertia::render('Services/Show', [
            'service'     => $service->only('id','name','timezone','teaser','image_path'),
            'variants'    => $service->variants()->where('is_active', 1)->get(['id','name','duration_min','padding_min']),
            'week'        => $week,
            'w'           => $w,
            'prevEnabled' => $w > -$weeksPast,
            'nextEnabled' => $w <  $weeksFuture,
        ]);
    }
}
