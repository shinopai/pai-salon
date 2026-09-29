<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 80" width="320" height="80">
  <defs>
    <!-- パレットカラーを用いたやわらかいグラデーション -->
    <linearGradient id="paiSoftGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#00838F" />
      <stop offset="100%" stop-color="#00ACC1" />
    </linearGradient>
    <linearGradient id="paiSoftGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#00ACC1" />
      <stop offset="100%" stop-color="#80DEEA" />
    </linearGradient>
  </defs>

  <!-- シンボルマーク：やわらかい髪のウェーブ＆ふんわり包み込むサークル -->
  <g transform="translate(12, 10)">
    <!-- 背景：優しく包み込む丸（Cosmic Dawn） -->
    <circle cx="30" cy="30" r="28" fill="#E0F7FA" opacity="0.75" />

    <!-- 外側のやわらかいリング（Calm Current） -->
    <circle cx="30" cy="30" r="23" stroke="#B2DFDB" stroke-width="2.5" fill="none"
      stroke-dasharray="100 20" stroke-linecap="round" transform="rotate(-30 30 30)" />

    <!-- ふんわりしたヘアウェーブ 1（メイン：丸みのあるS字曲線） -->
    <path d="M 20,38 C 20,24 28,16 35,18 C 42,20 40,30 31,31 C 22,32 20,40 28,42 C 34,44 40,38 40,34"
      stroke="url(#paiSoftGrad1)" stroke-width="5" fill="none" stroke-linecap="round" stroke-linejoin="round" />

    <!-- 寄り添うヘアウェーブ 2（アクセント：重ねて立体感とツヤを演出） -->
    <path d="M 23,26 C 28,21 35,21 38,25" stroke="url(#paiSoftGrad2)" stroke-width="3" fill="none"
      stroke-linecap="round" />

    <!-- 髪のツヤ・輝きを表す小さな丸アクセント -->
    <circle cx="38" cy="18" r="2.5" fill="#80DEEA" />
  </g>

  <!-- テキスト部分 -->
  <g transform="translate(85, 48)">
    <!-- Pai（メイン：Deep Ocean #00838F） -->
    <text x="0" y="0" font-family="system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
      font-size="30" font-weight="700" letter-spacing="1" fill="#00838F">Pai</text>

    <!-- Salon（サブ：Tranquil Turquoise #00ACC1） -->
    <text x="52" y="0" font-family="system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
      font-size="30" font-weight="300" letter-spacing="2" fill="#00ACC1">Salon</text>

    <!-- サブテキスト -->
    <text x="2" y="16" font-family="system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
      font-size="8.5" font-weight="600" letter-spacing="3.5" fill="#00ACC1" opacity="0.85">HAIR SALON</text>
  </g>
</svg>
