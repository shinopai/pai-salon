<x-app-layout>
  <div class="admin-customers-show">
    <div class="u-wrap">
      <header class="admin-customers-show__header">
        <p class="admin-customers-show__eyebrow">CUSTOMER MANAGEMENT</p>
        <h1 class="admin-customers-show__title">顧客詳細</h1>
        <p class="admin-customers-show__description">
          顧客の登録情報を確認できます。
        </p>
      </header>

      <section class="admin-customers-show__section">
        <dl class="admin-customers-show__list">
          <div class="admin-customers-show__item">
            <dt class="admin-customers-show__label">氏名</dt>
            <dd class="admin-customers-show__value">
              {{ $customer->name }}
            </dd>
          </div>

          <div class="admin-customers-show__item">
            <dt class="admin-customers-show__label">メールアドレス</dt>
            <dd class="admin-customers-show__value">
              {{ $customer->email }}
            </dd>
          </div>
        </dl>

        <div class="admin-customers-show__actions u-flex">
          <a href="{{ route('admin.customers.index') }}" class="admin-customers-show__back">
            一覧へ戻る
          </a>

          <a href="{{ route('admin.customers.edit', $customer) }}" class="admin-customers-show__edit">
            編集
          </a>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
