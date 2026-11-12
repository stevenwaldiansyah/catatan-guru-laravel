<x-app-layout>
    <x-page-header title="Laporan" description="Unduh semua jurnal dan Absensi Guru" />
    <div class="grid gap-[12px] md:grid-cols-2">
        @foreach (['jurnal' => 'Jurnal mengajar', 'absensi' => 'Absensi'] as $type => $label)
            <a class="card block" href="{{ route('reports.show', $type) }}">
                <h2 class="font-headline text-[20px] font-semibold">{{ $label }}</h2>
            </a>
        @endforeach
    </div>

    @if ($teachers->isNotEmpty())
        <section class="mt-8">
            <h2 class="font-headline text-xl font-semibold">Catatan dan Absensi</h2>
            <p class="mt-1 mb-4 text-[13px] text-muted">Unduh Jurnal dan Absensi Tiap Guru</p>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($teachers as $item)
                    <a href="{{ route('reports.teacher', $item) }}" class="card flex items-center gap-4 p-4 transition hover:border-primary">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full text-[16px] font-semibold text-primary" style="background: var(--nav-active-bg)">{{ mb_strtoupper(mb_substr($item->name, 0, 1)) }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-headline text-[16px] font-semibold">{{ $item->name }}</span>
                            <span class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-[12px] text-muted">
                                <span>{{ $item->journals_count }} jurnal</span>
                                <span>{{ $item->sessions_count }} absensi</span>
                            </span>
                        </span>
                        <svg class="shrink-0 text-muted" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</x-app-layout>
