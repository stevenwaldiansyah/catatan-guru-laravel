<x-app-layout>
    <x-page-header title="Rekap absensi" description="Jumlah status pada periode dan kelas yang dipilih." />
    <form method="GET" class="mb-[18px] grid gap-[12px] md:grid-cols-4">
        <select class="field" name="class_id" aria-label="Kelas" required>
            <option value="">Pilih kelas</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>
        <input class="field" type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Dari">
        <input class="field" type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Sampai">
        <button class="btn btn-secondary">Tampilkan</button>
    </form>
    @if ($rows->isEmpty())
        <x-empty title="Pilih kelas untuk melihat rekap" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Siswa</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th></tr></thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['student']->name }}</td>
                            <td>{{ $row['hadir'] }}</td>
                            <td>{{ $row['izin'] }}</td>
                            <td>{{ $row['sakit'] }}</td>
                            <td>{{ $row['alpa'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
