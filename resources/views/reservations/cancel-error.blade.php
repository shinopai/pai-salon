<x-app-layout>
  <section class="reservations-cancel-error">
    <div class="reservations-cancel-error__inner u-wrap">
      <div class="reservations-cancel-error__content">
        <p class="reservations-cancel-error__eyebrow">
          CANCELLATION
        </p>
        <h1 class="reservations-cancel-error__title">
          キャンセルできません
        </h1>
        <p class="reservations-cancel-error__message">
          この予約はキャンセルできません。
        </p>
        <p class="reservations-cancel-error__description">
          予約情報をご確認のうえ、必要に応じてサロンへお問い合わせください。
        </p>
        <a href="{{ route('reservations.menu') }}" class="reservations-cancel-error__button u-flex">
          予約ページへ戻る
        </a>
      </div>
    </div>
  </section>
</x-app-layout>
