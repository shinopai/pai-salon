<x-app-layout>
  <div class="admin-dashboard">
    <div class="u-wrap">
      <header class="admin-dashboard__header">
        <p class="admin-dashboard__eyebrow">ADMIN DASHBOARD</p>
        <h1 class="admin-dashboard__title">管理者ダッシュボード</h1>
        <p class="admin-dashboard__greeting">
          {{ $staff->name }}さん、ようこそ。
        </p>
      </header>

      <section class="admin-dashboard__section">
        <h2 class="admin-dashboard__section-title">管理メニュー</h2>

        <div class="admin-dashboard__grid">
          <a href="{{ route('admin.staffs.index') }}" class="admin-dashboard__card">
            <h3 class="admin-dashboard__card-title">スタッフ管理</h3>
            <p class="admin-dashboard__card-text">
              スタッフの登録・編集・管理を行います。
            </p>
          </a>

          <a href="{{ route('admin.customers.index') }}" class="admin-dashboard__card">
            <h3 class="admin-dashboard__card-title">顧客管理</h3>
            <p class="admin-dashboard__card-text">
              顧客情報の確認・編集を行います。
            </p>
          </a>

          <a href="{{ route('admin.menus.index') }}" class="admin-dashboard__card">
            <h3 class="admin-dashboard__card-title">メニュー管理</h3>
            <p class="admin-dashboard__card-text">
              サロンメニューの登録・編集・管理を行います。
            </p>
          </a>

          <a href="{{ route('admin.staff-menus.index') }}" class="admin-dashboard__card">
            <h3 class="admin-dashboard__card-title">スタッフ対応メニュー</h3>
            <p class="admin-dashboard__card-text">
              スタッフごとの対応可能メニューを設定します。
            </p>
          </a>

          <a href="{{ route('admin.reservations.index') }}" class="admin-dashboard__card">
            <h3 class="admin-dashboard__card-title">予約管理</h3>
            <p class="admin-dashboard__card-text">
              予約状況の確認・管理を行います。
            </p>
          </a>

          <a href="{{ route('admin.business-hours.index') }}" class="admin-dashboard__card">
            <h3 class="admin-dashboard__card-title">営業時間管理</h3>
            <p class="admin-dashboard__card-text">
              曜日ごとの営業時間を設定します。
            </p>
          </a>

          <a href="{{ route('admin.holidays.index') }}" class="admin-dashboard__card">
            <h3 class="admin-dashboard__card-title">休業日管理</h3>
            <p class="admin-dashboard__card-text">
              サロンの休業日を登録・管理します。
            </p>
          </a>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
