<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>休業日管理</title>
</head>

<body>
  <h1>休業日管理</h1>

  <table>
    <thead>
      <tr>
        <th>休業日</th>
        <th>理由</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($holidays as $holiday)
        <tr>
          <td>{{ $holiday->date }}</td>
          <td>{{ $holiday->reason }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>

</html>
