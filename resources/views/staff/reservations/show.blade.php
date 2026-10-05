<x-app-layout>
  <section class="staff-reservations-show">
    <div class="u-wrap">
      <div class="staff-reservations-show__inner">
        <div class="staff-reservations-show__header u-flex">
          <h1 class="staff-reservations-show__title">スタッフ予約詳細</h1>

          <a class="staff-reservations-show__back-button" href="{{ route('staff.reservations.index') }}">
            予約一覧へ戻る
          </a>
        </div>

        <form class="staff-reservations-show__form" action="{{ route('staff.reservations.update', $reservation) }}"
          method="POST">
          @csrf
          @method('PUT')

          <div class="staff-reservations-show__card">
            <dl class="staff-reservations-show__list">
              <div class="staff-reservations-show__item u-flex">
                <dt class="staff-reservations-show__label">予約番号</dt>
                <dd class="staff-reservations-show__value">
                  {{ $reservation->reservation_number }}
                </dd>
              </div>

              <div class="staff-reservations-show__item u-flex">
                <dt class="staff-reservations-show__label">顧客名</dt>
                <dd class="staff-reservations-show__value">
                  {{ $reservation->customer_name }}
                </dd>
              </div>

              <div class="staff-reservations-show__item u-flex">
                <dt class="staff-reservations-show__label">メールアドレス</dt>
                <dd class="staff-reservations-show__value">
                  {{ $reservation->customer_email }}
                </dd>
              </div>

              <div class="staff-reservations-show__item u-flex">
                <dt class="staff-reservations-show__label">担当スタッフ</dt>
                <dd class="staff-reservations-show__value">
                  @error('staff_id')
                    <p class="u-error">{{ $message }}</p>
                  @enderror
                  <select class="staff-reservations-show__select" name="staff_id" id="staff_id">
                    @foreach ($staffs as $staff)
                      <option value="{{ $staff->id }}" @selected($reservation->staff_id === $staff->id)>
                        {{ $staff->name }}
                      </option>
                    @endforeach
                  </select>
                </dd>
              </div>

              <div class="staff-reservations-show__item u-flex">
                <dt class="staff-reservations-show__label">メニュー</dt>
                <dd class="staff-reservations-show__value">
                  @error('menu_id')
                    <p class="u-error">{{ $message }}</p>
                  @enderror
                  <select class="staff-reservations-show__select" name="menu_id" id="menu_id">
                    @foreach ($menus as $menu)
                      <option value="{{ $menu->id }}" @selected($reservation->menu_id === $menu->id)>
                        {{ $menu->name }}
                      </option>
                    @endforeach
                  </select>
                </dd>
              </div>

              <div class="staff-reservations-show__item u-flex">
                <dt class="staff-reservations-show__label">予約日時</dt>
                <dd class="staff-reservations-show__value">
                  @error('start_at')
                    <p class="u-error">{{ $message }}</p>
                  @enderror
                  <input class="staff-reservations-show__input" type="datetime-local" name="start_at" id="start_at"
                    value="{{ $reservation->start_at->format('Y-m-d\TH:i') }}">
                </dd>
              </div>

              <div class="staff-reservations-show__item u-flex">
                <dt class="staff-reservations-show__label">ステータス</dt>
                <dd class="staff-reservations-show__value">
                  @error('status')
                    <p class="u-error">{{ $message }}</p>
                  @enderror
                  <select class="staff-reservations-show__select" name="status" id="status">
                    <option value="reserved" @selected($reservation->status === \App\Enums\ReservationStatus::RESERVED)>
                      予約中
                    </option>
                    <option value="completed" @selected($reservation->status === \App\Enums\ReservationStatus::COMPLETED)>
                      完了
                    </option>
                    <option value="cancelled" @selected($reservation->status === \App\Enums\ReservationStatus::CANCELLED)>
                      キャンセル
                    </option>
                  </select>
                </dd>
              </div>
            </dl>

            <div class="staff-reservations-show__actions">
              <button class="staff-reservations-show__submit-button" type="submit">
                予約を更新する
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</x-app-layout>
