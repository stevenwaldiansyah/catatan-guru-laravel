<x-app-layout>
    <x-page-header :title="$class->exists ? 'Ubah kelas' : 'Tambah kelas'" />
    <form method="POST" action="{{ $class->exists ? route('classes.update', $class) : route('classes.store') }}" class="card grid gap-[18px] md:grid-cols-2">
        @csrf
        @if ($class->exists) @method('PUT') @endif
        <x-select label="Tahun ajaran" name="academic_year_id" required>
            <option value="">Pilih</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}" @selected(old('academic_year_id', $class->academic_year_id) == $year->id)>{{ $year->name }}</option>
            @endforeach
        </x-select>
        <x-input label="Nama kelas" name="name" :value="$class->name" required />
        <x-input label="Tingkat" name="level" :value="$class->level" />
        <x-input label="Jurusan" name="major" :value="$class->major" />
        <x-select label="Wali kelas" name="homeroom_teacher_id">
            <option value="">Tidak ada</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(old('homeroom_teacher_id', $class->homeroom_teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
            @endforeach
        </x-select>
        <div class="md:col-span-2 flex gap-[12px]">
            <button class="btn btn-primary" data-submit>Simpan</button>
            <a class="btn btn-ghost" href="{{ route('classes.index') }}">Batal</a>
        </div>
    </form>
</x-app-layout>
