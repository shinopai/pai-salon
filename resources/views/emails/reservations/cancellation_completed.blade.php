<p>{{ $reservation->customer_name }} 様</p>

<p>ご予約のキャンセルが完了しました。</p>

<p>予約番号：{{ $reservation->reservation_number }}</p>
<p>予約日時：{{ $reservation->start_at->format('Y年m月d日 H:i') }}</p>
<p>メニュー：{{ $reservation->menu->name }}</p>
<p>担当スタッフ：{{ $reservation->staff->name }}</p>
