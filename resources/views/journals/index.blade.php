<x-app-layout>
    <x-page-header title="Jurnal mengajar" description="Draf dapat dihapus oleh guru. Jurnal selesai tetap dapat diperbarui, tetapi tidak dihapus oleh guru.">
        <x-slot:actions><a class="btn btn-primary" href="{{ route('journals.create') }}">Tambah jurnal</a></x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-[18px] grid gap-[12px] md:grid-cols-4">
        <input class="field" type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Dari tanggal">
        <input class="field" type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Sampai tanggal">
        <input class="field md:col-span-2" name="q" value="{{ request('q') }}" placeholder="Cari materi" aria-label="Cari materi">
        <button class="btn btn-secondary md:col-span-4 md:w-fit">Filter</button>
    </form>
    @if ($journals->isEmpty())
        <x-empty title="Belum ada jurnal" />
    @else
        <div class="space-y-[12px] md:hidden">
            @foreach ($journals as $journal)
                <article class="card">
                    <a class="font-headline text-[20px] font-semibold text-primary" href="{{ route('journals.show', $journal) }}">{{ $journal->date->translatedFormat('d M Y') }}</a>
                    <p class="text-[13px] text-muted">{{ $journal->schoolClass->name }} · {{ $journal->subject->name }}</p>
                    <p class="mt-[6px]">{{ \Illuminate\Support\Str::limit($journal->material, 120) }}</p>
                </article>
            @endforeach
        </div>
        <div class="hidden md:block table-wrap">
            <table class="data-table">
                <thead><tr><th>Tanggal</th><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($journals as $journal)
                        <tr>
                            <td><a class="text-primary" href="{{ route('journals.show', $journal) }}">{{ $journal->date->translatedFormat('d M Y') }}</a></td>
                            <td>{{ $journal->teacher->name }}</td>
                            <td>{{ $journal->schoolClass->name }}</td>
                            <td>{{ $journal->subject->name }}</td>
                            <td><x-badge :tone="$journal->status->value === 'completed' ? 'success' : 'warning'">{{ $journal->status->label() }}</x-badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-[18px]">{{ $journals->links() }}</div>
    @endif
</x-app-layout>
