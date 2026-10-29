<x-app-layout>
    <x-page-header title="Laporan" description="Ekspor memakai hak akses yang sama dengan halaman asalnya." />
    <div class="grid gap-[12px] md:grid-cols-2">
        @foreach (['jurnal' => 'Jurnal mengajar', 'absensi' => 'Absensi', 'catatan' => 'Catatan siswa'] as $type => $label)
            <a class="card block" href="{{ route('reports.show', $type) }}">
                <h2 class="font-headline text-[20px] font-semibold">{{ $label }}</h2>
                <p class="mt-[6px] text-[13px] text-muted">Filter, lalu unduh CSV atau PDF.</p>
            </a>
        @endforeach
    </div>
</x-app-layout>
