<x-app-layout>
    <x-page-header :title="$class->name" :description="$class->academicYear->name.($class->homeroomTeacher ? ' · Wali '.$class->homeroomTeacher->name : '')">
        <x-slot:actions><a class="btn btn-secondary" href="{{ route('classes.edit', $class) }}">Ubah</a></x-slot:actions>
    </x-page-header>
    @if ($students->isEmpty())
        <x-empty title="Belum ada siswa aktif" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>NIS</th><th>Nama</th><th>Jenis kelamin</th></tr></thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->nis ?: '-' }}</td>
                            <td><a class="text-primary" href="{{ route('students.show', $student) }}">{{ $student->name }}</a></td>
                            <td>{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
