<x-app-layout>
  <div class="admin-staffs-show">
    <div class="u-wrap">
      <header class="admin-staffs-show__header">
        <p class="admin-staffs-show__eyebrow">STAFF MANAGEMENT</p>
        <h1 class="admin-staffs-show__title">スタッフ詳細</h1>
        <p class="admin-staffs-show__description">
          スタッフの登録情報を確認できます。
        </p>
      </header>

      <section class="admin-staffs-show__section">
        <dl class="admin-staffs-show__list">
          <div class="admin-staffs-show__item">
            <dt class="admin-staffs-show__label">氏名</dt>
            <dd class="admin-staffs-show__value">
              {{ $staff->name }}
            </dd>
          </div>

          <div class="admin-staffs-show__item">
            <dt class="admin-staffs-show__label">メールアドレス</dt>
            <dd class="admin-staffs-show__value">
              {{ $staff->user->email }}
            </dd>
          </div>

          <div class="admin-staffs-show__item">
            <dt class="admin-staffs-show__label">権限</dt>
            <dd class="admin-staffs-show__value">
              {{ $staff->role->value === 'admin' ? '管理者' : 'スタッフ' }}
            </dd>
          </div>
        </dl>

        <div class="admin-staffs-show__actions u-flex">
          <a href="{{ route('admin.staffs.index') }}" class="admin-staffs-show__back">
            一覧へ戻る
          </a>

          <a href="{{ route('admin.staffs.edit', $staff) }}" class="admin-staffs-show__edit">
            編集
          </a>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
