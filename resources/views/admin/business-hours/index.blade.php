<x-app-layout>
  <div class="admin-business-hours">
    <div class="u-wrap">
      <header class="admin-business-hours__header">
        <p class="admin-business-hours__eyebrow">BUSINESS HOURS MANAGEMENT</p>
        <h1 class="admin-business-hours__title">営業時間管理</h1>
        <p class="admin-business-hours__description">
          曜日ごとの営業時間を設定できます。
        </p>
      </header>

      <section class="admin-business-hours__section">
        <div class="admin-business-hours__table-wrap">
          <table class="admin-business-hours__table">
            <thead>
              <tr>
                <th scope="col">曜日</th>
                <th scope="col">営業時間</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($businessHours as $businessHour)
                <tr>
                  <th scope="row" data-label="曜日">
                    {{ $dayNames[$businessHour->day_of_week] }}
                  </th>

                  <td data-label="営業時間">
                    <form method="POST" action="{{ route('admin.business-hours.update') }}"
                      class="admin-business-hours__form">
                      @csrf
                      @method('PUT')

                      <input type="hidden" name="day_of_week" value="{{ $businessHour->day_of_week }}">

                      <div class="admin-business-hours__time-group">
                        <input type="time" name="open_time"
                          value="{{ $businessHour->open_time ? \Carbon\Carbon::parse($businessHour->open_time)->format('H:i') : '' }}"
                          class="admin-business-hours__input">

                        <span class="admin-business-hours__separator">
                          ～
                        </span>

                        <input type="time" name="close_time"
                          value="{{ $businessHour->close_time ? \Carbon\Carbon::parse($businessHour->close_time)->format('H:i') : '' }}"
                          class="admin-business-hours__input">
                      </div>

                      <button type="submit" class="admin-business-hours__submit">
                        更新
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
