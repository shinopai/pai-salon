<x-app-layout>
  <section class="reservations-slots">
    <div class="reservations-slots__inner u-wrap">
      <div class="reservations-slots__heading">
        <p class="reservations-slots__eyebrow">WEB RESERVATION</p>

        <h1 class="reservations-slots__title">
          空き時間を選択
        </h1>

        <p class="reservations-slots__date">
          予約日：{{ $date->format('Y年m月d日') }}
        </p>
      </div>

      @if (empty($slots))
        <div class="reservations-slots__empty">
          <p class="reservations-slots__empty-title">
            予約可能な時間帯がありません。
          </p>

          <p class="reservations-slots__empty-description">
            別の日付を選択して、もう一度お試しください。
          </p>
        </div>
      @else
        <ul class="reservations-slots__list">
          @foreach ($slots as $slot)
            <li class="reservations-slots__item">
              <a href="{{ route('reservations.customer', [
                  'menu_id' => $menuId,
                  'staff_id' => $staffId,
                  'date' => $date->format('Y-m-d'),
                  'start_at' => $slot['start_at']->format('Y-m-d H:i:s'),
              ]) }}"
                class="reservations-slots__link u-flex">
                {{ $slot['start_at']->format('H:i') }}
              </a>
            </li>
          @endforeach
        </ul>
      @endif
    </div>
  </section>
</x-app-layout>
