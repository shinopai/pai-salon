<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>スタッフ・メニュー対応管理</title>
</head>

<body>
  <h1>スタッフ・メニュー対応管理</h1>

  @foreach ($staffs as $staff)
    <section>
      <h2>{{ $staff->name }}</h2>

      <form method="POST" action="{{ route('admin.staff-menus.update') }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="staff_id" value="{{ $staff->id }}">

        @foreach ($menus as $menu)
          <label>
            <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}" @checked($staff->menus->contains($menu->id))>
            {{ $menu->name }}
          </label>
          <br>
        @endforeach

        <button type="submit">保存</button>
      </form>
    </section>
  @endforeach
</body>

</html>
