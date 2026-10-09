<x-app-layout>
  <section class="reservations-date">
    <div class="reservations-date__inner u-wrap">
      <div class="reservations-date__heading">
        <p class="reservations-date__eyebrow">WEB RESERVATION</p>
        <h1 class="reservations-date__title">
          予約日を選択
        </h1>
        <p class="reservations-date__description">
          ご希望の予約日を選択してください。
        </p>
      </div>

      <div class="reservations-date__summary">
        <div class="reservations-date__summary-item u-flex">
          <span class="reservations-date__summary-label">
            メニュー
          </span>
          <span class="reservations-date__summary-value">
            {{ $menu->name }}
          </span>
        </div>
        <div class="reservations-date__summary-item u-flex">
          <span class="reservations-date__summary-label">
            担当スタッフ
          </span>
          <span class="reservations-date__summary-value">
            {{ $staff->name }}
          </span>
        </div>
      </div>

      <form method="GET" action="{{ route('reservations.slots') }}" class="reservations-date__form">
        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
        <input type="hidden" name="staff_id" value="{{ $staff->id }}">

        <div class="reservations-date__field u-flex">
          <label for="date" class="reservations-date__label">
            予約日
          </label>
          <input type="date" id="date" name="date" class="reservations-date__input">
        </div>

        <button type="submit" class="reservations-date__button u-flex">
          次へ
        </button>
      </form>
    </div>
  </section>
</x-app-layout>
