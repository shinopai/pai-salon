<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>予約キャンセル確認</title>
</head>

<body>
  <h1>予約キャンセル確認</h1>

  <p>以下の予約をキャンセルしますか？</p>

  <dl>
    <dt>予約番号</dt>
    <dd>{{ $reservation->reservation_number }}</dd>

    <dt>予約日時</dt>
    <dd>{{ $reservation->start_at }}</dd>

    <dt>メニュー</dt>
    <dd>{{ $reservation->menu->name }}</dd>

    <dt>担当スタッフ</dt>
    <dd>{{ $reservation->staff->name }}</dd>
  </dl>

  <form method="POST"
    action="{{ route('reservations.cancel', [
        'reservation_number' => $reservation->reservation_number,
        'token' => $token,
    ]) }}">
    @csrf

    <button type="submit">予約をキャンセルする</button>
  </form>
</body>

</html>
