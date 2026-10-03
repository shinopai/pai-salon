<x-app-layout>
  <div class="admin-holidays">
    <div class="u-wrap">
      <header class="admin-holidays__header u-flex">
        <div>
          <p class="admin-holidays__eyebrow">HOLIDAY MANAGEMENT</p>
          <h1 class="admin-holidays__title">休業日管理</h1>
          <p class="admin-holidays__description">
            サロンの休業日を確認・管理できます。
          </p>
        </div>

        <a href="{{ route('admin.holidays.create') }}" class="admin-holidays__create">
          休業日を登録
        </a>
      </header>

      <section class="admin-holidays__section">
        <div class="admin-holidays__list">
          @foreach ($holidays as $holiday)
            <article class="admin-holidays__card">
              <div class="admin-holidays__card-body">
                <div class="admin-holidays__item">
                  <span class="admin-holidays__label">
                    休業日
                  </span>
                  <time class="admin-holidays__value" datetime="{{ $holiday->date }}">
                    {{ $holiday->date }}
                  </time>
                </div>

                <div class="admin-holidays__item">
                  <span class="admin-holidays__label">
                    理由
                  </span>
                  <span class="admin-holidays__value">
                    {{ $holiday->reason }}
                  </span>
                </div>
              </div>

              <div class="admin-holidays__actions u-flex">
                <a href="{{ route('admin.holidays.edit', $holiday) }}" class="admin-holidays__edit">
                  編集
                </a>

                <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}"
                  class="admin-holidays__delete-form">
                  @csrf
                  @method('DELETE')

                  <button type="submit" class="admin-holidays__delete" onclick="return confirm('この休業日を削除しますか？')">
                    削除
                  </button>
                </form>
              </div>
            </article>
          @endforeach
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
