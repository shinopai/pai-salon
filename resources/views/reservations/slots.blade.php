<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>空き時間選択</title>
</head>

<body>

  <h1>空き時間選択</h1>

  <p>
    予約日：{{ $date->format('Y-m-d') }}
  </p>

  @if (empty($slots))
    <p>予約可能な時間帯がありません。</p>
  @else
    <ul>
      @foreach ($slots as $slot)
        <li>
          <a
            href="{{ route('reservations.customer', [
                'menu_id' => $menuId,
                'staff_id' => $staffId,
                'date' => $date->format('Y-m-d'),
                'start_at' => $slot['start_at']->format('Y-m-d H:i:s'),
            ]) }}">
            {{ $slot['start_at']->format('H:i') }}
          </a>
        </li>
      @endforeach
    </ul>
  @endif

</body>

</html>
