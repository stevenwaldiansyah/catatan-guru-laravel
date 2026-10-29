<x-app-layout>
    <x-page-header title="Kelas" description="Kelas terikat pada tahun ajaran agar riwayat tidak tertimpa.">
        <x-slot:actions><a class="btn btn-primary" href="{{ route('classes.create') }}">Tambah kelas</a></x-slot:actions>
    </x-page-header>
    @if ($classes->isEmpty())
        <x-empty title="Belum ada kelas" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Kelas</th><th>Tingkat</th><th>Jurusan</th><th>Tahun</th><th>Wali</th><th>Siswa</th><th></th></tr></thead>
                <tbody>
                    @foreach ($classes as $class)
                        <tr>
                            <td><a class="font-medium text-primary" href="{{ route('classes.show', $class) }}">{{ $class->name }}</a></td>
                            <td>{{ $class->level ?: '-' }}</td>
                            <td>{{ $class->major ?: '-' }}</td>
                            <td>{{ $class->academicYear->name }}</td>
                            <td>{{ $class->homeroomTeacher?->name ?: '-' }}</td>
                            <td>{{ $class->active_students_count }}</td>
                            <td class="flex gap-[6px]">
                                <a class="btn btn-secondary" href="{{ route('classes.edit', $class) }}">Ubah</a>
                                <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('classes.destroy', $class)), title: 'Hapus kelas {{ $class->name }}?' })">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-[18px]">{{ $classes->links() }}</div>
    @endif
</x-app-layout>
