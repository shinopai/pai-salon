<x-app-layout>
  <section class="staff-dashboard">
    <div class="u-wrap">
      <div class="staff-dashboard__inner">
        <div class="staff-dashboard__welcome">
          <h1 class="staff-dashboard__title">
            おはようございます、{{ $staff->name }}さん
          </h1>

          <p class="staff-dashboard__lead">
            本日の業務状況を確認できます。
          </p>
        </div>

        <div class="staff-dashboard__summary">
          <div class="staff-dashboard__summary-card">
            <p class="staff-dashboard__summary-label">今日の予約</p>
            <p class="staff-dashboard__summary-count">
              {{ $todayReservations->count() }}<span>件</span>
            </p>
          </div>

          <div class="staff-dashboard__summary-card">
            <p class="staff-dashboard__summary-label">次の予約</p>

            @if ($nextReservation)
              <p class="staff-dashboard__next-time">
                {{ $nextReservation->start_at->format('H:i') }}
              </p>

              <p class="staff-dashboard__next-detail">
                {{ $nextReservation->menu->name }}
                / {{ $nextReservation->customer_name }}様
              </p>
            @else
              <p class="staff-dashboard__next-empty">
                本日の予約はありません。
              </p>
            @endif
          </div>
        </div>

        <div class="staff-dashboard__links">
          <a class="staff-dashboard__link" href="{{ route('staff.reservations.index') }}">
            <span class="staff-dashboard__link-title">予約一覧</span>
            <span class="staff-dashboard__link-text">
              本日の予約や予約内容を確認
            </span>
          </a>

          <a class="staff-dashboard__link" href="{{ route('staff.menus.index') }}">
            <span class="staff-dashboard__link-title">対応可能メニュー</span>
            <span class="staff-dashboard__link-text">
              対応可能なメニューを確認
            </span>
          </a>

          <a class="staff-dashboard__link" href="{{ route('staff.profile') }}">
            <span class="staff-dashboard__link-title">スタッフ情報</span>
            <span class="staff-dashboard__link-text">
              プロフィールを確認・編集
            </span>
          </a>
        </div>
      </div>
    </div>
  </section>
</x-app-layout>
