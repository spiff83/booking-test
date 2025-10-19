@extends('layouts.app')
@section('content')
  <div class="mb-5 flex items-center justify-between">
    <a href="{{ route('admin.index') }}" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
      bg-white hover:bg-gray-50 text-gray-800 border-gray-300">← К услугам</a>
    <div class="text-sm text-gray-600">{{ $bookings->total() }} записей</div>
  </div>

  <h2 class="text-2xl font-bold mb-4">История: {{ $service->name }}</h2>

  <div class="overflow-x-auto bg-white rounded-xl shadow">
    <table class="w-full text-left">
      <thead class="bg-gray-100 text-sm text-gray-700">
      <tr>
        <th class="px-3 py-2 border-b">Дата</th>
        <th class="px-3 py-2 border-b">Время</th>
        <th class="px-3 py-2 border-b">Длительность</th>
        <th class="px-3 py-2 border-b">Клиент</th>
        <th class="px-3 py-2 border-b">Телефон</th>
        <th class="px-3 py-2 border-b">Статус</th>
      </tr>
      </thead>
      <tbody>
      @forelse($bookings as $b)
        @php
          $start = \Carbon\Carbon::parse($b->start_at_utc)->tz($service->timezone ?? 'Europe/Moscow');
          $end   = \Carbon\Carbon::parse($b->end_at_utc)->tz($service->timezone ?? 'Europe/Moscow');
          $dur   = $start->diffInMinutes($end);
        @endphp
        <tr class="border-b">
          <td class="px-3 py-2">{{ $start->format('d.m.Y') }}</td>
          <td class="px-3 py-2">{{ $start->format('H:i') }}–{{ $end->format('H:i') }}</td>
          <td class="px-3 py-2">{{ $dur }} мин</td>
          <td class="px-3 py-2">{{ $b->client_name }}</td>
          <td class="px-3 py-2">{{ $b->client_phone }}</td>
          <td class="px-3 py-2">{{ $b->status }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="px-3 py-3 text-gray-500">Записей нет</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $bookings->links() }}
  </div>
@endsection
