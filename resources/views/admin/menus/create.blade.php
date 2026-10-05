<x-app-layout>
  <div class="admin-menus-create">
    <div class="u-wrap">
      <header class="admin-menus-create__header">
        <p class="admin-menus-create__eyebrow">MENU MANAGEMENT</p>
        <h1 class="admin-menus-create__title">メニュー登録</h1>
        <p class="admin-menus-create__description">
          新しいメニューを登録します。
        </p>
      </header>

      <section class="admin-menus-create__section">
        <form action="{{ route('admin.menus.store') }}" method="POST" class="admin-menus-create__form">
          @csrf

          <div class="admin-menus-create__field">
            <label for="name" class="admin-menus-create__label">
              メニュー名
            </label>

            @error('name')
              <p class="u-error">
                {{ $message }}
              </p>
            @enderror

            <input type="text" id="name" name="name" value="{{ old('name') }}"
              class="admin-menus-create__input">
          </div>

          <div class="admin-menus-create__field">
            <label for="duration" class="admin-menus-create__label">
              所要時間
            </label>

            @error('duration')
              <p class="u-error">
                {{ $message }}
              </p>
            @enderror

            <div class="admin-menus-create__input-group">

              <input type="number" id="duration" name="duration" value="{{ old('duration') }}" min="1"
                class="admin-menus-create__input">

              <span class="admin-menus-create__unit">分</span>
            </div>
          </div>

          <div class="admin-menus-create__actions u-flex">
            <a href="{{ route('admin.menus.index') }}" class="admin-menus-create__back">
              一覧へ戻る
            </a>

            <button type="submit" class="admin-menus-create__submit">
              メニューを登録
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</x-app-layout>
