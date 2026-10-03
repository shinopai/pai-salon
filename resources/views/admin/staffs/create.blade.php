<x-app-layout>
  <div class="admin-staffs-create">
    <div class="u-wrap">
      <header class="admin-staffs-create__header">
        <p class="admin-staffs-create__eyebrow">STAFF MANAGEMENT</p>
        <h1 class="admin-staffs-create__title">スタッフ登録</h1>
        <p class="admin-staffs-create__description">
          新しいスタッフの情報を登録します。
        </p>
      </header>

      <section class="admin-staffs-create__section">
        <form action="{{ route('admin.staffs.store') }}" method="POST" class="admin-staffs-create__form">
          @csrf

          <div class="admin-staffs-create__field">
            <label for="name" class="admin-staffs-create__label">
              氏名
            </label>

            <input type="text" id="name" name="name" value="{{ old('name') }}"
              class="admin-staffs-create__input">

            @error('name')
              <p class="admin-staffs-create__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-staffs-create__field">
            <label for="email" class="admin-staffs-create__label">
              メールアドレス
            </label>

            <input type="email" id="email" name="email" value="{{ old('email') }}"
              class="admin-staffs-create__input">

            @error('email')
              <p class="admin-staffs-create__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-staffs-create__field">
            <label for="role" class="admin-staffs-create__label">
              権限
            </label>

            <select id="role" name="role" class="admin-staffs-create__select">
              <option value="staff" @selected(old('role', 'staff') === 'staff')>
                スタッフ
              </option>
              <option value="admin" @selected(old('role') === 'admin')>
                管理者
              </option>
            </select>

            @error('role')
              <p class="admin-staffs-create__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-staffs-create__actions u-flex">
            <a href="{{ route('admin.staffs.index') }}" class="admin-staffs-create__back">
              一覧へ戻る
            </a>

            <button type="submit" class="admin-staffs-create__submit">
              スタッフを登録
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</x-app-layout>
