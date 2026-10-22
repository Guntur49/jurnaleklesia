@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="page-head">
        <div>
            <p class="eyebrow">Ruang kerja Eklesia</p>
            <h1>Halo, {{ auth()->user()->name }}.</h1>
            <p class="lead">{{ now()->locale('id')->translatedFormat('l, d F Y') }} · {{ ucfirst(auth()->user()->role) }}</p>
        </div>
        <a class="btn btn-secondary" href="{{ route('journals.index') }}">Lihat jurnal bulanan</a>
    </div>

    @if(auth()->user()->role === 'staff')
        <div class="grid grid-3">
            <div class="card stat"><div class="stat-icon">01</div><div class="stat-label">Jabatan</div><div class="stat-value" style="font-size:23px">{{ auth()->user()->position }}</div></div>
            <div class="card stat"><div class="stat-icon">02</div><div class="stat-label">Jadwal pengisian</div><div class="stat-value" style="font-size:23px">{{ substr(auth()->user()->journal_start_time, 0, 5) }}–{{ substr(auth()->user()->journal_end_time, 0, 5) }}</div></div>
            <div class="card stat"><div class="stat-icon">03</div><div class="stat-label">Jurnal hari ini</div><div class="stat-value" style="font-size:23px">{{ $todayJournal ? 'Sudah tersimpan' : 'Belum diisi' }}</div></div>
        </div>
        <section class="card section">
            <p class="eyebrow">Catatan akhir pekerjaan</p>
            <h2>Tulis jurnal hari ini</h2>
            <p class="lead">Ceritakan pekerjaan yang sudah diselesaikan, hal yang sedang berjalan, atau catatan untuk tim.</p>
            @if($canWrite)
                <form method="POST" action="{{ route('journals.store') }}" style="margin-top:20px">
                    @csrf
                    <div class="field">
                        <label for="content">Apa yang dikerjakan hari ini?</label>
                        <textarea id="content" name="content" minlength="5" maxlength="10000" placeholder="Tuliskan pekerjaanmu dengan jelas..." required>{{ old('content', $todayJournal?->content) }}</textarea>
                    </div>
                    <div class="field" style="margin-top:16px">
                        <label for="result">Apa hasil hari ini?</label>
                        <textarea id="result" name="result" minlength="3" maxlength="10000" placeholder="Contoh: Laporan selesai dan siap ditinjau..." required>{{ old('result', $todayJournal?->result) }}</textarea>
                    </div>
                    <div class="form-actions"><button class="btn" type="submit">{{ $todayJournal ? 'Perbarui jurnal' : 'Simpan jurnal' }}</button></div>
                </form>
            @elseif($todayJournal)
                <div class="entry-note section"><strong>Jurnal tersimpan.</strong><p class="small"><strong>Yang dikerjakan</strong></p><div class="entry-text">{{ $todayJournal->content }}</div><p class="small"><strong>Hasil hari ini</strong></p><div class="entry-text">{{ $todayJournal->result ?: 'Belum dicatat untuk jurnal lama.' }}</div><p class="small" style="margin-bottom:0">Pengisian hanya dapat diubah pada jam {{ substr(auth()->user()->journal_start_time, 0, 5) }}–{{ substr(auth()->user()->journal_end_time, 0, 5) }}.</p></div>
            @else
                <div class="entry-note section">Form jurnal tersedia pukul <strong>{{ substr(auth()->user()->journal_start_time, 0, 5) }}–{{ substr(auth()->user()->journal_end_time, 0, 5) }}</strong>, sesuai jadwal yang ditetapkan admin.</div>
            @endif
        </section>
        <section class="card section">
            <div class="report-meta"><h2 class="section-title" style="margin:0">Jurnal terakhir saya</h2><a class="btn btn-secondary btn-small" href="{{ route('journals.index', ['staff_id' => auth()->id()]) }}">Lihat semua</a></div>
            @forelse($recentJournals as $journal)
                <div class="activity"><div class="avatar">{{ substr(auth()->user()->name, 0, 1) }}</div><div><h3>{{ $journal->journal_date->locale('id')->translatedFormat('l, d F Y') }}</h3><p><strong>Yang dikerjakan:</strong> {{ $journal->content }}</p><p style="margin-top:6px"><strong>Hasil hari ini:</strong> {{ $journal->result ?: 'Belum dicatat untuk jurnal lama.' }}</p></div></div>
            @empty
                <p class="muted">Belum ada jurnal yang tersimpan.</p>
            @endforelse
        </section>
    @elseif(auth()->user()->role === 'owner')
        <div class="grid grid-3">
            <div class="card stat"><div class="stat-icon">T</div><div class="stat-label">Staff aktif</div><div class="stat-value">{{ $staffCount }}</div></div>
            <div class="card stat" style="grid-column:span 2"><div class="stat-label">Ringkasan</div><div class="stat-value" style="font-size:21px;line-height:1.5">Pantau aktivitas terbaru dan perjalanan kerja tim Eklesia.</div></div>
        </div>
        <section class="card section">
            <div class="report-meta"><div><p class="eyebrow">Aktivitas terakhir</p><h2 class="section-title" style="margin:0">Jurnal terbaru dari tim</h2></div><a class="btn" href="{{ route('journals.index') }}">Telusuri jurnal</a></div>
            <div class="activity-list">
                @forelse($recentJournals as $journal)
                    <div class="activity"><div class="avatar">{{ substr($journal->user->name, 0, 1) }}</div><div><h3>{{ $journal->user->name }} <span class="badge">{{ $journal->user->position }}</span></h3><p><strong>Yang dikerjakan:</strong> {{ $journal->content }}</p><p style="margin-top:6px"><strong>Hasil hari ini:</strong> {{ $journal->result ?: 'Belum dicatat untuk jurnal lama.' }}</p></div><div class="date-label">{{ $journal->journal_date->locale('id')->translatedFormat('d M Y') }}</div></div>
                @empty
                    <p class="muted">Belum ada aktivitas jurnal dari staff.</p>
                @endforelse
            </div>
        </section>
    @else
        <div class="page-head"><div><p class="eyebrow">Administrasi</p><h1>Ringkasan tim</h1><p class="lead">Kelola akses anggota dan pantau jurnal pekerjaan.</p></div><a class="btn" href="{{ route('accounts.index') }}">Kelola akun</a></div>
        <div class="grid grid-3">
            <div class="card stat"><div class="stat-icon">S</div><div class="stat-label">Akun staff</div><div class="stat-value">{{ $staffCount }}</div></div>
            <div class="card stat"><div class="stat-icon">O</div><div class="stat-label">Akun owner</div><div class="stat-value">{{ $ownerCount }}</div></div>
            <div class="card stat"><div class="stat-icon">J</div><div class="stat-label">Jurnal terbaru</div><div class="stat-value">{{ $recentJournals->count() }}</div></div>
        </div>
        <section class="card section">
            <div class="report-meta"><div><p class="eyebrow">Aktivitas tim</p><h2 class="section-title" style="margin:0">Jurnal terbaru</h2></div><a class="btn btn-secondary" href="{{ route('journals.index') }}">Lihat jurnal staff</a></div>
            <div class="activity-list">
                @forelse($recentJournals as $journal)
                    <div class="activity"><div class="avatar">{{ substr($journal->user->name, 0, 1) }}</div><div><h3>{{ $journal->user->name }} <span class="badge">{{ $journal->user->position }}</span></h3><p><strong>Yang dikerjakan:</strong> {{ $journal->content }}</p><p style="margin-top:6px"><strong>Hasil hari ini:</strong> {{ $journal->result ?: 'Belum dicatat untuk jurnal lama.' }}</p></div><div class="date-label">{{ $journal->journal_date->locale('id')->translatedFormat('d M Y') }}</div></div>
                @empty
                    <p class="muted">Belum ada jurnal dari staff.</p>
                @endforelse
            </div>
        </section>
    @endif
@endsection
