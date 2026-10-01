<x-app-layout>
  <section class="staff-menus">
    <div class="u-wrap">
      <div class="staff-menus__inner">
        <h1 class="staff-menus__title">対応可能メニュー</h1>

        @if ($menus->isEmpty())
          <p class="staff-menus__empty">対応可能なメニューはありません。</p>
        @else
          <ul class="staff-menus__list">
            @foreach ($menus as $menu)
              <li class="staff-menus__item">
                <span class="staff-menus__name">{{ $menu->name }}</span>
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </section>
</x-app-layout>
