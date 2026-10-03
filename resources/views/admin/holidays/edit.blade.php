<x-app-layout>
  <div class="admin-holidays-edit">
    <div class="u-wrap">
      <header class="admin-holidays-edit__header">
        <p class="admin-holidays-edit__eyebrow">HOLIDAY MANAGEMENT</p>
        <h1 class="admin-holidays-edit__title">休業日編集</h1>
        <p class="admin-holidays-edit__description">
          登録されている休業日の内容を編集します。
        </p>
      </header>

      <section class="admin-holidays-edit__section">
        <form method="POST" action="{{ route('admin.holidays.update', $holiday) }}" class="admin-holidays-edit__form">
          @csrf
          @method('PUT')

          <div class="admin-holidays-edit__field">
            <label for="date" class="admin-holidays-edit__label">
              休業日
            </label>

            <input type="date" id="date" name="date" value="{{ old('date', $holiday->date) }}"
              class="admin-holidays-edit__input">

            @error('date')
              <p class="admin-holidays-edit__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-holidays-edit__field">
            <label for="reason" class="admin-holidays-edit__label">
              理由
            </label>

            <input type="text" id="reason" name="reason" value="{{ old('reason', $holiday->reason) }}"
              class="admin-holidays-edit__input">

            @error('reason')
              <p class="admin-holidays-edit__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-holidays-edit__actions u-flex">
            <a href="{{ route('admin.holidays.index') }}" class="admin-holidays-edit__back">
              一覧へ戻る
            </a>

            <button type="submit" class="admin-holidays-edit__submit">
              変更を保存
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</x-app-layout>
