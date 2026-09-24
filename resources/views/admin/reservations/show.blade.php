<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>予約詳細</title>
</head>

<body>
  <h1>予約詳細</h1>

  <div>
    <p>{{ $reservation->reservation_number }}</p>
    <p>{{ $reservation->customer_name }}</p>
    <p>{{ $reservation->customer_email }}</p>
    <p>{{ $reservation->staff->name }}</p>
    <p>{{ $reservation->menu->name }}</p>
    <p>{{ $reservation->start_at }}</p>
    <p>{{ $reservation->end_at }}</p>
    <p>{{ $reservation->status->value }}</p>
  </div>
</body>

</html>
