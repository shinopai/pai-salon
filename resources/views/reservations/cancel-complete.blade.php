<x-app-layout>
  <section class="reservations-cancel-complete">
    <div class="reservations-cancel-complete__inner u-wrap">
      <div class="reservations-cancel-complete__heading">
        <p class="reservations-cancel-complete__eyebrow">
          COMPLETED
        </p>
        <h1 class="reservations-cancel-complete__title">
          キャンセル完了
        </h1>
        <p class="reservations-cancel-complete__message">
          予約のキャンセルが完了しました。
        </p>
      </div>

      <div class="reservations-cancel-complete__number">
        <span class="reservations-cancel-complete__number-label">
          予約番号
        </span>
        <span class="reservations-cancel-complete__number-value">
          {{ $reservation->reservation_number }}
        </span>
      </div>

      <section class="reservations-cancel-complete__section">
        <h2 class="reservations-cancel-complete__section-title">
          キャンセルした予約
        </h2>
        <dl class="reservations-cancel-complete__summary">
          <div class="reservations-cancel-complete__summary-item">
            <dt class="reservations-cancel-complete__summary-label">
              予約日時
            </dt>
            <dd class="reservations-cancel-complete__summary-value">
              {{ $reservation->start_at->format('Y年n月j日 H:i') }}
            </dd>
          </div>
          <div class="reservations-cancel-complete__summary-item">
            <dt class="reservations-cancel-complete__summary-label">
              メニュー
            </dt>
            <dd class="reservations-cancel-complete__summary-value">
              {{ $reservation->menu->name }}
            </dd>
          </div>
          <div class="reservations-cancel-complete__summary-item">
            <dt class="reservations-cancel-complete__summary-label">
              担当スタッフ
            </dt>
            <dd class="reservations-cancel-complete__summary-value">
              {{ $reservation->staff->name }}
            </dd>
          </div>
        </dl>
      </section>
    </div>
  </section>
</x-app-layout>
