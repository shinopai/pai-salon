<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>顧客一覧</title>
</head>

<body>
  <h1>顧客検索</h1>
  <form method="GET" action="{{ route('admin.customers.index') }}">
    <label for="keyword">キーワード</label>
    <input type="text" id="keyword" name="keyword" value="{{ request('keyword') }}">

    <button type="submit">検索</button>
  </form>
  <h1>顧客一覧</h1>

  @foreach ($customers as $customer)
    <div>
      <p>{{ $customer->name }}</p>
      <p>{{ $customer->email }}</p>
    </div>
  @endforeach
</body>

</html>
