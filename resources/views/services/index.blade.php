@extends('layouts.app')

@section('content')
  <div class="mb-6 flex items-center justify-between">
    <h2 class="text-2xl font-bold">Выберите услугу</h2>
    <a href="{{ route('admin.index') }}"
       class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
              bg-red-50 hover:bg-red-100 text-red-900 border-red-200">
      Админка →
    </a>
  </div>

  @if($services->isEmpty())
    <div class="rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3">
      Сейчас нет активных услуг для бронирования.
    </div>
  @else
    <div class="space-y-5">
      @foreach ($services as $service)
		@php
		  $image = $service->image_path ?: '/images/default.jpg';
		@endphp
        <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
          <div class="md:flex">
            <div class="md:w-1/3">
              <img src="{{ $image }}" alt="{{ $service->name }}"
                   class="w-full h-48 md:h-full object-cover">
            </div>
            <div class="md:w-2/3 p-5">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h3 class="text-xl font-semibold">{{ $service->name }}</h3>
                  <div class="text-xs text-gray-500">Часовой пояс: {{ $service->timezone ?? 'Europe/Moscow' }}</div>
                </div>
                <a href="{{ route('services.show', $service) }}"
                   class="shrink-0 inline-flex items-center rounded-lg px-3 py-2 text-sm text-white
                          bg-red-800 hover:bg-red-900">
                  Бронировать →
                </a>
              </div>

              @if($service->variants->isNotEmpty())
                <div class="mt-3">
                  <div class="text-sm text-gray-600 mb-1">Варианты длительности:</div>
                  <div class="flex flex-wrap gap-2">
                    @foreach($service->variants as $v)
                      <span class="inline-flex items-center rounded-full border px-3 py-1 text-sm bg-gray-50">
                        {{ $v->name }} ({{ $v->duration_min }} + {{ $v->padding_min }} мин)
                      </span>
                    @endforeach
                  </div>
                </div>
              @endif

              <p class="mt-4 text-sm text-gray-700">
				{{ $service->teaser ?: 'Готовы к приключению? Выберите длительность и забронируйте удобное время.' }}
              </p>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
@endsection
