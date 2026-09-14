<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>予約完了</title>
</head>

<body>

  <h1>予約完了</h1>

  <p>
    ご予約ありがとうございます。
  </p>

  <h2>予約内容</h2>

  <p>
    予約番号：{{ $reservation->reservation_number }}
  </p>

  <p>
    メニュー：{{ $reservation->menu->name }}
  </p>

  <p>
    担当スタッフ：{{ $reservation->staff->name }}
  </p>

  <p>
    予約日時：{{ $reservation->start_at->format('Y-m-d H:i') }}
  </p>

  <h2>顧客情報</h2>

  <p>
    お名前：{{ $reservation->customer_name }}
  </p>

  <p>
    メールアドレス：{{ $reservation->customer_email }}
  </p>

</body>

</html>
