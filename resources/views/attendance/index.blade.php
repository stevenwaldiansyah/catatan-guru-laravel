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
                        <tr>
                            <td><a class="text-primary" href="{{ route('attendance.show', $session) }}">{{ $session->date->translatedFormat('d M Y') }}</a></td>
                            <td>{{ $session->schoolClass->name }}</td>
                            <td>{{ $session->subject->name }}</td>
                            <td>{{ $session->session_label }}</td>
                            <td>{{ $session->teacher->name }}</td>
                            <td>{{ $session->records_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-[18px]">{{ $sessions->links() }}</div>
    @endif
</x-app-layout>
