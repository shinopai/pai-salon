<?php

return [
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で入力してください。',
    'email' => ':attributeは有効なメールアドレスを入力してください。',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
    ],
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください。',
    ],
    'integer' => ':attributeは整数で入力してください。',
    'numeric' => ':attributeは数値で入力してください。',
    'date' => ':attributeは有効な日付を入力してください。',
    'date_format' => ':attributeは:format形式で入力してください。',
    'after' => ':attributeは:dateより後の日付を指定してください。',
    'after_or_equal' => ':attributeは:date以降の日付を指定してください。',
    'before' => ':attributeは:dateより前の日付を指定してください。',
    'before_or_equal' => ':attributeは:date以前の日付を指定してください。',
    'exists' => '選択した:attributeは存在しません。',
    'unique' => 'その:attributeはすでに使用されています。',
    'in' => '選択した:attributeは無効です。',
    'not_in' => '選択した:attributeは無効です。',
    'boolean' => ':attributeは真偽値で指定してください。',
    'confirmed' => ':attributeが一致しません。',
    'same' => ':attributeが一致しません。',
    'regex' => ':attributeの形式が正しくありません。',

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'customer_name' => 'お名前',
        'customer_email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード確認',
        'current_password' => '現在のパスワード',
        'new_password' => '新しいパスワード',

        'role' => '権限',

        'menu_id' => 'メニュー',
        'staff_id' => 'スタッフ',
        'start_at' => '開始日時',
        'end_at' => '終了日時',
        'date' => '日付',
        'status' => '予約ステータス',

        'duration' => '施術時間',
        'price' => '料金',
        'description' => '説明',

        'open_at' => '営業開始時間',
        'close_at' => '営業終了時間',

        'holiday_date' => '休業日',
        'reason' => '休業理由'
    ],
];
