<!DOCTYPE html>

<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>メニュー選択</title>
</head>

<body>
  <h1>メニュー選択</h1>

  <p>ご希望のメニューを選択してください。</p>

  <ul>
    @foreach ($menus as $menu)
      <li>
        <a href="{{ route('reservations.staff', ['menu_id' => $menu->id]) }}">
          {{ $menu->name }}（{{ $menu->duration }}分）
        </a>
      </li>
    @endforeach
  </ul>

</body>

</html>
