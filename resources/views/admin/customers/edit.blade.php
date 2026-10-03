<x-app-layout>
  <div class="admin-customers-edit">
    <div class="u-wrap">
      <header class="admin-customers-edit__header">
        <p class="admin-customers-edit__eyebrow">CUSTOMER MANAGEMENT</p>
        <h1 class="admin-customers-edit__title">顧客編集</h1>
        <p class="admin-customers-edit__description">
          顧客の登録情報を編集します。
        </p>
      </header>

      <section class="admin-customers-edit__section">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="admin-customers-edit__form">
          @csrf
          @method('PUT')

          <div class="admin-customers-edit__field">
            <label for="name" class="admin-customers-edit__label">
              名前
            </label>

            <input type="text" id="name" name="name" value="{{ old('name', $customer->name) }}"
              class="admin-customers-edit__input">

            @error('name')
              <p class="admin-customers-edit__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-customers-edit__field">
            <label for="email" class="admin-customers-edit__label">
              メールアドレス
            </label>

            <input type="email" id="email" name="email" value="{{ old('email', $customer->email) }}"
              class="admin-customers-edit__input">

            @error('email')
              <p class="admin-customers-edit__error">
                {{ $message }}
              </p>
            @enderror
          </div>

          <div class="admin-customers-edit__actions u-flex">
            <a href="{{ route('admin.customers.show', $customer) }}" class="admin-customers-edit__back">
              詳細へ戻る
            </a>

            <button type="submit" class="admin-customers-edit__submit">
              変更を保存
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</x-app-layout>
