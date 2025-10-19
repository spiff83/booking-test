@extends('layouts.app')

@section('content')
<div class="mb-5 flex items-center justify-between">
  <a href="{{ route('home') }}"
     class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
            bg-white hover:bg-gray-50 text-gray-800 border-gray-300">
    ← Ко всем услугам
  </a>
  <a href="{{ route('admin.index') }}"
     class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
            bg-red-50 hover:bg-red-100 text-red-900 border-red-200">
    Админка →
  </a>
</div>

  <h2 class="text-2xl font-bold mb-2">{{ $service->name }}</h2>

  {{-- Заметка о техническом интервале --}}
  <div class="mb-4 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3">
    К каждому бронированию автоматически добавляется технический перерыв 30 минут. 
    Учитывайте это при выборе времени.
  </div>

  {{-- Вариант длительности --}}
  <div class="mb-4">
    <label class="text-sm text-gray-600">Вариант:</label>
    <select id="variant" class="mt-1 border rounded-lg px-3 py-2">
      @foreach($service->variants as $v)
        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->duration_min }} + {{ $v->padding_min }} мин)</option>
      @endforeach
    </select>
  </div>

  {{-- Переключатель недель --}}
  <div class="mb-3 flex items-center gap-2">
	@php
	  // переменные приходят из контроллера: $prevEnabled, $nextEnabled, $w, $weeksMax
	@endphp
	<a href="{{ $prevEnabled ? route('services.show', [$service, 'w'=> $w-1]) : 'javascript:void(0)' }}"
	   class="px-3 py-2 rounded-lg border {{ $prevEnabled ? 'bg-white hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
	  ← Пред. неделя
	</a>
	<div class="text-sm text-gray-600">
	  Неделя: {{ $w === 0 ? 'текущая' : ($w < 0 ? 'прошлая ' . abs($w) : 'следующая ' . $w) }}
		(доступные недели бронирования: {{ $weeksMax }} назад … {{ $weeksMax }} вперед)
	</div>
	<a href="{{ $nextEnabled ? route('services.show', [$service, 'w'=> $w+1]) : 'javascript:void(0)' }}"
	   class="px-3 py-2 rounded-lg border {{ $nextEnabled ? 'bg-white hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
	  След. неделя →
	</a>
  </div>

{{-- Заголовок выбора дня --}}
<div class="mb-2 text-sm text-gray-600">Выберите день, когда хотите приехать</div>

  {{-- Календарь недели (с учётом hideClosedDays) --}}
	<div class="mb-4">
	  @if (empty($week))
		<div class="rounded-lg bg-gray-50 border px-4 py-3 text-gray-600">
		  В выбранную неделю нет доступных для бронирования дней.
		</div>
	  @else
		<div id="daysWrap" class="flex gap-2 flex-wrap">
		  @foreach($week as $d)
			<button
			  class="date-btn px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 transition"
			  data-date="{{ $d['ymd'] }}"
			  data-date-ru="{{ $d['date_ru'] }}"
			>
			  {{ $d['label_ru'] }}
			</button>
		  @endforeach
		</div>
	  @endif
	</div>

  {{-- Таймлайн + слоты --}}
  <div id="timeline" class="bg-white rounded-xl shadow p-4 mb-4 hidden">
    <div class="text-sm text-gray-600 mb-2">День: <span id="chosenDate"></span> (МСК)</div>

<div class="relative border-t border-b py-4">
  <div class="flex text-xs text-gray-500 justify-between mb-2">
    <span>10:00</span><span>12:00</span><span>14:00</span>
    <span>16:00</span><span>18:00</span><span>20:00</span>
  </div>

  {{-- Контейнер сетки + слой занятости --}}
  <div class="relative">
    {{-- Сетка из 20 сегментов по 30 мин --}}
    <div class="flex gap-0.5">
      @for ($i=0; $i<20; $i++)
        <div class="h-3 flex-1 bg-gray-100 rounded"></div>
      @endfor
    </div>

    {{-- Абсолютный слой сверху, без левых отступов --}}
    <div id="busyLayer" class="absolute inset-0 h-3 pointer-events-none"></div>
  </div>
</div>

{{-- Подпись над слотами --}}
<div class="mt-4 mb-1 text-sm text-gray-600">Доступные слоты начала бронирования</div>

<div class="mb-2 text-xs text-gray-500">
  Слот учитывает длительность и технический перерыв (+30 минут).
</div>

<div id="slots" class="flex flex-wrap gap-2"></div>
    <div class="mt-4">
      <div id="slots" class="flex flex-wrap gap-2"></div>
    </div>
  </div>

  {{-- Форма бронирования --}}
  <form id="bookForm" method="POST" action="{{ route('bookings.store') }}" class="space-y-3 hidden" onsubmit="return confirm('Подтвердить бронирование?')">
    @csrf
    <input type="hidden" name="variant_id" id="f_variant">
    <input type="hidden" name="date" id="f_date">
    <input type="hidden" name="start_local" id="f_start">

    <div class="grid grid-cols-2 gap-3">
      <label class="block">
        <span class="text-sm text-gray-600">Ваше имя</span>
        <input type="text" name="client_name" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </label>
      <label class="block">
        <span class="text-sm text-gray-600">Телефон</span>
        <input type="text" name="client_phone" class="mt-1 w-full border rounded-lg px-3 py-2" required>
      </label>
    </div>
    <button class="inline-flex items-center justify-center bg-red-800 hover:bg-red-900 text-white rounded-lg px-4 py-2">Забронировать</button>
  </form>

  <script>
    const serviceId = {{ $service->id }};
    const variantEl = document.getElementById('variant');
    const timeline  = document.getElementById('timeline');
    const chosenDate = document.getElementById('chosenDate');
    const busyLayer = document.getElementById('busyLayer');
    const slotsBox  = document.getElementById('slots');

    const form = document.getElementById('bookForm');
    const fVariant = document.getElementById('f_variant');
    const fDate    = document.getElementById('f_date');
    const fStart   = document.getElementById('f_start');

    let currentDate = null;

    document.querySelectorAll('.date-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        currentDate = btn.dataset.date;
        chosenDate.textContent = btn.dataset.dateRu; //currentDate;
        timeline.classList.remove('hidden');
        form.classList.add('hidden');
        await loadSlots();
      });
    });

    variantEl.addEventListener('change', () => { if (currentDate) loadSlots(); });

    async function loadSlots() {
      slotsBox.innerHTML = 'Загрузка...';
      busyLayer.innerHTML = '';
      const variantId = variantEl.value;
      fVariant.value = variantId;

      const url = `/services/${serviceId}/slots?date=${encodeURIComponent(currentDate)}&variant_id=${encodeURIComponent(variantId)}`;
      const res = await fetch(url);
      const data = await res.json();

      renderBusy(data.busy);
      renderAvailable(data.available);
    }

    function renderAvailable(arr){
      slotsBox.innerHTML = '';
      if (!arr || arr.length === 0){
        slotsBox.innerHTML = '<div class="text-gray-500">Нет доступных слотов</div>';
        return;
      }
      for (const s of arr){
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'px-3 py-2 rounded-lg border bg-white hover:bg-gray-50';
        btn.textContent = s;
        btn.addEventListener('click', () => {
          fDate.value = currentDate;
          fStart.value = s;
          form.classList.remove('hidden');
          form.scrollIntoView({behavior: 'smooth', block: 'start'});
        });
        slotsBox.appendChild(btn);
      }
    }

    function renderBusy(busy) {
      if (!busy || busy.length === 0) return;
      const totalMinutes = 10 * 60; // 10:00..20:00
      const toOffset = (hhmm) => {
        const [H,M] = hhmm.split(':').map(Number);
        return (H - 10) * 60 + M; // 10:00 => 0
      };
      busyLayer.innerHTML = '';
      for (const it of busy) {
        const startOff = Math.max(0, toOffset(it.start));
        const endOff   = Math.min(totalMinutes, toOffset(it.end));
        if (endOff <= startOff) continue;
        const leftPct  = (startOff / totalMinutes) * 100;
        const widthPct = ((endOff - startOff) / totalMinutes) * 100;

        const bar = document.createElement('div');
        bar.style.position = 'absolute';
        bar.style.left = leftPct + '%';
        bar.style.width = widthPct + '%';
        bar.style.height = '12px';
        bar.style.background = '#fecaca';
        bar.style.borderRadius = '6px';
        busyLayer.appendChild(bar);
      }
    }
  </script>
  
<script>
	// навешиваем клики и визуально выделяем выбранный день
	const dayButtons = document.querySelectorAll('.date-btn');
	function setActiveDay(btn){
	  dayButtons.forEach(b => b.classList.remove('bg-red-50','border-red-300','text-red-900'));
	  btn.classList.add('bg-red-50','border-red-300','text-red-900');
	}

	dayButtons.forEach(btn => {
	  btn.addEventListener('click', async () => {
		setActiveDay(btn);
		currentDate = btn.dataset.date;
		chosenDate.textContent = btn.dataset.dateRu;
		timeline.classList.remove('hidden');
		form.classList.add('hidden');
		await loadSlots();
	  });
	});
</script>
  
@endsection
