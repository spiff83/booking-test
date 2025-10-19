<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ServiceVariant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request)
    {
        $variant = ServiceVariant::active()->with('service')->findOrFail($request->variant_id);
        if (!$variant->service || !$variant->service->is_active) {
            return back()->withErrors(['variant_id' => 'Вариант недоступен'])->withInput();
        }

        $tz = $variant->service->timezone ?? 'Europe/Moscow';

        // Собираем локальный (МСК) старт из date + start_local
        $startLocal = CarbonImmutable::createFromFormat('Y-m-d H:i', $request->date.' '.$request->start_local, $tz);
        $totalMin   = $variant->duration_min + $variant->padding_min;
        $endLocal   = $startLocal->addMinutes($totalMin);

        // Доп.проверка на попадание в рабочее окно (страховка)
        $weekday = $startLocal->dayOfWeek;
        $sch = $variant->service->schedules()->where('weekday', $weekday)->first();
        if (!$sch || $sch->is_closed) {
            return back()->withErrors(['start_local'=>'День недоступен'])->withInput();
        }
        $workStart = $startLocal->setTimeFromTimeString($sch->start_local);
        $workEnd   = $startLocal->setTimeFromTimeString($sch->end_local);
        if (!($startLocal->gte($workStart) && $endLocal->lte($workEnd))) {
            return back()->withErrors(['start_local'=>'Вне графика работы'])->withInput();
        }

        $startUtc = $startLocal->utc();
        $endUtc   = $endLocal->utc();

        try {
            DB::transaction(function() use ($variant, $startUtc, $endUtc, $request) {

                // Блокируем потенциальные пересечения
				$variantIds = \App\Models\ServiceVariant::where('service_id', $variant->service_id)->pluck('id');

				$exists = \App\Models\Booking::whereIn('service_variant_id', $variantIds)
					->where('status', 'active')
					->where('start_at_utc', '<', $endUtc)
					->where('end_at_utc', '>', $startUtc)
					->lockForUpdate()
					->exists();

                if ($exists) {
                    throw new \RuntimeException('Слот только что заняли, выберите другой.');
                }

                Booking::create([
                    'service_variant_id' => $variant->id,
                    'start_at_utc' => $startUtc,
                    'end_at_utc'   => $endUtc,
                    'client_name'  => $request->client_name,
                    'client_phone' => $request->client_phone,
                    'status'       => 'active',
                ]);
            });

        } catch (\RuntimeException $e) {
            return back()->withErrors(['slot'=>$e->getMessage()])->withInput();
        }

        return redirect()->route('home')->with('ok','Бронь успешно создана!');
    }
}
