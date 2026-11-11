<x-app-layout>
    <x-page-header title="Catatan mengajar" description="Catatan kegiatan belajar dan absensi siswa ada di halaman ini.">
        <x-slot:actions>
            <a class="btn btn-primary" href="{{ route('journals.create') }}">Tambah jurnal</a>
        </x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-[18px] grid gap-[12px] md:grid-cols-4">
        <input class="field" type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Dari tanggal">
        <input class="field" type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Sampai tanggal">
        <input class="field md:col-span-2" name="q" value="{{ request('q') }}" placeholder="Cari materi" aria-label="Cari materi">
        @if (request()->filled('attendance_date'))
            <input type="hidden" name="attendance_date" value="{{ request('attendance_date') }}">
        @endif
        <button class="btn btn-secondary md:col-span-4 md:w-fit">Filter</button>
    </form>
    @if ($journals->isEmpty())
        <x-empty title="Belum ada jurnal" />
    @else
        <div class="space-y-[12px] md:hidden">
            @foreach ($journals as $journal)
                <a class="card card-link" href="{{ route('journals.show', $journal) }}">
                    <p class="font-headline text-[20px] font-semibold text-primary">{{ $journal->date->translatedFormat('d M Y') }}</p>
                    <p class="text-[13px] text-muted">{{ $journal->schoolClass->name }} · {{ $journal->subject->name }}</p>
                    <p class="mt-[6px]">{{ \Illuminate\Support\Str::limit($journal->material, 120) }}</p>
                </a>
            @endforeach
        </div>
        <div class="hidden md:block table-wrap">
            <table class="data-table">
                <thead><tr><th>Tanggal</th><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($journals as $journal)
                        @php($journalUrl = route('journals.show', $journal))
                        <tr class="row-link">
                            <td><a class="row-hit is-primary" href="{{ $journalUrl }}">{{ $journal->date->translatedFormat('d M Y') }}</a></td>
                            <td><a class="row-hit" href="{{ $journalUrl }}">{{ $journal->teacher->name }}</a></td>
                            <td><a class="row-hit" href="{{ $journalUrl }}">{{ $journal->schoolClass->name }}</a></td>
                            <td><a class="row-hit" href="{{ $journalUrl }}">{{ $journal->subject->name }}</a></td>
                            <td><a class="row-hit" href="{{ $journalUrl }}"><x-badge :tone="$journal->status->value === 'completed' ? 'success' : 'warning'">{{ $journal->status->label() }}</x-badge></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-[18px]">{{ $journals->links() }}</div>
    @endif

    <section id="absensi" class="mt-8">
        <div class="mb-4">
            <h2 class="font-headline text-xl font-semibold">Absensi</h2>
            <p class="mt-1 text-[13px] text-muted">Satu kombinasi kelas, mapel, tanggal, dan sesi hanya boleh tercatat sekali.</p>
        </div>
        <form method="GET" action="{{ route('journals.index') }}#absensi" class="mb-[18px] flex flex-wrap gap-[12px]">
            @foreach (request()->except('attendance_date', 'absensi') as $name => $value)
                @if (is_string($value))
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @endforeach
            <input class="field max-w-[220px]" type="date" name="attendance_date" value="{{ request('attendance_date') }}" aria-label="Tanggal absensi">
            <button class="btn btn-secondary">Filter</button>
        </form>
        @if ($sessions->isEmpty())
            <x-empty title="Belum ada absensi" />
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Sesi</th><th>Guru</th><th>Siswa</th></tr></thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            @php($attendanceUrl = route('attendance.show', $session))
                            <tr class="row-link">
                                <td><a class="row-hit is-primary" href="{{ $attendanceUrl }}">{{ $session->date->translatedFormat('d M Y') }}</a></td>
                                <td><a class="row-hit" href="{{ $attendanceUrl }}">{{ $session->schoolClass->name }}</a></td>
                                <td><a class="row-hit" href="{{ $attendanceUrl }}">{{ $session->subject->name }}</a></td>
                                <td><a class="row-hit" href="{{ $attendanceUrl }}">{{ $session->session_label }}</a></td>
                                <td><a class="row-hit" href="{{ $attendanceUrl }}">{{ $session->teacher->name }}</a></td>
                                <td><a class="row-hit" href="{{ $attendanceUrl }}">{{ $session->records_count }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-[18px]">{{ $sessions->links() }}</div>
        @endif
    </section>
</x-app-layout>
