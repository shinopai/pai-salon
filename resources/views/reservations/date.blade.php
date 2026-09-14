<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>日付選択</title>
</head>

<body>

  <h1>日付選択</h1>

  <p>
    メニュー：{{ $menu->name }}
  </p>

  <p>
    担当スタッフ：{{ $staff->name }}
  </p>

  <form method="GET" action="{{ route('reservations.slots') }}">
    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
    <input type="hidden" name="staff_id" value="{{ $staff->id }}">

    <label for="date">予約日</label>
    <input type="date" id="date" name="date">

    <button type="submit">次へ</button>
  </form>

</body>

</html>
