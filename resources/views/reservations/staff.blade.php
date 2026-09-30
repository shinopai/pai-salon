<x-app-layout>
  <section class="reservations-staff">
    <div class="reservations-staff__inner u-wrap">
      <div class="reservations-staff__heading">
        <p class="reservations-staff__eyebrow">WEB RESERVATION</p>

        <h1 class="reservations-staff__title">
          担当スタッフを選択
        </h1>

        <p class="reservations-staff__description">
          「{{ $menu->name }}」の担当スタッフを選択してください。
        </p>
      </div>

      <ul class="reservations-staff__list">
        @foreach ($staffs as $staff)
          <li class="reservations-staff__item">
            <a href="{{ route('reservations.date', ['menu_id' => $menu->id, 'staff_id' => $staff->id]) }}"
              class="reservations-staff__link u-flex">
              <span class="reservations-staff__name">
                {{ $staff->name }}
              </span>

              <span class="reservations-staff__arrow" aria-hidden="true">
                ›
              </span>
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  </section>
</x-app-layout>
