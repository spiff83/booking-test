@extends('layouts.app')
@section('content')
  <div class="mb-5 flex items-center justify-between">
    <a href="{{ route('admin.index') }}" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
      bg-white hover:bg-gray-50 text-gray-800 border-gray-300">← К услугам</a>
    <div class="text-sm text-gray-600">{{ $service->name }}</div>
  </div>

  <h2 class="text-2xl font-bold mb-4">Расписание услуги</h2>

  <form method="POST" action="{{ route('admin.services.schedule.update',$service->id) }}" class="space-y-3">
    @csrf
    @php
      $days = [
        1=>'Понедельник',2=>'Вторник',3=>'Среда',4=>'Четверг',5=>'Пятница',6=>'Суббота',0=>'Воскресенье'
      ];
    @endphp

    <div class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-100 text-sm text-gray-700">
          <tr>
            <th class="px-3 py-2 border-b">День</th>
            <th class="px-3 py-2 border-b">Закрыто</th>
            <th class="px-3 py-2 border-b">Начало</th>
            <th class="px-3 py-2 border-b">Конец</th>
          </tr>
        </thead>
        <tbody>
        @foreach($days as $w=>$label)
          @php $sch = $service->schedules->firstWhere('weekday',$w); @endphp
          <tr class="border-b">
            <td class="px-3 py-2">{{ $label }}</td>
            <td class="px-3 py-2">
              <input type="checkbox" name="day[{{ $w }}][is_closed]" {{ $sch && $sch->is_closed ? 'checked' : '' }}>
            </td>
            <td class="px-3 py-2">
              <input type="time" name="day[{{ $w }}][start_local]" value="{{ $sch? $sch->start_local : '10:00' }}" class="border rounded-lg px-2 py-1">
            </td>
            <td class="px-3 py-2">
              <input type="time" name="day[{{ $w }}][end_local]" value="{{ $sch? $sch->end_local : '20:00' }}" class="border rounded-lg px-2 py-1">
            </td>
            <input type="hidden" name="day[{{ $w }}][weekday]" value="{{ $w }}">
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    <button class="bg-red-800 hover:bg-red-900 text-white rounded-lg px-4 py-2">Сохранить расписание</button>
  </form>
@endsection
