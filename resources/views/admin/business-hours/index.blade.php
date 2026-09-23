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
      @foreach ($businessHours as $businessHour)
        <tr>
          <td>{{ $dayNames[$businessHour->day_of_week] }}</td>
          <td>
            <form method="POST" action="{{ route('admin.business-hours.update') }}">
              @csrf
              @method('PUT')

              <input type="hidden" name="day_of_week" value="{{ $businessHour->day_of_week }}">

              <input type="time" name="open_time" value="{{ $businessHour->open_time }}">

              ～

              <input type="time" name="close_time" value="{{ $businessHour->close_time }}">

              <button type="submit">更新</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>

</html>
