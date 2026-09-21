<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>メニュー一覧</title>
</head>

<body>
  <h1>メニュー一覧</h1>

  <table>
    <thead>
      <tr>
        <th>メニュー名</th>
        <th>所要時間</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($menus as $menu)
        <tr>
          <td>{{ $menu->name }}</td>
          <td>{{ $menu->duration }}分</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>

</html>
