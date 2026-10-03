<x-app-layout>
  <div class="admin-holidays-create">
    <div class="u-wrap">
      <header class="admin-holidays-create__header">
        <p class="admin-holidays-create__eyebrow">HOLIDAY MANAGEMENT</p>
        <h1 class="admin-holidays-create__title">休業日登録</h1>
        <p class="admin-holidays-create__description">
          新しい休業日を登録します。
        </p>
      </header>

      <section class="admin-holidays-create__section">
        <form method="POST" action="{{ route('admin.holidays.store') }}" class="admin-holidays-create__form">
          @csrf

          <div class="admin-holidays-create__field">
            <label for="date" class="admin-holidays-create__label">
              休業日
            </label>

            <input type="date" id="date" name="date" value="{{ old('date') }}"
              class="admin-holidays-create__input">

            @error('date')
              <p class="admin-holidays-create__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-holidays-create__field">
            <label for="reason" class="admin-holidays-create__label">
              理由
            </label>

            <input type="text" id="reason" name="reason" value="{{ old('reason') }}"
              class="admin-holidays-create__input">

            @error('reason')
              <p class="admin-holidays-create__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-holidays-create__actions u-flex">
            <a href="{{ route('admin.holidays.index') }}" class="admin-holidays-create__back">
              一覧へ戻る
            </a>

            <button type="submit" class="admin-holidays-create__submit">
              休業日を登録する
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</x-app-layout>
