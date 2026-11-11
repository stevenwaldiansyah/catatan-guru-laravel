<x-app-layout>
    @if ($missingProfile)
        <x-page-header title="Dasbor guru" description="Jadwal, jurnal, dan absensi dari kelas yang menjadi tanggung jawab Anda." />
        <div class="alert alert-error" role="alert">Akun ini belum terhubung ke profil guru. Minta admin untuk menghubungkan akun Anda.</div>
    @else
        <section class="card mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[13px] text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
                <h1 class="mt-1 font-headline text-2xl font-semibold">{{ $greeting }}, {{ auth()->user()->name }}!</h1>
                <p class="mt-1 max-w-[62ch] text-[14px] text-muted">Pantau aktivitas mengajar dan kelola administrasi pembelajaran Anda.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="btn btn-primary" href="{{ route('journals.create') }}">Tambah Jurnal</a>
                <a class="btn btn-secondary" href="{{ route('schedules.index') }}">Lihat jadwal</a>
                <a class="btn btn-secondary" href="{{ route('reports.index') }}">Lihat laporan</a>
            </div>
        </section>
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat label="Total kelas" :value="$classCount" icon="school" hint="Kelas yang diampu" />
            <x-stat label="Total siswa" :value="$studentCount" icon="graduation-cap" hint="Siswa aktif di kelas Anda" />
            <x-stat label="Jadwal hari ini" :value="$schedules->count()" icon="calendar-days" hint="Sesi mengajar hari ini" />
            <x-stat label="Catatan bulan ini" :value="$journalMonth" icon="notebook-pen" hint="Catatan mengajar bulan ini" />
        </section>
        <section class="mt-6 grid gap-6 lg:grid-cols-12">
            <article class="card lg:col-span-5">
                <h2 class="font-headline text-[20px] font-semibold">Jadwal hari ini</h2>
                @if ($schedules->isEmpty())
                    <p class="mt-4 text-[14px] text-muted">Tidak ada jadwal mengajar hari ini.</p>
                @else
                    <ul class="mt-4 divide-y divide-line">
                        @foreach ($schedules as $schedule)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div>
                                    <p class="text-[13px] font-semibold text-primary">{{ $schedule->timeRange() }}</p>
                                    <p class="font-medium">{{ $schedule->assignment->subject->name }}</p>
                                    <p class="text-[13px] text-muted">{{ $schedule->assignment->schoolClass->name }}{{ $schedule->room ? ' · '.$schedule->room : '' }}</p>
                                </div>
                                <x-badge :tone="$schedule->attendance_done ? 'success' : 'warning'">{{ $schedule->attendance_done ? 'Sudah mengajar' : 'Belum diisi' }}</x-badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
            <article class="card lg:col-span-7">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="font-headline text-[20px] font-semibold">Catatan mengajar terbaru</h2>
                    <a class="text-[13px] font-semibold text-primary" href="{{ route('journals.index') }}">Lihat semua</a>
                </div>
                @if ($journals->isEmpty())
                    <p class="text-[14px] text-muted">Belum ada catatan mengajar.</p>
                @else
                    <div class="table-wrap border-0 shadow-none">
                        <table class="data-table min-w-[520px]">
                            <thead>
                                <tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Materi</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($journals as $journal)
                                    @php($journalUrl = route('journals.show', $journal))
                                    <tr class="row-link">
                                        <td><a class="row-hit is-primary" href="{{ $journalUrl }}">{{ $journal->date->translatedFormat('d M Y') }}</a></td>
                                        <td><a class="row-hit" href="{{ $journalUrl }}">{{ $journal->schoolClass->name }}</a></td>
                                        <td><a class="row-hit" href="{{ $journalUrl }}">{{ $journal->subject->name }}</a></td>
                                        <td><a class="row-hit" href="{{ $journalUrl }}">{{ \Illuminate\Support\Str::limit($journal->material, 42) }}</a></td>
                                        <td><a class="row-hit" href="{{ $journalUrl }}"><x-badge :tone="$journal->status->value === 'completed' ? 'success' : 'warning'">{{ $journal->status->label() }}</x-badge></a></td>
                                        <td><a class="row-hit is-primary" href="{{ $journalUrl }}">Detail</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </section>
    @endif
</x-app-layout>
