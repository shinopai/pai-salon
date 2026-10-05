<x-app-layout>
  <div class="admin-staffs-edit">
    <div class="u-wrap">
      <header class="admin-staffs-edit__header">
        <p class="admin-staffs-edit__eyebrow">STAFF MANAGEMENT</p>
        <h1 class="admin-staffs-edit__title">スタッフ編集</h1>
        <p class="admin-staffs-edit__description">
          スタッフの登録情報を編集します。
        </p>
      </header>

      <section class="admin-staffs-edit__section">
        <form action="{{ route('admin.staffs.update', $staff) }}" method="POST" class="admin-staffs-edit__form">
          @csrf
          @method('PUT')

          <div class="admin-staffs-edit__field">
            <label for="name" class="admin-staffs-edit__label">
              氏名
            </label>

            @error('name')
              <p class="u-error">
                {{ $message }}
              </p>
            @enderror

            <input type="text" id="name" name="name" value="{{ old('name', $staff->name) }}"
              class="admin-staffs-edit__input">
          </div>

          <div class="admin-staffs-edit__field">
            <label for="email" class="admin-staffs-edit__label">
              メールアドレス
            </label>

            @error('email')
              <p class="u-error">
                {{ $message }}
              </p>
            @enderror

            <input type="email" id="email" name="email" value="{{ old('email', $staff->user->email) }}"
              class="admin-staffs-edit__input">
          </div>

          <div class="admin-staffs-edit__field">
            <label for="role" class="admin-staffs-edit__label">
              権限
            </label>

            @error('role')
              <p class="u-error">
                {{ $message }}
              </p>
            @enderror

            <select id="role" name="role" class="admin-staffs-edit__select">
              <option value="staff" @selected(old('role', $staff->role->value) === 'staff')>
                スタッフ
              </option>
              <option value="admin" @selected(old('role', $staff->role->value) === 'admin')>
                管理者
              </option>
            </select>
          </div>

          <div class="admin-staffs-edit__actions u-flex">
            <a href="{{ route('admin.staffs.show', $staff) }}" class="admin-staffs-edit__back">
              詳細へ戻る
            </a>

            <button type="submit" class="admin-staffs-edit__submit">
              変更を保存
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</x-app-layout>
