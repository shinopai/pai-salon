<header class="header">
  <div class="u-wrap">
    <div class="header__inner u-flex">
      @if ($isAdmin)
        <a href="{{ route('admin.staffs.index') }}" class="header__logo">
          <x-application-logo />
        </a>
      @elseif ($isStaff)
        <a href="{{ route('staff.dashboard') }}" class="header__logo">
          <x-application-logo />
        </a>
      @else
        <a href="{{ url('/') }}" class="header__logo">
          <x-application-logo />
        </a>
      @endif

      <nav class="header__nav u-flex">
        @if ($isAdmin)
          <ul class="header__menu u-flex">
            <li class="header__item">
              <a href="{{ route('admin.staffs.index') }}" class="header__link">
                スタッフ
              </a>
            </li>
            <li class="header__item">
              <a href="{{ route('admin.customers.index') }}" class="header__link">
                顧客
              </a>
            </li>
            <li class="header__item">
              <a href="{{ route('admin.menus.index') }}" class="header__link">
                メニュー
              </a>
            </li>
            <li class="header__item">
              <a href="{{ route('admin.staff-menus.index') }}" class="header__link">
                スタッフメニュー
              </a>
            </li>
            <li class="header__item">
              <a href="{{ route('admin.reservations.index') }}" class="header__link">
                予約
              </a>
            </li>
            <li class="header__item">
              <a href="{{ route('admin.business-hours.index') }}" class="header__link">
                営業時間
              </a>
            </li>
            <li class="header__item">
              <a href="{{ route('admin.holidays.index') }}" class="header__link">
                休日
              </a>
            </li>
          </ul>

          <div class="header__user u-flex">
            <span class="header__name">{{ $staffName }}</span>

            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="header__logout">
                ログアウト
              </button>
            </form>
          </div>
        @elseif ($isStaff)
          <ul class="header__menu u-flex">
            <li class="header__item">
              <a href="{{ route('staff.reservations.index') }}" class="header__link">
                予約一覧
              </a>
            </li>
            <li class="header__item">
              <a href="{{ route('staff.profile') }}" class="header__link">
                プロフィール
              </a>
            </li>
            <li class="header__item">
              <a href="{{ route('staff.menus.index') }}" class="header__link">
                対応メニュー
              </a>
            </li>
          </ul>

          <div class="header__user u-flex">
            <span class="header__name">{{ $staffName }}</span>

            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="header__logout">
                ログアウト
              </button>
            </form>
          </div>
        @else
          <ul class="header__menu u-flex">
            <li class="header__item">
              <a href="{{ route('reservations.menu') }}" class="header__link">
                Web予約
              </a>
            </li>
            <li class="header__item">
              <a href="{{ route('login') }}" class="header__link header__link--small">
                関係者ログイン
              </a>
            </li>
          </ul>
        @endif
      </nav>
    </div>
  </div>
</header>
