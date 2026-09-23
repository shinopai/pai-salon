<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>休業日登録</title>
</head>

<body>
  <h1>休業日登録</h1>

  <form method="POST" action="{{ route('admin.holidays.store') }}">
    @csrf

    <div>
      <label for="date">休業日</label>
      <input type="date" id="date" name="date" value="{{ old('date') }}">
    </div>

    <div>
      <label for="reason">理由</label>
      <input type="text" id="reason" name="reason" value="{{ old('reason') }}">
    </div>

    <button type="submit">登録</button>
  </form>
</body>

</html>
