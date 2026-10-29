<x-app-layout>
    <x-page-header title="Jadwal" description="Penugasan menghubungkan guru, kelas, mapel, dan semester. Bentrok jadwal mengikuti pengaturan sekolah.">
        <x-slot:actions>
            @can('create', App\Models\Schedule::class)
                <a class="btn btn-primary" href="{{ route('schedules.create') }}">Tambah jadwal</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
    @if (auth()->user()->isAdmin())
        <form method="POST" action="{{ route('assignments.store') }}" class="card mb-[18px] grid gap-[12px] md:grid-cols-2 xl:grid-cols-5">
            @csrf
            <h2 class="font-headline text-[20px] font-semibold md:col-span-2 xl:col-span-5">Penugasan mengajar</h2>
            <select class="field" name="teacher_id" aria-label="Guru" required><option value="">Guru</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select>
            <select class="field" name="class_id" aria-label="Kelas" required><option value="">Kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }} · {{ $class->academicYear->name }}</option>@endforeach</select>
            <select class="field" name="subject_id" aria-label="Mapel" required><option value="">Mapel</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select>
            <select class="field" name="semester_id" aria-label="Semester" required><option value="">Semester</option>@foreach($semesters as $semester)<option value="{{ $semester->id }}">{{ $semester->academicYear->name }} · {{ $semester->name }}</option>@endforeach</select>
            <button class="btn btn-primary" data-submit>Simpan penugasan</button>
        </form>
    @endif
    <section class="mb-[24px]">
        <h2 class="mb-[12px] font-headline text-[20px] font-semibold">Daftar penugasan</h2>
        @if ($assignments->isEmpty())
            <x-empty title="Belum ada penugasan" />
        @else
            <div class="space-y-[12px]">
                @foreach ($assignments as $assignment)
                    <article class="card flex flex-wrap items-center justify-between gap-[12px]">
                        <p>{{ $assignment->teacher->name }} · {{ $assignment->schoolClass->name }} · {{ $assignment->subject->name }} · {{ $assignment->semester->name }}</p>
                        @if (auth()->user()->isAdmin())
                            <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('assignments.destroy', $assignment)), title: 'Hapus penugasan ini?' })">Hapus</button>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
    <form method="GET" class="mb-[18px] flex gap-[12px]">
        <select class="field max-w-[240px]" name="day" aria-label="Hari"><option value="">Semua hari</option>@foreach($days as $number => $name)<option value="{{ $number }}" @selected(request('day') == $number)>{{ $name }}</option>@endforeach</select>
        <button class="btn btn-secondary">Filter</button>
    </form>
    @if ($schedules->isEmpty())
        <x-empty title="Belum ada jadwal" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Hari</th><th>Jam</th><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Ruang</th><th></th></tr></thead>
                <tbody>
                    @foreach ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->dayName() }}</td>
                            <td>{{ $schedule->timeRange() }}</td>
                            <td>{{ $schedule->assignment->teacher->name }}</td>
                            <td>{{ $schedule->assignment->schoolClass->name }}</td>
                            <td>{{ $schedule->assignment->subject->name }}</td>
                            <td>{{ $schedule->room ?: '-' }}</td>
                            <td>
                                @can('update', $schedule)
                                    <a class="btn btn-secondary" href="{{ route('schedules.edit', $schedule) }}">Ubah</a>
                                    <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('schedules.destroy', $schedule)), title: 'Hapus jadwal ini?' })">Hapus</button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-[18px]">{{ $schedules->links() }}</div>
    @endif
</x-app-layout>
