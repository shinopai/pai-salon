<x-app-layout>
  <section class="staff-profile-edit">
    <div class="u-wrap">
      <div class="staff-profile-edit__inner">
        <h1 class="staff-profile-edit__title">スタッフ情報編集</h1>

        <form class="staff-profile-edit__form" action="{{ route('staff.profile.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="staff-profile-edit__field">
            <label class="staff-profile-edit__label" for="name">名前</label>
            @error('name')
              <p class="u-error">{{ $message }}</p>
            @enderror
            <input class="staff-profile-edit__input" type="text" id="name" name="name"
              value="{{ $staff->name }}">
          </div>

          <div class="staff-profile-edit__field">
            <label class="staff-profile-edit__label" for="email">メールアドレス</label>
            <input class="staff-profile-edit__input" type="email" id="email" name="email"
              value="{{ $staff->user->email }}" readonly>
          </div>

          <div class="staff-profile-edit__field">
            <label class="staff-profile-edit__label" for="role">権限</label>
            <input class="staff-profile-edit__input" type="text" id="role" name="role"
              value="{{ $staff->role->value }}" readonly>
          </div>

          <div class="staff-profile-edit__actions u-flex">
            <a class="staff-profile-edit__back-button" href="{{ route('staff.profile') }}">
              戻る
            </a>
            <button class="staff-profile-edit__submit-button" type="submit">
              保存する
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</x-app-layout>
