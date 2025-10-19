<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Бронирование</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Tailwind через CDN для скорости -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="max-w-5xl mx-auto p-4">
    <header class="mb-6">
      <h1 class="text-2xl font-bold">Онлайн-бронирование</h1>
      @if (session('ok'))
        <div class="mt-3 rounded-lg bg-green-100 text-green-800 px-4 py-2">{{ session('ok') }}</div>
      @endif
      @if ($errors->any())
        <div class="mt-3 rounded-lg bg-red-100 text-red-800 px-4 py-2">
          @foreach ($errors->all() as $e)
            <div>• {{ $e }}</div>
          @endforeach
        </div>
      @endif
    </header>

    <main>
      @yield('content')
    </main>
  </div>
</body>
</html>
