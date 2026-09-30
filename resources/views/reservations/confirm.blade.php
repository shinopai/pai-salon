<x-app-layout>
  <section class="reservations-confirm">
    <div class="reservations-confirm__inner u-wrap">
      <div class="reservations-confirm__heading">
        <p class="reservations-confirm__eyebrow">WEB RESERVATION</p>

        <h1 class="reservations-confirm__title">
          予約確認
        </h1>

        <p class="reservations-confirm__description">
          以下の内容をご確認のうえ、予約を確定してください。
        </p>
      </div>

      <div class="reservations-confirm__section">
        <h2 class="reservations-confirm__section-title">
          予約内容
        </h2>

        <div class="reservations-confirm__summary">
          <div class="reservations-confirm__summary-item">
            <span class="reservations-confirm__summary-label">
              メニュー
            </span>
            <span class="reservations-confirm__summary-value">
              {{ $menu->name }}
            </span>
          </div>

          <div class="reservations-confirm__summary-item">
            <span class="reservations-confirm__summary-label">
              担当スタッフ
            </span>
            <span class="reservations-confirm__summary-value">
              {{ $staff->name }}
            </span>
          </div>

          <div class="reservations-confirm__summary-item">
            <span class="reservations-confirm__summary-label">
              予約日
            </span>
            <span class="reservations-confirm__summary-value">
              {{ $date->format('Y-m-d') }}
            </span>
          </div>

          <div class="reservations-confirm__summary-item">
            <span class="reservations-confirm__summary-label">
              予約時間
            </span>
            <span class="reservations-confirm__summary-value">
              {{ $startAt->format('H:i') }}
            </span>
          </div>
        </div>
      </div>

      <div class="reservations-confirm__section">
        <h2 class="reservations-confirm__section-title">
          顧客情報
        </h2>

        <div class="reservations-confirm__summary">
          <div class="reservations-confirm__summary-item">
            <span class="reservations-confirm__summary-label">
              お名前
            </span>
            <span class="reservations-confirm__summary-value">
              {{ $customerName }}
            </span>
          </div>

          <div class="reservations-confirm__summary-item">
            <span class="reservations-confirm__summary-label">
              メールアドレス
            </span>
            <span class="reservations-confirm__summary-value">
              {{ $customerEmail }}
            </span>
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('reservations.store') }}" class="reservations-confirm__form">
        @csrf

        <input type="hidden" name="menu_id" value="{{ $menu->id }}">

        <input type="hidden" name="staff_id" value="{{ $staff->id }}">

        <input type="hidden" name="start_at" value="{{ $startAt->format('Y-m-d H:i:s') }}">

        <input type="hidden" name="customer_name" value="{{ $customerName }}">

        <input type="hidden" name="customer_email" value="{{ $customerEmail }}">

        <button type="submit" class="reservations-confirm__button u-flex">
          予約を確定する
        </button>
      </form>
    </div>
  </section>
</x-app-layout>
