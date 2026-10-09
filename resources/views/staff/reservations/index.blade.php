<x-app-layout>
  <section class="staff-reservations">
    <div class="u-wrap">
      <div class="staff-reservations__inner">
        <h1 class="staff-reservations__title">スタッフ予約一覧</h1>

        @if ($reservations->isEmpty())
          <div class="staff-reservations__empty">
            <p class="staff-reservations__empty-text">
              現在、予約はありません。
            </p>
          </div>
        @else
          <div class="staff-reservations__list">
            @foreach ($reservations as $reservation)
              <article class="staff-reservations__item">
                <div class="staff-reservations__item-header u-flex">
                  <p class="staff-reservations__number">
                    予約番号：{{ $reservation->reservation_number }}
                  </p>
                  <span class="staff-reservations__status">
                    {{ $reservation->status->value }}
                  </span>
                </div>

                <dl class="staff-reservations__details">
                  <div class="staff-reservations__detail">
                    <dt class="staff-reservations__label">顧客名</dt>
                    <dd class="staff-reservations__value">
                      {{ $reservation->customer_name }}
                    </dd>
                  </div>

                  <div class="staff-reservations__detail">
                    <dt class="staff-reservations__label">メニュー</dt>
                    <dd class="staff-reservations__value">
                      {{ $reservation->menu->name }}
                    </dd>
                  </div>

                  <div class="staff-reservations__detail">
                    <dt class="staff-reservations__label">予約日時</dt>
                    <dd class="staff-reservations__value">
                      {{ $reservation->start_at->format('Y/m/d H\:i') }}
                    </dd>
                  </div>
                </dl>

                <div class="staff-reservations__actions u-flex">
                  <a class="staff-reservations__detail-button"
                    href="{{ route('staff.reservations.show', $reservation) }}">
                    詳細を見る
                  </a>
                </div>
              </article>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </section>
</x-app-layout>
