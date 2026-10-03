<x-app-layout>
  <div class="admin-staffs">
    <div class="u-wrap">
      <header class="admin-staffs__header u-flex">
        <div>
          <p class="admin-staffs__eyebrow">STAFF MANAGEMENT</p>
          <h1 class="admin-staffs__title">スタッフ一覧</h1>
          <p class="admin-staffs__description">
            登録されているスタッフを確認・管理できます。
          </p>
        </div>

        <a href="{{ route('admin.staffs.create') }}" class="admin-staffs__create">
          スタッフを登録
        </a>
      </header>

      <section class="admin-staffs__section">
        <div class="admin-staffs__list">
          @foreach ($staffs as $staff)
            <article class="admin-staffs__card u-flex">
              <div class="admin-staffs__card-body">
                <h2 class="admin-staffs__name">
                  {{ $staff->name }}
                </h2>

                <p class="admin-staffs__role">
                  {{ $staff->role->value === 'admin' ? '管理者' : 'スタッフ' }}
                </p>
              </div>

              <div class="admin-staffs__actions u-flex">
                <a href="{{ route('admin.staffs.show', $staff) }}"
                  class="admin-staffs__action admin-staffs__action--detail">
                  詳細
                </a>

                <a href="{{ route('admin.staffs.edit', $staff) }}"
                  class="admin-staffs__action admin-staffs__action--edit">
                  編集
                </a>

                <form action="{{ route('admin.staffs.destroy', $staff) }}" method="POST"
                  class="admin-staffs__delete-form">
                  @csrf
                  @method('DELETE')

                  <button type="submit" class="admin-staffs__action admin-staffs__action--delete"
                    onclick="return confirm('このスタッフを削除しますか？')">
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
