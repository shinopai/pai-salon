<x-app-layout>
  <div class="login">
    <div class="u-wrap">
      <header class="login__header">
        <p class="login__eyebrow">WELCOME BACK</p>
        <h1 class="login__title">ログイン</h1>
        <p class="login__description">
          メールアドレスとパスワードを入力してください。
        </p>
      </header>

      <section class="login__section">
        <x-auth-session-status class="login__status" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="login__form" novalidate>
          @csrf

          <div class="login__field">
            <label for="email" class="login__label">
              メールアドレス
            </label>

            @error('email')
              <p class="u-error">{{ $message }}</p>
            @enderror

            <input id="email" type="email" name="email" value="{{ old('email') }}" class="login__input"
              required autofocus autocomplete="username">
          </div>

          <div class="login__field">
            <label for="password" class="login__label">
              パスワード
            </label>

            @error('password')
              <p class="u-error">{{ $message }}</p>
            @enderror

            <input id="password" type="password" name="password" class="login__input" required
              autocomplete="current-password">
          </div>

          <div class="login__remember">
            <label for="remember" class="login__remember-label">
              <input id="remember" type="checkbox" name="remember" value="1" class="login__checkbox">
              <span>ログイン状態を保持する</span>
            </label>
          </div>

          <div class="login__actions u-flex">
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="login__forgot">
                パスワードをお忘れですか？
              </a>
            @endif

            <button type="submit" class="login__submit">
              ログイン
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</x-app-layout>
