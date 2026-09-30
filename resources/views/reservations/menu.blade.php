<x-app-layout>
  <section class="reservations-menu">
    <div class="reservations-menu__inner u-wrap">
      <div class="reservations-menu__heading">
        <p class="reservations-menu__eyebrow">WEB RESERVATION</p>

        <h1 class="reservations-menu__title">
          メニューを選択
        </h1>

        <p class="reservations-menu__description">
          ご希望のメニューを選択してください。
        </p>
      </div>

      <ul class="reservations-menu__list">
        @foreach ($menus as $menu)
          <li class="reservations-menu__item">
            <a href="{{ route('reservations.staff', ['menu_id' => $menu->id]) }}" class="reservations-menu__link u-flex">
              <span class="reservations-menu__name">
                {{ $menu->name }}
              </span>

              <span class="reservations-menu__duration">
                {{ $menu->duration }}分
              </span>
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  </section>
</x-app-layout>
