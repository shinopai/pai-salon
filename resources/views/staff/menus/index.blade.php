<h1>対応可能メニュー</h1>

<ul>
  @foreach ($menus as $menu)
    <li>{{ $menu->name }}</li>
  @endforeach
</ul>
