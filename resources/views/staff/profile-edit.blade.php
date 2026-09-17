<h1>スタッフ情報編集</h1>

<form>
  <div>
    <label for="name">名前</label>
    <input type="text" id="name" name="name" value="{{ $staff->name }}">
  </div>

  <div>
    <label for="email">メールアドレス</label>
    <input type="email" id="email" name="email" value="{{ $staff->user->email }}" readonly>
  </div>

  <div>
    <label for="role">権限</label>
    <input type="text" id="role" name="role" value="{{ $staff->role->value }}" readonly>
  </div>
</form>
