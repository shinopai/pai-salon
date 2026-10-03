<x-app-layout>
  <div class="admin-menus-edit">
    <div class="u-wrap">
      <header class="admin-menus-edit__header">
        <p class="admin-menus-edit__eyebrow">MENU MANAGEMENT</p>
        <h1 class="admin-menus-edit__title">メニュー編集</h1>
        <p class="admin-menus-edit__description">
          メニューの登録内容を編集します。
        </p>
      </header>

      <section class="admin-menus-edit__section">
        <form action="{{ route('admin.menus.update', $menu) }}" method="POST" class="admin-menus-edit__form">
          @csrf
          @method('PUT')

          <div class="admin-menus-edit__field">
            <label for="name" class="admin-menus-edit__label">
              メニュー名
            </label>

            <input type="text" id="name" name="name" value="{{ old('name', $menu->name) }}"
              class="admin-menus-edit__input">

            @error('name')
              <p class="admin-menus-edit__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-menus-edit__field">
            <label for="duration" class="admin-menus-edit__label">
              所要時間
            </label>

            <div class="admin-menus-edit__input-group">
              <input type="number" id="duration" name="duration" value="{{ old('duration', $menu->duration) }}"
                min="1" class="admin-menus-edit__input">

              <span class="admin-menus-edit__unit">分</span>
            </div>

            @error('duration')
              <p class="admin-menus-edit__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-menus-edit__actions u-flex">
            <a href="{{ route('admin.menus.show', $menu) }}" class="admin-menus-edit__back">
              詳細へ戻る
            </a>

            <button type="submit" class="admin-menus-edit__submit">
              変更を保存
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</x-app-layout>
