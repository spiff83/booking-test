<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\SlotService;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    /**
     * Возвращает JSON:
     *  - available: массив стартов "HH:MM", где помещается (duration+padding)
     *  - busy: массив занятых интервалов [['start'=>'HH:MM','end'=>'HH:MM'], ...] — для визуализации таймлайна
     */
    public function serviceSlots(Service $service, Request $request, SlotService $slots)
    {
        $request->validate([
            'date' => ['required','date_format:Y-m-d'],
            'variant_id' => ['required','integer'],
        ]);

        abort_unless($service->is_active, 404);

        $data = $slots->getServiceSlots(
            serviceId: $service->id,
            variantId: (int)$request->variant_id,
            dateYmd: $request->date
        );

        return response()->json($data);
    }
}
