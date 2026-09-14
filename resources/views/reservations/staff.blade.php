<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>スタッフ選択</title>
</head>

<body>

  <h1>スタッフ選択</h1>

  <p>
    「{{ $menu->name }}」の担当スタッフを選択してください。
  </p>

  <ul>
    @foreach ($staffs as $staff)
      <li>
        <a href="{{ route('reservations.date', ['menu_id' => $menu->id, 'staff_id' => $staff->id]) }}">
          {{ $staff->name }}
        </a>
      </li>
    @endforeach
  </ul>

</body>

</html>
