@extends('layouts.app')

@section('title', 'Jurnal bulanan')

@section('content')
    <div class="page-head">
        <div><p class="eyebrow">Arsip pekerjaan</p><h1>Jurnal staff</h1><p class="lead">Telusuri catatan harian berdasarkan nama dan bulan.</p></div>
    </div>
    <section class="card">
        <form method="GET" action="{{ route('journals.index') }}" class="filters">
            <div class="field"><label for="staff_id">Pilih staff</label><select id="staff_id" name="staff_id" required><option value="">Pilih nama staff</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected((string) $selectedStaffId === (string) $member->id)>{{ $member->name }} · {{ $member->position }}</option>@endforeach</select></div>
            <div class="field"><label for="month">Bulan</label><select id="month" name="month">@foreach($months as $number => $name)<option value="{{ $number }}" @selected($month === $number)>{{ $name }}</option>@endforeach</select></div>
            <div class="field"><label for="year">Tahun</label><input id="year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" required></div>
            <button class="btn" type="submit">Tampilkan</button>
        </form>
    </section>
    @if($selectedStaff)
        <div class="report-meta">
            <div><p class="eyebrow">Laporan bulanan</p><h2 class="section-title" style="margin:0">{{ $selectedStaff->name }} · {{ \Illuminate\Support\Carbon::create($year, $month, 1)->locale('id')->translatedFormat('F Y') }}</h2></div>
            @if(in_array(auth()->user()->role, ['admin', 'owner'], true))
                <a class="btn btn-secondary" href="{{ route('journals.pdf', ['user' => $selectedStaff, 'month' => $month, 'year' => $year]) }}">Unduh PDF</a>
            @endif
        </div>
        <section class="calendar-wrap" aria-label="Kalender laporan {{ $selectedStaff->name }}">
            <div class="calendar-legend" aria-label="Keterangan status jurnal">
                <span class="legend-item"><span class="legend-dot legend-dot--complete"></span>Sudah diisi</span>
                <span class="legend-item"><span class="legend-dot legend-dot--empty"></span>Belum diisi</span>
            </div>
            <div class="calendar-grid" role="grid" aria-label="Kalender {{ \Illuminate\Support\Carbon::create($year, $month, 1)->locale('id')->translatedFormat('F Y') }}">
                @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
                    <div class="calendar-weekday" role="columnheader">{{ $weekday }}</div>
                @endforeach
                @foreach($calendarWeeks as $week)
                    @foreach($week as $item)
                        <div class="calendar-cell" role="gridcell">
                            @if($item)
                                @php
                                    $journal = $item['journal'];
                                    $dateLabel = $item['date']->locale('id')->translatedFormat('l, d F Y');
                                @endphp
                                <button
                                    type="button"
                                    class="calendar-day {{ $journal ? 'calendar-day--complete' : 'calendar-day--empty' }}"
                                    aria-label="{{ $dateLabel }} — {{ $journal ? 'sudah diisi' : 'belum diisi' }}"
                                    data-date="{{ $dateLabel }}"
                                    data-status="{{ $journal ? 'complete' : 'empty' }}"
                                    data-journal-content="{{ $journal?->content ?? '' }}"
                                    data-journal-result="{{ $journal?->result ?? '' }}"
                                >
                                    <span class="calendar-day-number">{{ $item['date']->format('j') }}</span>
                                    <span class="calendar-day-status">{{ $journal ? 'Sudah diisi' : 'Belum diisi' }}</span>
                                </button>
                            @else
                                <div class="calendar-blank" aria-hidden="true"></div>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </section>
        <dialog class="report-modal" id="journal-report-modal" aria-labelledby="journal-modal-date">
            <div class="report-modal-inner">
                <div class="report-modal-head">
                    <div><p class="eyebrow" style="margin-top:0">Laporan harian · {{ $selectedStaff->name }}</p><h2 class="report-modal-title" id="journal-modal-date"></h2></div>
                    <button type="button" class="report-modal-close" aria-label="Tutup laporan" data-close-report>&times;</button>
                </div>
                <div id="journal-modal-body"></div>
            </div>
        </dialog>
        <script>
            (function () {
                var modal = document.getElementById('journal-report-modal');
                var title = document.getElementById('journal-modal-date');
                var body = document.getElementById('journal-modal-body');

                function addReportSection(label, content) {
                    var section = document.createElement('section');
                    section.className = 'modal-report-section';
                    var heading = document.createElement('h3');
                    heading.textContent = label;
                    var paragraph = document.createElement('p');
                    paragraph.textContent = content;
                    section.append(heading, paragraph);
                    body.append(section);
                }

                document.querySelectorAll('.calendar-day').forEach(function (day) {
                    day.addEventListener('click', function () {
                        title.textContent = day.dataset.date;
                        body.replaceChildren();

                        if (day.dataset.status === 'empty') {
                            var message = document.createElement('div');
                            message.className = 'modal-empty';
                            message.textContent = 'Belum ada jurnal yang diisi untuk tanggal ini.';
                            body.append(message);
                        } else {
                            addReportSection('Yang dikerjakan', day.dataset.journalContent);
                            addReportSection('Hasil hari ini', day.dataset.journalResult || 'Belum dicatat untuk jurnal lama.');
                        }

                        modal.showModal();
                    });
                });

                modal.querySelector('[data-close-report]').addEventListener('click', function () {
                    modal.close();
                });

                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        modal.close();
                    }
                });
            })();
        </script>
    @else
        <div class="card section" style="text-align:center;padding:42px 20px"><div class="stat-icon" style="margin:0 auto 15px">J</div><h2>Pilih staff untuk melihat jurnal</h2><p class="muted">Daftar akan menampilkan semua tanggal pada bulan yang dipilih, termasuk hari tanpa catatan.</p></div>
    @endif
@endsection
