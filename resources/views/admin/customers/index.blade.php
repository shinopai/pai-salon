<x-app-layout>
  <div class="admin-customers">
    <div class="u-wrap">
      <header class="admin-customers__header">
        <p class="admin-customers__eyebrow">CUSTOMER MANAGEMENT</p>
        <h1 class="admin-customers__title">顧客一覧</h1>
        <p class="admin-customers__description">
          顧客情報を検索・確認できます。
        </p>
      </header>

      <section class="admin-customers__search-section">
        <h2 class="admin-customers__section-title">顧客検索</h2>

        <form method="GET" action="{{ route('admin.customers.index') }}" class="admin-customers__search-form u-flex">
          <div class="admin-customers__search-field">
            <label for="keyword" class="admin-customers__label">
              キーワード
            </label>

            <input type="text" id="keyword" name="keyword" value="{{ request('keyword') }}"
              class="admin-customers__input" placeholder="氏名・メールアドレス">
          </div>

          <button type="submit" class="admin-customers__search-button">
            検索
          </button>
        </form>
      </section>

      <section class="admin-customers__list-section">
        <h2 class="admin-customers__section-title">顧客一覧</h2>

        <div class="admin-customers__list">
          @foreach ($customers as $customer)
            <article class="admin-customers__card u-flex">
              <div class="admin-customers__card-body">
                <h3 class="admin-customers__name">
                  {{ $customer->name }}
                </h3>

                <p class="admin-customers__email">
                  {{ $customer->email }}
                </p>
              </div>

              <div class="admin-customers__actions u-flex">
                <a href="{{ route('admin.customers.show', $customer) }}" class="admin-customers__action">
                  詳細
                </a>
              </div>
            </article>
          @endforeach
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
