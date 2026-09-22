<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ・メニュー対応管理</title>
</head>
<body>
    <h1>スタッフ・メニュー対応管理</h1>

    <table>
        <thead>
            <tr>
                <th>スタッフ名</th>
                <th>対応可能メニュー</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>
                        @foreach ($staff->menus as $menu)
                            <span>{{ $menu->name }}</span>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
