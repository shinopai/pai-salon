<x-app-layout>
  <section class="staff-profile">
    <div class="u-wrap">
      <div class="staff-profile__inner">
        <div class="staff-profile__header">
          <h1 class="staff-profile__title">スタッフ情報</h1>

          <a class="staff-profile__edit-button" href="{{ route('staff.profile.edit') }}">
            プロフィールを編集
          </a>
        </div>

        <div class="staff-profile__card">
          <dl class="staff-profile__list">
            <div class="staff-profile__item">
              <dt class="staff-profile__label">名前</dt>
              <dd class="staff-profile__value">{{ $staff->name }}</dd>
            </div>

            <div class="staff-profile__item">
              <dt class="staff-profile__label">権限</dt>
              <dd class="staff-profile__value">{{ $staff->role->value }}</dd>
            </div>

            <div class="staff-profile__item">
              <dt class="staff-profile__label">メールアドレス</dt>
              <dd class="staff-profile__value">{{ $staff->user->email }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </section>
</x-app-layout>
