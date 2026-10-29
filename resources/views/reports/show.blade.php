<x-app-layout>
    <x-page-header :title="$report['title']" :description="$report['period']">
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route('reports.csv', array_merge(['type' => $type], request()->query())) }}">CSV</a>
            <a class="btn btn-primary" href="{{ route('reports.pdf', array_merge(['type' => $type], request()->query())) }}">PDF</a>
        </x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-[18px] grid gap-[12px] md:grid-cols-3">
        <input class="field" type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Dari tanggal">
        <input class="field" type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Sampai tanggal">
        <select class="field" name="class_id" aria-label="Kelas">
            <option value="">Semua kelas</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary">Terapkan filter</button>
    </form>
    @if (count($report['rows']) === 0)
        <x-empty title="Tidak ada data pada filter ini" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr>@foreach ($report['columns'] as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($report['rows'] as $row)
                        <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
