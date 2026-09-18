<h1>スタッフ一覧</h1>

@foreach ($staffs as $staff)
  <div>{{ $staff->name }}</div>
@endforeach
