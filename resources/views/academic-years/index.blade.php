<x-app-layout>
    <x-page-header title="Tahun ajaran" description="Semester ganjil dan genap dibuat otomatis. Tahun aktif dipilih di Pengaturan.">
        <x-slot:actions><a class="btn btn-primary" href="{{ route('academic-years.create') }}">Tambah tahun</a></x-slot:actions>
    </x-page-header>
    @forelse ($years as $year)
        <article class="card mb-[12px]">
            <div class="flex flex-wrap items-center justify-between gap-[12px]">
                <h2 class="font-headline text-[20px] font-semibold">{{ $year->name }}</h2>
                @if ($year->is_active)<x-badge tone="success">Tahun aktif</x-badge>@endif
            </div>
            <ul class="mt-[12px] text-[15px]">
                @foreach ($year->semesters as $semester)
                    <li class="min-h-[44px]">{{ $semester->name }} · {{ $semester->starts_on->translatedFormat('d M Y') }} – {{ $semester->ends_on->translatedFormat('d M Y') }} @if($semester->is_active)<x-badge tone="info">Semester aktif</x-badge>@endif</li>
                @endforeach
            </ul>
            <div class="mt-[12px] flex gap-[12px]">
                <a class="btn btn-secondary" href="{{ route('academic-years.edit', $year) }}">Ubah</a>
                <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('academic-years.destroy', $year)), title: 'Hapus tahun ajaran {{ $year->name }}?' })">Hapus</button>
            </div>
        </article>
    @empty
        <x-empty title="Belum ada tahun ajaran" />
    @endforelse
    <div class="mt-[18px]">{{ $years->links() }}</div>
</x-app-layout>
