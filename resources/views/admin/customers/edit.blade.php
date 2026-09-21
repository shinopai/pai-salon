<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>顧客編集</title>
</head>
<body>
    <h1>顧客編集</h1>

    <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
        @csrf
        @method('PUT')

        <div>
            <label for="name">名前</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ $customer->name }}"
            >
        </div>

        <div>
            <label for="email">メールアドレス</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ $customer->email }}"
            >
        </div>

        <button type="submit">更新</button>
    </form>
</body>
</html>
