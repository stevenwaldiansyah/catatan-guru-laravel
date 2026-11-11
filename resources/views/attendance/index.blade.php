<x-app-layout>
    <x-page-header title="Absensi" description="Satu kombinasi kelas, mapel, tanggal, dan sesi hanya boleh tercatat sekali.">
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route('attendance.recap') }}">Rekap</a>
            <a class="btn btn-primary" href="{{ route('attendance.create') }}">Isi absensi</a>
        </x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-[18px] flex flex-wrap gap-[12px]">
        <input class="field max-w-[220px]" type="date" name="date" value="{{ request('date') }}" aria-label="Tanggal">
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
</x-app-layout>
