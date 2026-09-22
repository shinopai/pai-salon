<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>営業時間管理</title>
</head>

<body>
  <h1>営業時間管理</h1>

  <table>
    <thead>
      <tr>
        <th>曜日</th>
        <th>営業時間</th>
      </tr>
    </thead>
    <tbody>
      @php
        $dayNames = [
            0 => '日曜日',
            1 => '月曜日',
            2 => '火曜日',
            3 => '水曜日',
            4 => '木曜日',
            5 => '金曜日',
            6 => '土曜日',
        ];
      @endphp

      @foreach ($businessHours as $businessHour)
        <tr>
          <td>{{ $dayNames[$businessHour->day_of_week] }}</td>
          <td>
            @if ($businessHour->open_time === null && $businessHour->close_time === null)
              定休日
            @else
              {{ $businessHour->open_time }} ～ {{ $businessHour->close_time }}
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>

</html>
