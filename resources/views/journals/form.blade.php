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
        @unless ($journal->exists)
            @php
                $attendanceAssignment = $assignments->first(fn ($item) => (string) $item->id === (string) old('assignment_id', $selectedAssignment));
                $attendanceStudents = $attendanceAssignment?->schoolClass->activeStudents() ?? collect();
                $attendanceStatuses = \App\Enums\AttendanceStatus::options();
            @endphp
            <section class="grid gap-[12px]" aria-labelledby="absensi-judul">
                <div>
                    <h2 id="absensi-judul" class="font-headline text-lg font-semibold">Absensi</h2>
                    <p id="absensi-hint" class="mt-[4px] text-[13px] text-muted">
                        @if ($attendanceAssignment && $attendanceStudents->isEmpty())
                            Tidak ada siswa aktif di kelas {{ $attendanceAssignment->schoolClass->name }}.
                        @elseif ($attendanceAssignment)
                            Kelas {{ $attendanceAssignment->schoolClass->name }}. Status awal setiap siswa adalah hadir.
                        @else
                            Pilih penugasan untuk menampilkan siswa.
                        @endif
                    </p>
                </div>
                <div id="absensi-tabel" class="table-wrap {{ $attendanceStudents->isEmpty() ? 'hidden' : '' }}">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIS</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="absensi-siswa">
                            @foreach ($attendanceStudents as $student)
                                @php $attendanceSelected = old('attendance.'.$student->id, 'hadir'); @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="font-medium">{{ $student->name }}</td>
                                    <td>{{ $student->nis }}</td>
                                    <td>
                                        <label class="sr-only" for="attendance-{{ $student->id }}">Status {{ $student->name }}</label>
                                        <select id="attendance-{{ $student->id }}" name="attendance[{{ $student->id }}]" class="field" required>
                                            @foreach ($attendanceStatuses as $status)
                                                <option value="{{ $status->value }}" @selected($attendanceSelected === $status->value)>{{ $status->label() }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endunless
        <x-textarea label="Materi" name="material" :value="$journal->material" required />
        <x-textarea label="Catatan atau kendala" name="notes" :value="$journal->notes" />
        <x-select label="Status" name="status" required>
            <option value="draft" @selected(old('status', $journal->status?->value ?? 'completed') === 'draft')>Draf</option>
            <option value="completed" @selected(old('status', $journal->status?->value ?? 'completed') === 'completed')>Selesai</option>
        </x-select>
        <div class="flex gap-[12px]"><button class="btn btn-primary" data-submit>Simpan</button><a class="btn btn-ghost" href="{{ route('journals.index') }}">Batal</a></div>
    </form>
    @unless ($journal->exists)
        <script>
            const attendanceStatuses = @json(collect(\App\Enums\AttendanceStatus::options())->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->values());

            function attendanceRow(student, number) {
                const row = document.createElement('tr');

                const numberCell = document.createElement('td');
                numberCell.textContent = String(number);

                const nameCell = document.createElement('td');
                nameCell.className = 'font-medium';
                nameCell.textContent = student.name;

                const nisCell = document.createElement('td');
                nisCell.textContent = student.nis || '';

                const statusCell = document.createElement('td');
                const label = document.createElement('label');
                label.className = 'sr-only';
                label.htmlFor = 'attendance-' + student.id;
                label.textContent = 'Status ' + student.name;

                const select = document.createElement('select');
                select.id = 'attendance-' + student.id;
                select.name = 'attendance[' + student.id + ']';
                select.className = 'field';
                select.required = true;
                attendanceStatuses.forEach((status) => {
                    const option = document.createElement('option');
                    option.value = status.value;
                    option.textContent = status.label;
                    option.selected = status.value === 'hadir';
                    select.append(option);
                });

                statusCell.append(label, select);
                row.append(numberCell, nameCell, nisCell, statusCell);
                return row;
            }

            document.getElementById('assignment_id')?.addEventListener('change', async (event) => {
                const list = document.getElementById('absensi-siswa');
                const table = document.getElementById('absensi-tabel');
                const hint = document.getElementById('absensi-hint');
                const id = event.target.value;
                list.replaceChildren();
                table.classList.add('hidden');
                if (!id) {
                    hint.textContent = 'Pilih penugasan untuk menampilkan siswa.';
                    return;
                }

                hint.textContent = 'Memuat siswa...';
                const response = await fetch(@js(route('journals.students')) + '?assignment_id=' + encodeURIComponent(id), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) {
                    hint.textContent = 'Absensi kelas ini tidak dapat dimuat.';
                    return;
                }

                const data = await response.json();
                hint.textContent = data.students.length
                    ? 'Kelas ' + data.class_name + '. Status awal setiap siswa adalah hadir.'
                    : 'Tidak ada siswa aktif di kelas ' + data.class_name + '.';
                data.students.forEach((student, index) => list.append(attendanceRow(student, index + 1)));
                table.classList.toggle('hidden', data.students.length === 0);
            });
        </script>
    @endunless
</x-app-layout>
