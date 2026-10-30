<x-app-layout>
    <x-page-header title="Dasbor admin" description="Ringkasan data sekolah dari database, bukan angka contoh." />
    @if (! $settings?->active_semester_id)
        <div class="alert alert-info mb-[18px]" role="status">Tahun ajaran atau semester aktif belum diatur. Buka Pengaturan untuk memilihnya.</div>
    @endif
    <section class="grid grid-cols-1 gap-4 min-[360px]:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Guru" :value="$teacherCount" />
        <x-stat label="Siswa aktif" :value="$studentCount" />
        <x-stat label="Kelas" :value="$classCount" hint="Pada tahun ajaran aktif" />
        <x-stat label="Mapel aktif" :value="$subjectCount" />
    </section>
    <section class="mt-6 grid min-w-0 gap-6 lg:grid-cols-12">
        <article class="card min-w-0 max-w-full lg:col-span-8">
            <h2 class="font-headline text-xl font-semibold">Jurnal 6 bulan terakhir</h2>
            <p class="mt-2 text-[13px] text-muted">{{ $journalMonth }} jurnal pada bulan ini.</p>
            <div class="mt-4 h-[280px] w-full max-w-full overflow-hidden"><canvas id="journalChart"></canvas></div>
        </article>
        <article class="card min-w-0 max-w-full lg:col-span-4">
            <h2 class="font-headline text-xl font-semibold">Absensi hari ini</h2>
            <ul class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <li class="rounded-xl border border-line bg-canvas px-3 py-3"><span class="block text-[12px] text-muted">Hadir</span><span class="kpi text-2xl text-mint">{{ $attendanceToday['hadir'] }}</span></li>
                <li class="rounded-xl border border-line bg-canvas px-3 py-3"><span class="block text-[12px] text-muted">Izin</span><span class="kpi text-2xl">{{ $attendanceToday['izin'] }}</span></li>
                <li class="rounded-xl border border-line bg-canvas px-3 py-3"><span class="block text-[12px] text-muted">Sakit</span><span class="kpi text-2xl text-primary">{{ $attendanceToday['sakit'] }}</span></li>
                <li class="rounded-xl border border-line bg-canvas px-3 py-3"><span class="block text-[12px] text-muted">Alpa</span><span class="kpi text-2xl text-danger">{{ $attendanceToday['alpa'] }}</span></li>
            </ul>
            @php $totalAttendance = array_sum($attendanceToday); @endphp
            @if ($totalAttendance > 0)
                <div class="mt-4 h-[180px] w-full max-w-full overflow-hidden"><canvas id="attendanceChart"></canvas></div>
            @else
                <p class="mt-4 text-[13px] text-muted">Belum ada absensi yang tercatat hari ini.</p>
            @endif
        </article>
    </section>
    <section class="mt-[24px]">
        <h2 class="mb-[12px] font-headline text-[20px] font-semibold">Jurnal terbaru</h2>
        @if ($recentJournals->isEmpty())
            <x-empty title="Belum ada jurnal" text="Jurnal yang disimpan guru akan muncul di sini." />
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Tanggal</th><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($recentJournals as $journal)
                            <tr>
                                <td>{{ $journal->date->translatedFormat('d M Y') }}</td>
                                <td>{{ $journal->teacher->name }}</td>
                                <td>{{ $journal->schoolClass->name }}</td>
                                <td>{{ $journal->subject->name }}</td>
                                <td><x-badge :tone="$journal->status->value === 'completed' ? 'success' : 'warning'">{{ $journal->status->label() }}</x-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
            const attendance = @json($attendanceToday);
            const months = @json($journalMonths);
            const attendanceCanvas = document.getElementById('attendanceChart');
            if (attendanceCanvas && window.Chart) {
                new Chart(attendanceCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                        datasets: [{ data: [attendance.hadir, attendance.izin, attendance.sakit, attendance.alpa], backgroundColor: ['#32D74B', '#FFD60A', '#00E5FF', '#FF453A'], borderWidth: 0 }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#98989D', boxWidth: 10 } } } }
                });
            }
            const journalCanvas = document.getElementById('journalChart');
            if (journalCanvas && window.Chart) {
                new Chart(journalCanvas, {
                    type: 'line',
                    data: {
                        labels: months.map((item) => item.label),
                        datasets: [{
                            label: 'Jurnal',
                            data: months.map((item) => item.total),
                            borderColor: '#00E5FF',
                            backgroundColor: 'rgba(48, 209, 88, 0.22)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { color: '#2C2C2E' }, ticks: { color: '#98989D' } },
                            y: { beginAtZero: true, grid: { color: '#2C2C2E' }, ticks: { color: '#98989D', precision: 0 } },
                        },
                    },
                });
            }
            });
        </script>
    @endpush
</x-app-layout>
