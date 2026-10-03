<x-app-layout>
  <div class="admin-menus">
    <div class="u-wrap">
      <header class="admin-menus__header u-flex">
        <div>
          <p class="admin-menus__eyebrow">MENU MANAGEMENT</p>
          <h1 class="admin-menus__title">メニュー一覧</h1>
          <p class="admin-menus__description">
            登録されているメニューを確認・管理できます。
          </p>
        </div>

        <a href="{{ route('admin.menus.create') }}" class="admin-menus__create">
          メニューを登録
        </a>
      </header>

      @if (session('error'))
        <p class="admin-menus__error">
          {{ session('error') }}
        </p>
      @endif

      <section class="admin-menus__section">
        <div class="admin-menus__table-wrap">
          <table class="admin-menus__table">
            <thead>
              <tr>
                <th scope="col">メニュー名</th>
                <th scope="col">所要時間</th>
                <th scope="col">操作</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($menus as $menu)
                <tr>
                  <td data-label="メニュー名">
                    {{ $menu->name }}
                  </td>

                  <td data-label="所要時間">
                    {{ $menu->duration }}分
                  </td>

                  <td data-label="操作">
                    <div class="admin-menus__actions u-flex">
                      <a href="{{ route('admin.menus.show', $menu) }}" class="admin-menus__detail">
                        詳細
                      </a>

                      <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}"
                        class="admin-menus__delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-menus__delete" onclick="return confirm('このメニューを削除しますか？')">
                          削除
                        </button>
                      </form>
                    </div>
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
