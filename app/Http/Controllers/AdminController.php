<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Service;
use App\Models\ServiceVariant;
use App\Models\Booking;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $services = Service::with('variants')->orderBy('id')->get();

        $hide   = AppSetting::getBool('hide_closed_days', true);
        $depth = AppSetting::getInt('weeks_depth', 2);
        return view('admin.index', compact('services','hide','depth'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'hide_closed_days' => ['nullable','in:on,off,1,0'],
            'weeks_depth'      => ['required','integer','min:0','max:4'],
        ]);
        AppSetting::set('hide_closed_days', $request->boolean('hide_closed_days') ? 1 : 0);
        AppSetting::set('weeks_depth', (int)$request->weeks_depth);
        return back()->with('ok','Настройки сохранены.');
    }

    public function toggleService($id)
    {
        $service = Service::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();
        return back()->with('ok','Статус услуги обновлён.');
    }

    public function toggleVariant($id)
    {
        $variant = ServiceVariant::findOrFail($id);
        $variant->is_active = !$variant->is_active;
        $variant->save();
        return back()->with('ok','Статус варианта обновлён.');
    }

    // ---------- CRUD услуг ----------
    public function createService()
    {
        return view('admin.service_form', ['service'=>null]);
    }

	public function storeService(Request $r)
	{
		$data = $r->validate([
			'name'   => ['required','string','max:255'],
			'image'  => ['nullable','image','max:5120'],
			'teaser' => ['nullable','string','max:2000'],
			'is_active' => ['nullable','in:on,1,0'], // чекбокс
		]);

		$service = new \App\Models\Service([
			'name'      => $data['name'],
			'timezone'  => 'Europe/Moscow',
			'is_active' => $r->boolean('is_active') ? 1 : 0, // по умолчанию выключена
			'teaser'    => $data['teaser'] ?? null,
		]);

		if ($r->hasFile('image')) {
			$path = $r->file('image')->store('services', 'public');
			$service->image_path = '/storage/'.$path;
		}
		$service->save();

		return redirect()->route('admin.index')->with('ok','Услуга создана');
	}

    public function editService($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.service_form', compact('service'));
    }

	public function updateService($id, Request $r)
	{
		$service = \App\Models\Service::findOrFail($id);
		$data = $r->validate([
			'name'   => ['required','string','max:255'],
			'image'  => ['nullable','image','max:5120'],
			'teaser' => ['nullable','string','max:2000'],
			'is_active' => ['nullable','in:on,1,0'],
		]);

		$service->name   = $data['name'];
		$service->teaser = $data['teaser'] ?? null;
		$service->is_active = $r->boolean('is_active') ? 1 : 0;

		if ($r->hasFile('image')) {
			$path = $r->file('image')->store('services', 'public');
			$service->image_path = '/storage/'.$path;
		}

		$service->save();
		return redirect()->route('admin.index')->with('ok','Услуга обновлена');
	}

    public function deleteService($id)
    {
        $service = Service::with('variants')->findOrFail($id);

        // запрет удаления, если есть брони на любой вариант
        $hasBookings = ServiceVariant::where('service_id',$service->id)
            ->whereHas('bookings')
            ->exists();

        if ($hasBookings) {
            return back()->withErrors([
                'delete' => 'Нельзя удалить услугу: есть связанные брони. Отключите её (неактивна).'
            ]);
        }

        $service->delete(); // у нас SoftDeletes — безопасно
        return back()->with('ok','Услуга удалена');
    }

    // ---------- варианты ----------
    public function storeVariant($serviceId, Request $r)
    {
        $service = Service::findOrFail($serviceId);
        $data = $r->validate([
            'name'         => ['required','string','max:50'],
            'duration_min' => ['required','integer','min:15','max:600'],
            'padding_min'  => ['required','integer','min:0','max:180'],
        ]);

        ServiceVariant::create([
            'service_id'   => $service->id,
            'name'         => $data['name'],
            'duration_min' => $data['duration_min'],
            'padding_min'  => $data['padding_min'],
            'is_active'    => 1,
        ]);

        return back()->with('ok','Вариант добавлен');
    }

    public function deleteVariant($id)
    {
        $variant = ServiceVariant::findOrFail($id);

        if ($variant->bookings()->exists()) {
            return back()->withErrors([
                'delete' => 'Нельзя удалить вариант: есть связанные брони. Отключите вариант (неактивен).'
            ]);
        }

        $variant->delete();
        return back()->with('ok','Вариант удалён');
    }

    // ---------- история ----------
    public function history($id)
    {
        $service = Service::findOrFail($id);
        $variantIds = ServiceVariant::where('service_id',$service->id)->pluck('id');

        $bookings = Booking::whereIn('service_variant_id',$variantIds)
            ->orderByDesc('start_at_utc')
            ->paginate(15);

        return view('admin.history', compact('service','bookings'));
    }

	public function editSchedule($id)
	{
		$service = \App\Models\Service::with('schedules')->findOrFail($id);
		// гарантируем наличие записей на каждый день
		for ($w=0; $w<=6; $w++){
			if (!$service->schedules->firstWhere('weekday',$w)) {
				\App\Models\ServiceSchedule::create([
					'service_id'=>$service->id,
					'weekday'=>$w,
					'start_local'=> $w===0 ? null : '10:00:00',
					'end_local'  => $w===0 ? null : '20:00:00',
					'is_closed'  => $w===0,
				]);
			}
		}
		$service->load('schedules');
		return view('admin.schedule_form', compact('service'));
	}

	public function updateSchedule($id, \Illuminate\Http\Request $r)
	{
		$service = \App\Models\Service::findOrFail($id);
		$data = $r->validate([
			'day'   => ['required','array'],
			'day.*.weekday'    => ['required','integer','min:0','max:6'],
			'day.*.is_closed'  => ['nullable','in:on,1'],
			'day.*.start_local'=> ['nullable','date_format:H:i'],
			'day.*.end_local'  => ['nullable','date_format:H:i'],
		]);

		foreach ($data['day'] as $row) {
			$w = (int)$row['weekday'];
			$closed = !empty($row['is_closed']);
			$start = $closed ? null : ($row['start_local'] ?? '10:00');
			$end   = $closed ? null : ($row['end_local']   ?? '20:00');

			\App\Models\ServiceSchedule::updateOrCreate(
				['service_id'=>$service->id, 'weekday'=>$w],
				['is_closed'=>$closed, 'start_local'=>$start, 'end_local'=>$end]
			);
		}
		return back()->with('ok','Расписание обновлено');
	}

}
