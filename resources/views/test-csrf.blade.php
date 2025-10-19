<!doctype html><html><head>
<meta name="csrf-token" content="{{ csrf_token() }}">
</head><body>
<form method="POST" action="/bookings">
  @csrf
  <input type="hidden" name="variant_id" value="1">
  <input type="hidden" name="date" value="{{ now('Europe/Moscow')->format('Y-m-d') }}">
  <input type="hidden" name="start_local" value="10:00">
  <input name="client_name" value="Тест">
  <input name="client_phone" value="+7 900 000-00-00">
  <button>POST</button>
</form>
</body></html>
