<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>メニュー詳細</title>
</head>

<body>
  <h1>メニュー詳細</h1>

  <dl>
    <dt>メニュー名</dt>
    <dd>{{ $menu->name }}</dd>

    <dt>所要時間</dt>
    <dd>{{ $menu->duration }}分</dd>
  </dl>
</body>

</html>
