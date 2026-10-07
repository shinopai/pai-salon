<x-app-layout>
  <div class="errors">
    <div class="errors__inner u-wrap">
      <div class="errors__content">
        <p class="errors__code">403</p>
        <h1 class="errors__title">アクセスできません</h1>
        <p class="errors__message">
          この予約を操作する権限がありません。
        </p>
        <a href="{{ route('staff.reservations.index') }}" class="errors__link">
          予約一覧へ戻る
        </a>
      </div>
    </div>
  </div>
</x-app-layout>
