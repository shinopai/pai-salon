<x-app-layout>
  <div class="admin-reservations-show">
    <div class="u-wrap">
      <header class="admin-reservations-show__header">
        <p class="admin-reservations-show__eyebrow">RESERVATION MANAGEMENT</p>
        <h1 class="admin-reservations-show__title">予約詳細</h1>
        <p class="admin-reservations-show__description">
          予約内容を確認・管理できます。
        </p>
      </header>

      @if (session('success'))
        <p class="admin-reservations-show__success">
          {{ session('success') }}
        </p>
      @endif

      <section class="admin-reservations-show__section">
        <div class="admin-reservations-show__reservation">
          <div class="admin-reservations-show__reservation-header">
            <p class="admin-reservations-show__number">
              {{ $reservation->reservation_number }}
            </p>
          </div>

          <dl class="admin-reservations-show__list">
            <div class="admin-reservations-show__item">
              <dt class="admin-reservations-show__label">
                顧客名
              </dt>
              <dd class="admin-reservations-show__value">
                {{ $reservation->customer_name }}
              </dd>
            </div>

            <div class="admin-reservations-show__item">
              <dt class="admin-reservations-show__label">
                メールアドレス
              </dt>
              <dd class="admin-reservations-show__value">
                {{ $reservation->customer_email }}
              </dd>
            </div>

            <div class="admin-reservations-show__item">
              <dt class="admin-reservations-show__label">
                担当スタッフ
              </dt>
              <dd class="admin-reservations-show__value">
                {{ $reservation->staff->name }}
              </dd>
            </div>

            <div class="admin-reservations-show__item">
              <dt class="admin-reservations-show__label">
                メニュー
              </dt>
              <dd class="admin-reservations-show__value">
                {{ $reservation->menu->name }}
              </dd>
            </div>

            <div class="admin-reservations-show__item">
              <dt class="admin-reservations-show__label">
                開始日時
              </dt>
              <dd class="admin-reservations-show__value">
                {{ $reservation->start_at }}
              </dd>
            </div>

            <div class="admin-reservations-show__item">
              <dt class="admin-reservations-show__label">
                終了日時
              </dt>
              <dd class="admin-reservations-show__value">
                {{ $reservation->end_at }}
              </dd>
            </div>

            <div class="admin-reservations-show__item">
              <dt class="admin-reservations-show__label">
                ステータス
              </dt>
              <dd class="admin-reservations-show__value">
                {{ $reservation->status->value }}
              </dd>
            </div>
          </dl>
        </div>

        <div class="admin-reservations-show__edit">
          <h2 class="admin-reservations-show__edit-title">
            予約を編集
          </h2>

          <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}"
            class="admin-reservations-show__form">
            @csrf
            @method('PUT')

            <div class="admin-reservations-show__field">
              <label for="staff_id" class="admin-reservations-show__field-label">
                担当スタッフ
              </label>

              @error('staff_id')
                <p class="u-error">
                  {{ $message }}
                </p>
              @enderror

              <select id="staff_id" name="staff_id" class="admin-reservations-show__select">
                @foreach ($staffs as $staff)
                  <option value="{{ $staff->id }}" @selected($staff->id === $reservation->staff_id)>
                    {{ $staff->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="admin-reservations-show__field">
              <label for="menu_id" class="admin-reservations-show__field-label">
                メニュー
              </label>

              @error('menu_id')
                <p class="u-error">
                  {{ $message }}
                </p>
              @enderror

              <select id="menu_id" name="menu_id" class="admin-reservations-show__select">
                @foreach ($menus as $menu)
                  <option value="{{ $menu->id }}" @selected($menu->id === $reservation->menu_id)>
                    {{ $menu->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="admin-reservations-show__field">
              <label for="start_at" class="admin-reservations-show__field-label">
                開始日時
              </label>

              @error('start_at')
                <p class="u-error">
                  {{ $message }}
                </p>
              @enderror

              <input type="datetime-local" id="start_at" name="start_at"
                value="{{ old('start_at', $reservation->start_at->format('Y-m-d\TH:i')) }}"
                class="admin-reservations-show__input">
            </div>

            <div class="admin-reservations-show__field">
              <label for="status" class="admin-reservations-show__field-label">
                ステータス
              </label>

              @error('status')
                <p class="u-error">
                  {{ $message }}
                </p>
              @enderror

              <select id="status" name="status" class="admin-reservations-show__select">
                @foreach (\App\Enums\ReservationStatus::cases() as $status)
                  <option value="{{ $status->value }}" @selected($status === $reservation->status)>
                    {{ $status->value }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="admin-reservations-show__actions u-flex">
              <a href="{{ route('admin.reservations.index') }}" class="admin-reservations-show__back">
                一覧へ戻る
              </a>

              <button type="submit" class="admin-reservations-show__submit">
                予約を更新する
              </button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
