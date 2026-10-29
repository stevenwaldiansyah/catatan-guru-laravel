<x-app-layout>
    <x-page-header :title="$schedule->exists ? 'Ubah jadwal' : 'Tambah jadwal'" />
    <form method="POST" action="{{ $schedule->exists ? route('schedules.update', $schedule) : route('schedules.store') }}" class="card grid max-w-[720px] gap-[18px] bg-white">
        @csrf
        @if ($schedule->exists) @method('PUT') @endif
        <x-select label="Penugasan" name="teaching_assignment_id" required>
            <option value="">Pilih</option>
            @foreach ($assignments as $assignment)
                <option value="{{ $assignment->id }}" @selected(old('teaching_assignment_id', $schedule->teaching_assignment_id) == $assignment->id)>{{ $assignment->teacher->name }} · {{ $assignment->schoolClass->name }} · {{ $assignment->subject->name }} · {{ $assignment->semester->name }}</option>
            @endforeach
        </x-select>
        <x-select label="Hari" name="day_of_week" required>
            @foreach ($days as $number => $name)
                <option value="{{ $number }}" @selected(old('day_of_week', $schedule->day_of_week) == $number)>{{ $name }}</option>
            @endforeach
        </x-select>
        <x-input label="Jam mulai" name="start_time" type="time" :value="substr((string) old('start_time', $schedule->start_time), 0, 5)" required />
        <x-input label="Jam selesai" name="end_time" type="time" :value="substr((string) old('end_time', $schedule->end_time), 0, 5)" required />
        <x-input label="Ruang" name="room" :value="$schedule->room" />
        <x-input label="Label sesi" name="label" :value="$schedule->label" />
        <div class="flex gap-[12px]"><button class="btn btn-primary" data-submit>Simpan</button><a class="btn btn-ghost" href="{{ route('schedules.index') }}">Batal</a></div>
    </form>
</x-app-layout>
