@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Админ-панель</h2>
    <a href="{{ route('home') }}" class="text-red-700 hover:underline">← На сайт</a>
  </div>

  {{-- Настройки --}}
  <div class="bg-white shadow rounded-lg mb-8 p-5">
    <h3 class="text-lg font-semibold mb-3">Настройки бронирования</h3>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
      @csrf
      <label class="flex items-center gap-3">
        <input type="checkbox" name="hide_closed_days"
               class="h-4 w-4"
               {{ $hide ? 'checked' : '' }}>
        <span>Скрывать дни, недоступные для бронирования (например, Воскресенье)</span>
      </label>

      <div>
        <label class="block text-sm text-gray-600 mb-1">
          Глубина бронирования в неделях (0–4)
        </label>
        <input type="number" min="0" max="4" name="weeks_depth" value="{{ $depth }}"
               class="border rounded-lg px-3 py-2 w-28">
        <div class="text-xs text-gray-500 mt-1">
          0 — только текущая неделя; 1 — текущая + 1 неделя и т.д. Максимум 4.
        </div>
      </div>

      <button class="inline-flex items-center bg-red-800 hover:bg-red-900 text-white rounded-lg px-4 py-2">
        Сохранить
      </button>
    </form>
  </div>

<a href="{{ route('admin.services.create') }}"
   class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
          bg-white hover:bg-gray-50 text-gray-800 border-gray-300">
  + Новая услуга
</a>

  {{-- Далее — уже существующий блок со списком услуг и вариантами --}}
  @foreach($services as $service)
    <div class="bg-white shadow rounded-lg mb-8 p-5">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h3 class="text-xl font-semibold">{{ $service->name }}</h3>
          <div class="text-sm text-gray-500">ID {{ $service->id }}</div>
        </div>
        <form method="POST" action="{{ route('admin.toggle.service', $service->id) }}">
          @csrf
          <button class="px-3 py-1 rounded-lg text-white {{ $service->is_active ? 'bg-green-700 hover:bg-green-800' : 'bg-gray-500 hover:bg-gray-600' }}">
            {{ $service->is_active ? 'Активна' : 'Неактивна' }}
          </button>
		</form>

		<div class="flex gap-2">
		  <a href="{{ route('admin.services.edit', $service->id) }}"
			 class="inline-flex items-center rounded-lg border px-3 py-1 text-sm
					bg-white hover:bg-gray-50 text-gray-800 border-gray-300">Редактировать</a>

 		  <a href="{{ route('admin.services.schedule', $service->id) }}"
		     class="inline-flex items-center rounded-lg border px-3 py-1 text-sm
				  bg-white hover:bg-gray-50 text-gray-800 border-gray-300">
		    Расписание
		  </a>


		  <a href="{{ route('admin.services.history', $service->id) }}"
			 class="inline-flex items-center rounded-lg border px-3 py-1 text-sm
					bg-red-50 hover:bg-red-100 text-red-900 border-red-200">История</a>

		  <form method="POST" action="{{ route('admin.services.delete',$service->id) }}"
				onsubmit="return confirm('Удалить услугу?');">
			@csrf
			<button class="inline-flex items-center rounded-lg border px-3 py-1 text-sm
						   bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-300">
			  Удалить
			</button>
		</div>


        </form>
      </div>

      @if ($service->variants->isEmpty())
        <div class="text-gray-500 italic">Нет вариантов.</div>
      @else
        <table class="w-full text-left border border-gray-200 rounded-lg overflow-hidden">
          <thead class="bg-gray-100 text-sm text-gray-700">
          <tr>
            <th class="px-3 py-2 border-b">ID</th>
            <th class="px-3 py-2 border-b">Название</th>
            <th class="px-3 py-2 border-b">Длительность</th>
            <th class="px-3 py-2 border-b">Буфер</th>
            <th class="px-3 py-2 border-b">Статус</th>
            <th class="px-3 py-2 border-b text-right">Действие</th>
          </tr>
          </thead>
          <tbody>
          @foreach ($service->variants as $v)
            <tr class="border-b hover:bg-gray-50">
              <td class="px-3 py-2">{{ $v->id }}</td>
              <td class="px-3 py-2">{{ $v->name }}</td>
              <td class="px-3 py-2">{{ $v->duration_min }} мин</td>
              <td class="px-3 py-2">{{ $v->padding_min }} мин</td>
              <td class="px-3 py-2">
                <span class="{{ $v->is_active ? 'text-green-700' : 'text-gray-500' }}">
                  {{ $v->is_active ? 'Активен' : 'Неактивен' }}
                </span>
              </td>
              <td class="px-3 py-2 text-right">
                <form method="POST" action="{{ route('admin.toggle.variant', $v->id) }}" class="inline">
                  @csrf
                  <button class="px-3 py-1 rounded-lg text-white {{ $v->is_active ? 'bg-green-700 hover:bg-green-800' : 'bg-gray-500 hover:bg-gray-600' }}">
                    {{ $v->is_active ? 'Выключить' : 'Включить' }}
                  </button>
                </form>

				<form method="POST" action="{{ route('admin.variants.delete', $v->id) }}"
					  onsubmit="return confirm('Удалить вариант?');" class="inline">
				  @csrf
				  <button class="px-3 py-1 rounded-lg border bg-white hover:bg-gray-50 text-gray-800">
					Удалить
				  </button>
				</form>

              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
		{{-- Добавить новый вариант длительности --}}
		<div class="mt-4 border-t pt-4">
		  <h4 class="font-semibold mb-2">Добавить вариант длительности</h4>
		  <form method="POST" action="{{ route('admin.variants.store', $service->id) }}" class="grid md:grid-cols-4 gap-3">
			@csrf
			<input type="text" name="name" placeholder="Название (напр. 90 минут)"
				   class="border rounded-lg px-3 py-2" required>
			<input type="number" name="duration_min" placeholder="Длительность, мин"
				   class="border rounded-lg px-3 py-2" min="15" max="600" required>
			<input type="number" name="padding_min" placeholder="Буфер, мин"
				   class="border rounded-lg px-3 py-2" min="0" max="180" value="30" required>
			<button class="bg-red-800 hover:bg-red-900 text-white rounded-lg px-4 py-2">Добавить</button>
		  </form>
		</div>

    </div>
  @endforeach
@endsection
