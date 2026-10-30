<x-app-layout>
    <x-page-header title="Dasbor guru" description="Jadwal, jurnal, absensi, dan catatan dari kelas yang menjadi tanggung jawab Anda." />
    @if ($missingProfile)
        <div class="alert alert-error" role="alert">Akun ini belum terhubung ke profil guru. Minta admin untuk menghubungkan akun Anda.</div>
    @else
        <section class="grid grid-cols-1 gap-4 min-[360px]:grid-cols-2 xl:grid-cols-3">
            <x-stat label="Kelas diampu" :value="$classCount" hint="Semester aktif" />
            <x-stat label="Jadwal hari ini" :value="$schedules->count()" />
            <x-stat label="Catatan bulan ini" :value="$noteCount" />
        </section>
        <section class="mt-6 grid gap-6 lg:grid-cols-12">
            <article class="card lg:col-span-8">
                <h2 class="font-headline text-[20px] font-semibold">Jadwal hari ini</h2>
                @if ($schedules->isEmpty())
                    <p class="mt-[12px] text-[15px] text-muted">Tidak ada jadwal mengajar hari ini.</p>
                @else
                    <ul class="mt-4 divide-y divide-line">
                        @foreach ($schedules as $schedule)
                            <li class="flex min-h-[52px] items-center justify-between gap-[12px] py-[12px]">
                                <div>
                                    <p class="font-medium">{{ $schedule->timeRange() }} · {{ $schedule->assignment->schoolClass->name }}</p>
                                    <p class="text-[13px] text-muted">{{ $schedule->assignment->subject->name }}{{ $schedule->room ? ' · '.$schedule->room : '' }}</p>
                                </div>
                                <x-badge :tone="$schedule->attendance_done ? 'success' : 'warning'">{{ $schedule->attendance_done ? 'Absensi terisi' : 'Absensi belum' }}</x-badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
            <article class="card lg:col-span-4">
                <h2 class="font-headline text-[20px] font-semibold">Jurnal terbaru</h2>
                @if ($journals->isEmpty())
                    <p class="mt-[12px] text-[15px] text-muted">Belum ada jurnal.</p>
                @else
                    <ul class="mt-4 divide-y divide-line">
                        @foreach ($journals as $journal)
                            <li class="py-[12px]">
                                <a class="font-medium text-primary" href="{{ route('journals.show', $journal) }}">{{ $journal->date->translatedFormat('d M Y') }} · {{ $journal->schoolClass->name }}</a>
                                <p class="text-[13px] text-muted">{{ $journal->subject->name }} · {{ $journal->status->label() }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
        </section>
    @endif
</x-app-layout>
