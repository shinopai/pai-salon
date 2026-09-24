<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>予約一覧</title>
</head>

<body>
  <h1>予約一覧</h1>

  @foreach ($reservations as $reservation)
    <div>
      <p>{{ $reservation->reservation_number }}</p>
      <p>{{ $reservation->customer_name }}</p>
      <p>{{ $reservation->customer_email }}</p>
      <p>{{ $reservation->start_at }}</p>
      <p>{{ $reservation->end_at }}</p>
    </div>
  @endforeach
</body>

</html>
