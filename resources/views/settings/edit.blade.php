<x-app-layout>
    <x-page-header title="Pengaturan" description="Identitas sekolah, periode aktif, dan aturan jadwal." />
    <form method="POST" action="{{ route('settings.update') }}" class="card grid gap-[18px] md:grid-cols-2">
        @csrf
        @method('PUT')
        <x-input label="Nama sekolah" name="school_name" :value="$settings->school_name" required />
        <x-input label="NPSN" name="npsn" :value="$settings->npsn" />
        <x-input label="Telepon" name="phone" :value="$settings->phone" />
        <x-input label="Email sekolah" name="email" type="email" :value="$settings->email" />
        <div class="md:col-span-2"><x-textarea label="Alamat" name="address" :value="$settings->address" /></div>
        <x-select label="Tahun ajaran aktif" name="active_academic_year_id">
            <option value="">Belum dipilih</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}" @selected(old('active_academic_year_id', $settings->active_academic_year_id) == $year->id)>{{ $year->name }}</option>
            @endforeach
        </x-select>
        <x-select label="Semester aktif" name="active_semester_id">
            <option value="">Belum dipilih</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}" @selected(old('active_semester_id', $settings->active_semester_id) == $semester->id)>{{ $semester->academicYear->name }} · {{ $semester->name }}</option>
            @endforeach
        </x-select>
        <div class="space-y-[6px] md:col-span-2">
            <x-checkbox label="Cegah bentrok jadwal guru" name="prevent_teacher_overlap" :checked="$settings->prevent_teacher_overlap ?? true" />
            <x-checkbox label="Cegah bentrok jadwal kelas" name="prevent_class_overlap" :checked="$settings->prevent_class_overlap ?? true" />
            <x-checkbox label="Cegah bentrok ruang" name="prevent_room_overlap" :checked="$settings->prevent_room_overlap" />
        </div>
        <div class="md:col-span-2"><button class="btn btn-primary" data-submit>Simpan pengaturan</button></div>
    </form>
</x-app-layout>
