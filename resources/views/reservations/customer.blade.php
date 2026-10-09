<x-app-layout>
  <section class="reservations-customer">
    <div class="reservations-customer__inner u-wrap">
      <div class="reservations-customer__heading">
        <p class="reservations-customer__eyebrow">WEB RESERVATION</p>
        <h1 class="reservations-customer__title">
          顧客情報入力
        </h1>
        <p class="reservations-customer__description">
          ご予約に必要な情報を入力してください。
        </p>
      </div>

      <div class="reservations-customer__summary">
        <div class="reservations-customer__summary-item u-flex">
          <span class="reservations-customer__summary-label">
            メニュー
          </span>
          <span class="reservations-customer__summary-value">
            {{ $menu->name }}
          </span>
        </div>
        <div class="reservations-customer__summary-item u-flex">
          <span class="reservations-customer__summary-label">
            担当スタッフ
          </span>
          <span class="reservations-customer__summary-value">
            {{ $staff->name }}
          </span>
        </div>
        <div class="reservations-customer__summary-item u-flex">
          <span class="reservations-customer__summary-label">
            予約日
          </span>
          <span class="reservations-customer__summary-value">
            {{ $date->format('Y-m-d') }}
          </span>
        </div>
        <div class="reservations-customer__summary-item u-flex">
          <span class="reservations-customer__summary-label">
            予約時間
          </span>
          <span class="reservations-customer__summary-value">
            {{ $startAt->format('H:i') }}
          </span>
        </div>
      </div>

      <form method="GET" action="{{ route('reservations.confirm') }}" class="reservations-customer__form" novalidate>
        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
        <input type="hidden" name="staff_id" value="{{ $staff->id }}">
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
        <input type="hidden" name="start_at" value="{{ $startAt->format('Y-m-d H:i:s') }}">

        <div class="reservations-customer__field">
          <label for="customer_name" class="reservations-customer__label">
            お名前
          </label>
          @error('customer_name')
            <p class="u-error">{{ $message }}</p>
          @enderror
          <input type="text" id="customer_name" name="customer_name" maxlength="100" required
            class="reservations-customer__input">
        </div>

        <div class="reservations-customer__field">
          <label for="customer_email" class="reservations-customer__label">
            メールアドレス
          </label>
          @error('customer_email')
            <p class="u-error">{{ $message }}</p>
          @enderror
          <input type="email" id="customer_email" name="customer_email" maxlength="255" required
            class="reservations-customer__input">
        </div>

        <button type="submit" class="reservations-customer__button u-flex">
          確認画面へ
        </button>
      </form>
    </div>
  </section>
</x-app-layout>
