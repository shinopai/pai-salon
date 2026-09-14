<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>顧客情報入力</title>
</head>

<body>

  <h1>顧客情報入力</h1>

  <p>
    メニュー：{{ $menu->name }}
  </p>

  <p>
    担当スタッフ：{{ $staff->name }}
  </p>

  <p>
    予約日：{{ $date->format('Y-m-d') }}
  </p>

  <p>
    予約時間：{{ $startAt->format('H:i') }}
  </p>

  <form method="GET" action="{{ route('reservations.confirm') }}">

    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
    <input type="hidden" name="staff_id" value="{{ $staff->id }}">
    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
    <input type="hidden" name="start_at" value="{{ $startAt->format('Y-m-d H:i:s') }}">

    <div>
      <label for="customer_name">お名前</label>
      <input type="text" id="customer_name" name="customer_name" maxlength="100" required>
    </div>

    <div>
      <label for="customer_email">メールアドレス</label>
      <input type="email" id="customer_email" name="customer_email" maxlength="255" required>
    </div>

    <button type="submit">確認画面へ</button>

  </form>

</body>

</html>
