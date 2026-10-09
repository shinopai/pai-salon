<x-app-layout>
  <section class="reservations-complete">
    <div class="reservations-complete__inner u-wrap">
      <div class="reservations-complete__heading">
        <p class="reservations-complete__eyebrow">WEB RESERVATION</p>
        <h1 class="reservations-complete__title">
          予約完了
        </h1>
        <p class="reservations-complete__message">
          ご予約ありがとうございます。
        </p>
      </div>

      <div class="reservations-complete__number u-flex">
        <span class="reservations-complete__number-label">
          予約番号
        </span>
        <span class="reservations-complete__number-value">
          {{ $reservation->reservation_number }}
        </span>
      </div>

      <div class="reservations-complete__section">
        <h2 class="reservations-complete__section-title">
          予約内容
        </h2>
        <div class="reservations-complete__summary">
          <div class="reservations-complete__summary-item">
            <span class="reservations-complete__summary-label">
              メニュー
            </span>
            <span class="reservations-complete__summary-value">
              {{ $reservation->menu->name }}
            </span>
          </div>
          <div class="reservations-complete__summary-item">
            <span class="reservations-complete__summary-label">
              担当スタッフ
            </span>
            <span class="reservations-complete__summary-value">
              {{ $reservation->staff->name }}
            </span>
          </div>
          <div class="reservations-complete__summary-item">
            <span class="reservations-complete__summary-label">
              予約日時
            </span>
            <span class="reservations-complete__summary-value">
              {{ $reservation->start_at->format('Y-m-d H:i') }}
            </span>
          </div>
        </div>
      </div>

      <div class="reservations-complete__section">
        <h2 class="reservations-complete__section-title">
          顧客情報
        </h2>
        <div class="reservations-complete__summary">
          <div class="reservations-complete__summary-item">
            <span class="reservations-complete__summary-label">
              お名前
            </span>
            <span class="reservations-complete__summary-value">
              {{ $reservation->customer_name }}
            </span>
          </div>
          <div class="reservations-complete__summary-item">
            <span class="reservations-complete__summary-label">
              メールアドレス
            </span>
            <span class="reservations-complete__summary-value">
              {{ $reservation->customer_email }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
</x-app-layout>
