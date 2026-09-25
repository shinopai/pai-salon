<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>予約詳細</title>
</head>

<body>
  <h1>予約詳細</h1>

  @if (session('success'))
    <p>{{ session('success') }}</p>
  @endif

  <div>
    <p>{{ $reservation->reservation_number }}</p>
    <p>{{ $reservation->customer_name }}</p>
    <p>{{ $reservation->customer_email }}</p>
    <p>{{ $reservation->staff->name }}</p>
    <p>{{ $reservation->menu->name }}</p>
    <p>{{ $reservation->start_at }}</p>
    <p>{{ $reservation->end_at }}</p>
    <p>{{ $reservation->status->value }}</p>
  </div>

  <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
    @csrf
    @method('PUT')

    <div>
      <label for="staff_id">担当スタッフ</label>
      <select id="staff_id" name="staff_id">
        @foreach ($staffs as $staff)
          <option value="{{ $staff->id }}" @selected($staff->id === $reservation->staff_id)>
            {{ $staff->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label for="menu_id">メニュー</label>
      <select id="menu_id" name="menu_id">
        @foreach ($menus as $menu)
          <option value="{{ $menu->id }}" @selected($menu->id === $reservation->menu_id)>
            {{ $menu->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label for="start_at">開始日時</label>
      <input type="datetime-local" id="start_at" name="start_at"
        value="{{ $reservation->start_at->format('Y-m-d\TH:i') }}">
    </div>

    <div>
      <label for="status">ステータス</label>
      <select id="status" name="status">
        @foreach (\App\Enums\ReservationStatus::cases() as $status)
          <option value="{{ $status->value }}" @selected($status === $reservation->status)>
            {{ $status->value }}
          </option>
        @endforeach
      </select>
    </div>

    <button type="submit">予約を更新する</button>
  </form>
</body>

</html>
