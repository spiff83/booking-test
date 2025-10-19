@extends('layouts.app')
@section('content')
  <div class="mb-5">
    <a href="{{ route('admin.index') }}" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
      bg-white hover:bg-gray-50 text-gray-800 border-gray-300">← Назад</a>
  </div>

  <h2 class="text-2xl font-bold mb-4">{{ $service ? 'Редактирование услуги' : 'Новая услуга' }}</h2>

  <form method="POST" enctype="multipart/form-data"
        action="{{ $service ? route('admin.services.update',$service->id) : route('admin.services.store') }}"
        class="space-y-4">
    @csrf
    <label class="block">
      <span class="text-sm text-gray-600">Название услуги</span>
      <input type="text" name="name" class="mt-1 w-full border rounded-lg px-3 py-2"
             value="{{ old('name', $service->name ?? '') }}" required>
    </label>

	<label class="block">
	  <span class="text-sm text-gray-600">Короткое описание (тизер)</span>
	  <textarea name="teaser" rows="4" class="mt-1 w-full border rounded-lg px-3 py-2"
				placeholder="Пара ярких предложений о впечатлениях...">{{ old('teaser', $service->teaser ?? '') }}</textarea>
	</label>

	<label class="inline-flex items-center gap-2">
	  <input type="checkbox" name="is_active"
			 {{ old('is_active', $service->is_active ?? false) ? 'checked' : '' }}>
	  <span>Услуга активна</span>
	</label>

    <label class="block">
      <span class="text-sm text-gray-600">Картинка (jpg/png)</span>
      <input type="file" name="image" accept="image/*" class="mt-1 w-full">
      @if($service && $service->image_path)
        <img src="{{ $service->image_path }}" alt="" class="mt-2 w-full max-w-md rounded-lg border">
      @endif
    </label>

    <button class="inline-flex items-center justify-center bg-red-800 hover:bg-red-900 text-white rounded-lg px-4 py-2">
      Сохранить
    </button>
  </form>
@endsection
