<x-app-layout>
  <div class="admin-menus-show">
    <div class="u-wrap">
      <header class="admin-menus-show__header">
        <p class="admin-menus-show__eyebrow">MENU MANAGEMENT</p>
        <h1 class="admin-menus-show__title">メニュー詳細</h1>
        <p class="admin-menus-show__description">
          メニューの登録内容を確認できます。
        </p>
      </header>

      <section class="admin-menus-show__section">
        <dl class="admin-menus-show__list">
          <div class="admin-menus-show__item">
            <dt class="admin-menus-show__label">
              メニュー名
            </dt>
            <dd class="admin-menus-show__value">
              {{ $menu->name }}
            </dd>
          </div>

          <div class="admin-menus-show__item">
            <dt class="admin-menus-show__label">
              所要時間
            </dt>
            <dd class="admin-menus-show__value">
              {{ $menu->duration }}分
            </dd>
          </div>
        </dl>

        <div class="admin-menus-show__actions u-flex">
          <a href="{{ route('admin.menus.index') }}" class="admin-menus-show__back">
            一覧へ戻る
          </a>

          <a href="{{ route('admin.menus.edit', $menu) }}" class="admin-menus-show__edit">
            編集
          </a>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
