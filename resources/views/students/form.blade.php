<x-app-layout>
    @php $currentClass = $student->exists ? $student->enrollments->firstWhere('status', \App\Enums\EnrollmentStatus::Active)?->class_id : null; @endphp
    <x-page-header :title="$student->exists ? 'Ubah siswa' : 'Tambah siswa'" description="Memindahkan kelas pada tahun ajaran yang sama menyimpan riwayat pindah." />
    <form method="POST" action="{{ $student->exists ? route('students.update', $student) : route('students.store') }}" class="card grid gap-[18px] bg-white md:grid-cols-2">
        @csrf
        @if ($student->exists) @method('PUT') @endif
        <x-input label="Nama" name="name" :value="$student->name" required />
        <x-input label="NIS" name="nis" :value="$student->nis" />
        <x-input label="NISN" name="nisn" :value="$student->nisn" />
        <x-select label="Jenis kelamin" name="gender" required>
            <option value="L" @selected(old('gender', $student->gender) === 'L')>Laki-laki</option>
            <option value="P" @selected(old('gender', $student->gender) === 'P')>Perempuan</option>
        </x-select>
        <x-select label="Status" name="status" required>
            <option value="aktif" @selected(old('status', $student->status ?: 'aktif') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(old('status', $student->status) === 'nonaktif')>Nonaktif</option>
        </x-select>
        <x-select label="Kelas" name="class_id" required>
            <option value="">Pilih</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(old('class_id', $currentClass) == $class->id)>{{ $class->name }} · {{ $class->academicYear->name }}</option>
            @endforeach
        </x-select>
        <div class="md:col-span-2 flex gap-[12px]">
            <button class="btn btn-primary" data-submit>Simpan</button>
            <a class="btn btn-ghost" href="{{ route('students.index') }}">Batal</a>
        </div>
    </form>
</x-app-layout>
