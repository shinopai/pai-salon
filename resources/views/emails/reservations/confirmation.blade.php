<p>{{ $reservation->customer_name }} 様</p>

<p>ご予約ありがとうございます。</p>

<p>以下の内容で予約を承りました。</p>

<ul>
  <li>予約番号：{{ $reservation->reservation_number }}</li>
  <li>予約日時：
    {{ $reservation->start_at->format('Y年m月d日 H:i') }}
    ～ {{ $reservation->end_at->format('H:i') }}
  </li>
  <li>メニュー：{{ $reservation->menu->name }}</li>
  <li>担当スタッフ：{{ $reservation->staff->name }}</li>
</ul>

<p>予約内容をご確認ください。</p>

<p>
  予約をキャンセルする場合は、以下のURLから手続きを行ってください。
</p>

<p>
  {{-- <a
    href="{{ route('reservations.cancel.show', [
        'reservation_number' => $reservation->reservation_number,
        'token' => $cancellationToken,
    ]) }}">
    予約キャンセルページ
  </a> --}}
</p>

<p>よろしくお願いいたします。</p>
