<x-app-layout>
    <x-page-header title="Guru" description="Kelola akun guru dan hubungkan profil dengan pengguna.">
        <x-slot:actions>
            <a href="{{ route('teachers.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Tambah guru</a>
        </x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-[18px] flex flex-col gap-[12px] sm:flex-row">
        <label class="sr-only" for="q">Cari guru</label>
        <input id="q" name="q" value="{{ request('q') }}" class="field" placeholder="Cari nama, NIP, atau email">
        <button class="btn btn-secondary">Cari</button>
    </form>
    @if ($teachers->isEmpty())
        <x-empty title="Belum ada guru" text="Tambahkan guru agar jadwal dan jurnal dapat diisi." />
    @else
        <div class="space-y-[12px] md:hidden">
            @foreach ($teachers as $teacher)
                <article class="card">
                    <h2 class="font-headline text-[20px] font-semibold">{{ $teacher->name }}</h2>
                    <p class="text-[13px] text-muted">{{ $teacher->nip ?: 'NIP belum diisi' }} · {{ $teacher->user->email }}</p>
                    <div class="mt-[12px]"><x-badge :tone="$teacher->user->is_active ? 'success' : 'neutral'">{{ $teacher->user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></div>
                    <div class="mt-[12px] flex flex-wrap gap-[12px]">
                        <a class="btn btn-secondary" href="{{ route('teachers.edit', $teacher) }}">Ubah</a>
                        <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('teachers.destroy', $teacher)), title: 'Arsipkan akun {{ $teacher->name }}?' })">Arsipkan</button>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="hidden md:block">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Nama</th><th>NIP</th><th>Akun</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($teachers as $teacher)
                            <tr>
                                <td>{{ $teacher->name }}</td>
                                <td>{{ $teacher->nip ?: '-' }}</td>
                                <td>{{ $teacher->user->username }}<br><span class="text-[13px] text-muted">{{ $teacher->user->email }}</span></td>
                                <td><x-badge :tone="$teacher->user->is_active ? 'success' : 'neutral'">{{ $teacher->user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                <td class="flex gap-[6px]">
                                    <a class="btn btn-secondary" href="{{ route('teachers.edit', $teacher) }}">Ubah</a>
                                    <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('teachers.destroy', $teacher)), title: 'Arsipkan akun {{ $teacher->name }}?' })">Arsipkan</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-[18px]">{{ $teachers->links() }}</div>
    @endif
</x-app-layout>
