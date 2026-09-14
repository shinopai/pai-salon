<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>予約確認</title>
</head>

<body>

  <h1>予約確認</h1>

  <h2>予約内容</h2>

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

  <h2>顧客情報</h2>

  <p>
    お名前：{{ $customerName }}
  </p>

  <p>
    メールアドレス：{{ $customerEmail }}
  </p>

  <form method="POST" action="{{ route('reservations.store') }}">
    @csrf

    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
    <input type="hidden" name="staff_id" value="{{ $staff->id }}">
    <input type="hidden" name="start_at" value="{{ $startAt->format('Y-m-d H:i:s') }}">
    <input type="hidden" name="customer_name" value="{{ $customerName }}">
    <input type="hidden" name="customer_email" value="{{ $customerEmail }}">

    <button type="submit">予約を確定する</button>
  </form>

</body>

</html>
