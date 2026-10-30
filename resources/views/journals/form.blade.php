<x-app-layout>
    <x-page-header :title="$journal->exists ? 'Ubah jurnal' : 'Tambah jurnal'" />
    @php
        $selectedAssignment = old('assignment_id');
        if (! $selectedAssignment && $journal->exists) {
            $selectedAssignment = $assignments->first(fn ($item) => $item->teacher_id === $journal->teacher_id && $item->class_id === $journal->class_id && $item->subject_id === $journal->subject_id && $item->semester_id === $journal->semester_id)?->id;
        }
    @endphp
    <form method="POST" action="{{ $journal->exists ? route('journals.update', $journal) : route('journals.store') }}" class="card grid gap-[18px]">
        @csrf
        @if ($journal->exists) @method('PUT') @endif
        <x-select label="Penugasan" name="assignment_id" required>
            <option value="">Pilih</option>
            @foreach ($assignments as $assignment)
                <option value="{{ $assignment->id }}" @selected($selectedAssignment == $assignment->id)>{{ $assignment->teacher->name }} · {{ $assignment->schoolClass->name }} · {{ $assignment->subject->name }} · {{ $assignment->semester->name }}</option>
            @endforeach
        </x-select>
        <div class="grid gap-[18px] md:grid-cols-2">
            <x-input label="Tanggal" name="date" type="date" :value="$journal->date?->toDateString()" required />
            <x-input label="Sesi" name="session_label" :value="$journal->session_label" required />
        </div>
        <x-textarea label="Materi" name="material" :value="$journal->material" required />
        <x-textarea label="Kegiatan pembelajaran" name="activities" :value="$journal->activities" />
        <x-input label="Metode" name="method" :value="$journal->method" />
        <x-textarea label="Catatan atau kendala" name="notes" :value="$journal->notes" />
        <x-select label="Status" name="status" required>
            <option value="draft" @selected(old('status', $journal->status?->value ?? 'draft') === 'draft')>Draf</option>
            <option value="completed" @selected(old('status', $journal->status?->value) === 'completed')>Selesai</option>
        </x-select>
        <div class="flex gap-[12px]"><button class="btn btn-primary" data-submit>Simpan</button><a class="btn btn-ghost" href="{{ route('journals.index') }}">Batal</a></div>
    </form>
</x-app-layout>
