<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>メニュー一覧</title>
</head>

<body>
  @if (session('error'))
    <p>{{ session('error') }}</p>
  @endif

  <h1>メニュー一覧</h1>

  <table>
    <thead>
      <tr>
        <th>メニュー名</th>
        <th>所要時間</th>
        <th>操作</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($menus as $menu)
        <tr>
          <td>{{ $menu->name }}</td>
          <td>{{ $menu->duration }}分</td>
          <td>
            <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}">
              @csrf
              @method('DELETE')
              <button type="submit">削除</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>

</html>
