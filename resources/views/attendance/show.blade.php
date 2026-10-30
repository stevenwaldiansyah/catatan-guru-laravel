<x-app-layout>
    <x-page-header :title="$session->date->translatedFormat('d M Y')" :description="$session->schoolClass->name.' · '.$session->subject->name.' · '.$session->session_label">
        <x-slot:actions>
            @can('delete', $session)
                <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('attendance.destroy', $session)), title: 'Hapus sesi absensi ini beserta status siswanya?' })">Hapus</button>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <ul class="mb-[18px] grid grid-cols-2 gap-[12px] lg:grid-cols-4">
        <li class="card">Hadir: {{ $summary['hadir'] }}</li>
        <li class="card">Izin: {{ $summary['izin'] }}</li>
        <li class="card">Sakit: {{ $summary['sakit'] }}</li>
        <li class="card">Alpa: {{ $summary['alpa'] }}</li>
    </ul>
    @can('update', $session)
        <form method="POST" action="{{ route('attendance.update', $session) }}" class="card space-y-[12px]">
            @csrf
            @method('PUT')
            <input type="hidden" name="assignment_id" value="{{ \App\Models\TeachingAssignment::query()->where('teacher_id', $session->teacher_id)->where('class_id', $session->class_id)->where('subject_id', $session->subject_id)->where('semester_id', $session->semester_id)->value('id') }}">
            <input type="hidden" name="date" value="{{ $session->date->toDateString() }}">
            <input type="hidden" name="session_label" value="{{ $session->session_label }}">
            @foreach ($session->records as $record)
                <div class="grid items-center gap-[12px] md:grid-cols-[1fr_180px_1fr]">
                    <p>{{ $record->student->name }}</p>
                    <label class="sr-only" for="status-{{ $record->student_id }}">Status {{ $record->student->name }}</label>
                    <select id="status-{{ $record->student_id }}" class="field" name="status[{{ $record->student_id }}]" required>
                        @foreach (\App\Enums\AttendanceStatus::options() as $status)
                            <option value="{{ $status->value }}" @selected($record->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <input class="field" name="note[{{ $record->student_id }}]" value="{{ $record->note }}" placeholder="Keterangan" aria-label="Keterangan {{ $record->student->name }}">
                </div>
            @endforeach
            <button class="btn btn-primary" data-submit>Simpan perubahan</button>
        </form>
    @endcan
</x-app-layout>
