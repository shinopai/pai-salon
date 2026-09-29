<x-app-layout>
  <section class="reservations">
    <div class="reservations__hero">
      <div class="reservations__hero-content u-wrap u-flex">
        <p class="reservations__brand">PaiSalon</p>

        <h1 class="reservations__title">
          あなたの毎日に、<br>
          ちょっとした癒しを。
        </h1>

        <p class="reservations__description">
          PaiSalonで、あなたに合ったサロン時間を。
        </p>

        <a href="{{ route('reservations.menu') }}" class="reservations__button u-flex">
          Web予約はこちら
        </a>
      </div>
    </div>
  </section>
</x-app-layout>
