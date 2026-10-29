<x-app-layout>
    <x-page-header title="Mata pelajaran" description="Kode mapel dipakai sebagai identitas unik.">
        <x-slot:actions><a class="btn btn-primary" href="{{ route('subjects.create') }}">Tambah mapel</a></x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-[18px] flex gap-[12px]"><input class="field" name="q" value="{{ request('q') }}" aria-label="Cari mapel" placeholder="Cari nama atau kode"><button class="btn btn-secondary">Cari</button></form>
    @if ($subjects->isEmpty())
        <x-empty title="Belum ada mata pelajaran" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Kode</th><th>Nama</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach ($subjects as $subject)
                        <tr>
                            <td class="font-mono text-[13px]">{{ $subject->code }}</td>
                            <td>{{ $subject->name }}</td>
                            <td><x-badge :tone="$subject->is_active ? 'success' : 'neutral'">{{ $subject->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                            <td class="flex gap-[6px]">
                                <a class="btn btn-secondary" href="{{ route('subjects.edit', $subject) }}">Ubah</a>
                                <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('subjects.destroy', $subject)), title: 'Hapus mapel {{ $subject->name }}?' })">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-[18px]">{{ $subjects->links() }}</div>
    @endif
</x-app-layout>
