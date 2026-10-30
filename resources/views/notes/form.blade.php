<x-app-layout>
    <x-page-header :title="$note->exists ? 'Ubah catatan' : 'Tambah catatan'" />
    <form method="POST" action="{{ $note->exists ? route('notes.update', $note) : route('notes.store') }}" class="card grid gap-[18px]">
        @csrf
        @if ($note->exists) @method('PUT') @endif
        @if ($note->exists)
            <div>
                <span class="label">Siswa</span>
                <p class="mt-[6px]">{{ $note->student->name }}</p>
            </div>
            <div>
                <span class="label">Kelas</span>
                <p class="mt-[6px]">{{ $note->schoolClass->name }}</p>
            </div>
        @else
            <x-select label="Siswa" name="student_id" required>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id', $note->student_id) == $student->id)>{{ $student->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Kelas" name="class_id" required>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected(old('class_id', $note->class_id) == $class->id)>{{ $class->name }}</option>
                @endforeach
            </x-select>
        @endif
        <x-input label="Tanggal" name="date" type="date" :value="$note->date?->toDateString()" required />
        <x-select label="Kategori" name="category" required>
            @foreach ($categories as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $note->category) === $value)>{{ $label }}</option>
            @endforeach
        </x-select>
        <x-textarea label="Deskripsi perkembangan" name="description" :value="$note->description" required />
        <x-textarea label="Tindak lanjut" name="follow_up" :value="$note->follow_up" />
        <div class="flex gap-[12px]"><button class="btn btn-primary" data-submit>Simpan</button><a class="btn btn-ghost" href="{{ route('notes.index') }}">Batal</a></div>
    </form>
</x-app-layout>
