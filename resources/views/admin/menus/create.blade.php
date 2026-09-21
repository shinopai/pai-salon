<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メニュー登録</title>
</head>
<body>
    <h1>メニュー登録</h1>

    <form action="{{ route('admin.menus.store') }}" method="POST">
        @csrf

        <div>
            <label for="name">メニュー名</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
            >
        </div>

        <div>
            <label for="duration">所要時間</label>
            <input
                type="number"
                id="duration"
                name="duration"
                value="{{ old('duration') }}"
                min="1"
            >
        </div>

        <button type="submit">登録</button>
    </form>
</body>
</html>
