<x-app-layout>
    <x-page-header title="Dasbor admin" description="Ringkasan data sekolah dari database, bukan angka contoh." />
    @if (! $settings?->active_semester_id)
        <div class="alert alert-info mb-[18px]" role="status">Tahun ajaran atau semester aktif belum diatur. Buka Pengaturan untuk memilihnya.</div>
    @endif
    <section class="grid grid-cols-1 gap-[12px] min-[360px]:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Guru" :value="$teacherCount" />
        <x-stat label="Siswa aktif" :value="$studentCount" />
        <x-stat label="Kelas" :value="$classCount" hint="Pada tahun ajaran aktif" />
        <x-stat label="Mapel aktif" :value="$subjectCount" />
    </section>
    <section class="mt-[24px] grid min-w-0 gap-[18px] lg:grid-cols-2">
        <article class="card min-w-0 max-w-full">
            <h2 class="font-headline text-[20px] font-semibold">Absensi hari ini</h2>
            <ul class="mt-[12px] grid grid-cols-2 gap-[12px] text-[15px]">
                <li>Hadir: {{ $attendanceToday['hadir'] }}</li>
                <li>Izin: {{ $attendanceToday['izin'] }}</li>
                <li>Sakit: {{ $attendanceToday['sakit'] }}</li>
                <li>Alpa: {{ $attendanceToday['alpa'] }}</li>
            </ul>
            @php $totalAttendance = array_sum($attendanceToday); @endphp
            @if ($totalAttendance > 0)
                <div class="mt-[18px] h-[220px] w-full max-w-full overflow-hidden"><canvas id="attendanceChart"></canvas></div>
            @else
                <p class="mt-[18px] text-[13px] text-muted">Belum ada absensi yang tercatat hari ini.</p>
            @endif
        </article>
        <article class="card min-w-0 max-w-full">
            <h2 class="font-headline text-[20px] font-semibold">Jurnal 6 bulan terakhir</h2>
            <p class="mt-[6px] text-[13px] text-muted">{{ $journalMonth }} jurnal pada bulan ini.</p>
            <div class="mt-[18px] h-[220px] w-full max-w-full overflow-hidden"><canvas id="journalChart"></canvas></div>
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
                        datasets: [{ data: [attendance.hadir, attendance.izin, attendance.sakit, attendance.alpa], backgroundColor: ['#34D399', '#FBBF24', '#3B82F6', '#F97171'] }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            }
            const journalCanvas = document.getElementById('journalChart');
            if (journalCanvas && window.Chart) {
                new Chart(journalCanvas, {
                    type: 'bar',
                    data: { labels: months.map((item) => item.label), datasets: [{ label: 'Jurnal', data: months.map((item) => item.total), backgroundColor: '#3B82F6' }] },
                    options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
            });
        </script>
    @endpush
</x-app-layout>
