<x-app-layout>
    <x-page-header title="Siswa" description="Identitas siswa unik lewat NIS atau NISN.">
        <x-slot:actions>
            @can('create', App\Models\Student::class)
                <a class="btn btn-primary" href="{{ route('students.create') }}">Tambah siswa</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-[18px] grid gap-[12px] md:grid-cols-[1fr_220px_auto]">
        <input class="field" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIS, atau NISN" aria-label="Cari siswa">
        <select class="field" name="class_id" aria-label="Filter kelas">
            <option value="">Semua kelas</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary">Filter</button>
    </form>
    @if ($students->isEmpty())
        <x-empty title="Tidak ada siswa" text="Ubah pencarian atau tambahkan siswa baru." />
    @else
        <div class="space-y-[12px] md:hidden">
            @foreach ($students as $student)
                <article class="card">
                    <a class="font-headline text-[20px] font-semibold text-primary" href="{{ route('students.show', $student) }}">{{ $student->name }}</a>
                    <p class="text-[13px] text-muted">{{ $student->nis ?: 'Tanpa NIS' }} · {{ $student->enrollments->first()?->schoolClass?->name ?: 'Belum ada kelas' }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @include('students.partials.actions', ['student' => $student])
                    </div>
                </article>
            @endforeach
        </div>
        <div class="hidden md:block table-wrap">
            <table class="data-table">
                <thead><tr><th>NIS</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->nis ?: '-' }}</td>
                            <td>{{ $student->nisn ?: '-' }}</td>
                            <td><a class="text-primary" href="{{ route('students.show', $student) }}">{{ $student->name }}</a></td>
                            <td>{{ $student->enrollments->first()?->schoolClass?->name ?: '-' }}</td>
                            <td><x-badge :tone="$student->status === 'aktif' ? 'success' : 'neutral'">{{ ucfirst($student->status) }}</x-badge></td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    @include('students.partials.actions', ['student' => $student])
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-[18px]">{{ $students->links() }}</div>
    @endif
</x-app-layout>
