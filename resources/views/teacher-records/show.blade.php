<x-app-layout>
    @php
        $filters = array_filter([
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
        ], fn ($value) => is_string($value) && $value !== '');
    @endphp
    <x-page-header title="Catatan {{ $teacher->name }}" description="Riwayat jurnal dan absensi milik {{ $teacher->name }}.">
        <x-slot:actions>
            <a class="btn btn-ghost" href="{{ route('reports.index') }}">Laporan</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="mb-6 grid gap-3 md:grid-cols-4">
        <input class="field" type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Dari tanggal">
        <input class="field" type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Sampai tanggal">
        <button class="btn btn-secondary md:w-fit">Filter</button>
    </form>

    <section class="mb-8">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-headline text-xl font-semibold">Jurnal</h2>
                <p class="mt-1 text-[13px] text-muted">{{ $journals->count() }} catatan pada filter ini.</p>
            </div>
            <div class="flex gap-2">
                <a class="btn btn-secondary" href="{{ route('reports.teacher.journals.csv', array_merge(['teacher' => $teacher], $filters)) }}">CSV</a>
                <a class="btn btn-primary" href="{{ route('reports.teacher.journals.pdf', array_merge(['teacher' => $teacher], $filters)) }}">PDF</a>
            </div>
        </div>
        @if ($journals->isEmpty())
            <x-empty title="Belum ada jurnal" />
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Sesi</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($journals as $journal)
                            @php($journalUrl = route('journals.show', $journal))
                            <tr class="row-link">
                                <td><a class="row-hit is-primary" href="{{ $journalUrl }}">{{ $journal->date->translatedFormat('d M Y') }}</a></td>
                                <td><a class="row-hit" href="{{ $journalUrl }}">{{ $journal->schoolClass->name }}</a></td>
                                <td><a class="row-hit" href="{{ $journalUrl }}">{{ $journal->subject->name }}</a></td>
                                <td><a class="row-hit" href="{{ $journalUrl }}">{{ $journal->session_label }}</a></td>
                                <td><a class="row-hit" href="{{ $journalUrl }}"><x-badge :tone="$journal->status->value === 'completed' ? 'success' : 'warning'">{{ $journal->status->label() }}</x-badge></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section>
        <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-headline text-xl font-semibold">Absensi</h2>
                <p class="mt-1 text-[13px] text-muted">{{ $sessions->count() }} sesi pada filter ini.</p>
            </div>
            <div class="flex gap-2">
                <a class="btn btn-secondary" href="{{ route('reports.teacher.attendance.csv', array_merge(['teacher' => $teacher], $filters)) }}">CSV</a>
                <a class="btn btn-primary" href="{{ route('reports.teacher.attendance.pdf', array_merge(['teacher' => $teacher], $filters)) }}">PDF</a>
            </div>
        </div>
        @if ($sessions->isEmpty())
            <x-empty title="Belum ada absensi" />
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Sesi</th><th>Siswa</th></tr></thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            @php($attendanceUrl = route('attendance.show', $session))
                            <tr class="row-link">
                                <td><a class="row-hit is-primary" href="{{ $attendanceUrl }}">{{ $session->date->translatedFormat('d M Y') }}</a></td>
                                <td><a class="row-hit" href="{{ $attendanceUrl }}">{{ $session->schoolClass->name }}</a></td>
                                <td><a class="row-hit" href="{{ $attendanceUrl }}">{{ $session->subject->name }}</a></td>
                                <td><a class="row-hit" href="{{ $attendanceUrl }}">{{ $session->session_label }}</a></td>
                                <td><a class="row-hit" href="{{ $attendanceUrl }}">{{ $session->records_count }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-app-layout>
