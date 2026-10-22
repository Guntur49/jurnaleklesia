<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:DejaVu Sans,sans-serif;color:#31251d;font-size:11px}h1{color:#762c12;font-size:22px;margin:0 0 5px}.sub{color:#75665a;margin-bottom:22px}.day{border:1px solid #e8ded0;padding:11px 13px;margin-bottom:8px;page-break-inside:avoid}.date{font-weight:bold;color:#a9431d;margin-bottom:7px}.label{font-weight:bold;color:#762c12;margin:7px 0 3px}.content{line-height:1.5;white-space:pre-wrap}.empty{color:#9a8a7a;font-style:italic}.foot{margin-top:20px;color:#827468;font-size:9px}
    </style>
</head>
<body>
    <h1>Jurnal Eklesia</h1>
    <div class="sub">Nama: <strong>{{ $user->name }}</strong> · Jabatan: {{ $user->position }} · Periode: {{ $monthLabel }}</div>
    @foreach($days as $item)
        <div class="day">
            <div class="date">{{ $item['date']->locale('id')->translatedFormat('l, d F Y') }}</div>
            @if($item['journal'])
                <div class="label">Yang dikerjakan</div>
                <div class="content">{{ $item['journal']->content }}</div>
                <div class="label">Hasil hari ini</div>
                <div class="content">{{ $item['journal']->result ?: 'Belum dicatat untuk jurnal lama.' }}</div>
            @else
                <div class="empty">Belum ada jurnal pada tanggal ini.</div>
            @endif
        </div>
    @endforeach
    <div class="foot">Dicetak pada {{ now()->locale('id')->translatedFormat('d F Y H:i') }}</div>
</body>
</html>
