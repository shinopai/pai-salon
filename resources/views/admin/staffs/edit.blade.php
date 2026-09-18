<h1>スタッフ編集</h1>

<form>
  <label for="name">氏名</label>
  <input type="text" id="name" name="name" value="{{ $staff->name }}">

  <label for="email">メールアドレス</label>
  <input type="email" id="email" name="email" value="{{ $staff->user->email }}">

  <label for="role">権限</label>
  <select id="role" name="role">
    <option value="staff" @selected($staff->role->value === 'staff')>スタッフ</option>
    <option value="admin" @selected($staff->role->value === 'admin')>管理者</option>
  </select>
</form>
