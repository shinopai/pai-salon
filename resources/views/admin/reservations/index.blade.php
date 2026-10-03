<x-app-layout>
  <div class="admin-reservations">
    <div class="u-wrap">
      <header class="admin-reservations__header">
        <p class="admin-reservations__eyebrow">RESERVATION MANAGEMENT</p>
        <h1 class="admin-reservations__title">予約一覧</h1>
        <p class="admin-reservations__description">
          登録されている予約を確認・管理できます。
        </p>
      </header>

      <section class="admin-reservations__section">
        <div class="admin-reservations__list">
          @foreach ($reservations as $reservation)
            <article class="admin-reservations__card">
              <div class="admin-reservations__card-header">
                <p class="admin-reservations__number">
                  {{ $reservation->reservation_number }}
                </p>
              </div>

              <div class="admin-reservations__card-body">
                <div class="admin-reservations__item">
                  <span class="admin-reservations__label">
                    顧客名
                  </span>
                  <span class="admin-reservations__value">
                    {{ $reservation->customer_name }}
                  </span>
                </div>

                <div class="admin-reservations__item">
                  <span class="admin-reservations__label">
                    メールアドレス
                  </span>
                  <span class="admin-reservations__value">
                    {{ $reservation->customer_email }}
                  </span>
                </div>

                <div class="admin-reservations__item">
                  <span class="admin-reservations__label">
                    開始日時
                  </span>
                  <time class="admin-reservations__value" datetime="{{ $reservation->start_at }}">
                    {{ $reservation->start_at }}
                  </time>
                </div>

                <div class="admin-reservations__item">
                  <span class="admin-reservations__label">
                    終了日時
                  </span>
                  <time class="admin-reservations__value" datetime="{{ $reservation->end_at }}">
                    {{ $reservation->end_at }}
                  </time>
                </div>
              </div>

              <div class="admin-reservations__actions u-flex">
                <a href="{{ route('admin.reservations.show', $reservation) }}" class="admin-reservations__detail">
                  詳細
                </a>
              </div>
            </article>
          @endforeach
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
