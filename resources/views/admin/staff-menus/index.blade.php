<x-app-layout>
  <div class="admin-staff-menus">
    <div class="u-wrap">
      <header class="admin-staff-menus__header">
        <p class="admin-staff-menus__eyebrow">STAFF MENU MANAGEMENT</p>
        <h1 class="admin-staff-menus__title">スタッフ・メニュー対応管理</h1>
        <p class="admin-staff-menus__description">
          スタッフごとに対応可能なメニューを設定できます。
        </p>
      </header>

      <div class="admin-staff-menus__list">
        @foreach ($staffs as $staff)
          <section class="admin-staff-menus__card">
            <div class="admin-staff-menus__card-header">
              <h2 class="admin-staff-menus__staff-name">
                {{ $staff->name }}
              </h2>
            </div>

            <form method="POST" action="{{ route('admin.staff-menus.update') }}" class="admin-staff-menus__form">
              @csrf
              @method('PUT')

              <input type="hidden" name="staff_id" value="{{ $staff->id }}">

              <fieldset class="admin-staff-menus__fieldset">
                <legend class="admin-staff-menus__legend">
                  対応可能メニュー
                </legend>

                <div class="admin-staff-menus__menus">
                  @foreach ($menus as $menu)
                    <label class="admin-staff-menus__menu u-flex">
                      <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}" @checked($staff->menus->contains($menu->id))
                        class="admin-staff-menus__checkbox">
                      <span class="admin-staff-menus__menu-name">
                        {{ $menu->name }}
                      </span>
                    </label>
                  @endforeach
                </div>
              </fieldset>

              <div class="admin-staff-menus__actions u-flex">
                <button type="submit" class="admin-staff-menus__submit">
                  保存
                </button>
              </div>
            </form>
          </section>
        @endforeach
      </div>
    </div>
  </div>
</x-app-layout>
