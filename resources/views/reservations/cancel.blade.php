<x-app-layout>
  <section class="reservations-cancel">
    <div class="reservations-cancel__inner u-wrap">
      <div class="reservations-cancel__heading">
        <p class="reservations-cancel__eyebrow">
          CANCELLATION
        </p>
        <h1 class="reservations-cancel__title">
          予約キャンセル確認
        </h1>
        <p class="reservations-cancel__description">
          以下の予約をキャンセルしますか？
        </p>
      </div>

      <section class="reservations-cancel__section">
        <h2 class="reservations-cancel__section-title">
          予約内容
        </h2>
        <dl class="reservations-cancel__summary">
          <div class="reservations-cancel__summary-item">
            <dt class="reservations-cancel__summary-label">
              予約番号
            </dt>
            <dd class="reservations-cancel__summary-value">
              {{ $reservation->reservation_number }}
            </dd>
          </div>
          <div class="reservations-cancel__summary-item">
            <dt class="reservations-cancel__summary-label">
              予約日時
            </dt>
            <dd class="reservations-cancel__summary-value">
              {{ $reservation->start_at->format('Y年n月j日 H:i') }}
            </dd>
          </div>
          <div class="reservations-cancel__summary-item">
            <dt class="reservations-cancel__summary-label">
              メニュー
            </dt>
            <dd class="reservations-cancel__summary-value">
              {{ $reservation->menu->name }}
            </dd>
          </div>
          <div class="reservations-cancel__summary-item">
            <dt class="reservations-cancel__summary-label">
              担当スタッフ
            </dt>
            <dd class="reservations-cancel__summary-value">
              {{ $reservation->staff->name }}
            </dd>
          </div>
        </dl>
      </section>

      <form class="reservations-cancel__form" method="POST"
        action="{{ route('reservations.cancel', [
            'reservation_number' => $reservation->reservation_number,
            'token' => $token,
        ]) }}">
        @csrf
        <button type="submit" class="reservations-cancel__button u-flex">
          予約をキャンセルする
        </button>
      </form>
    </div>
  </section>
</x-app-layout>
