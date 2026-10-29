<x-app-layout>
    <x-page-header title="Isi absensi" description="Pilih penugasan, lalu tandai status setiap siswa. Tombol massal tetap terlihat, tidak hanya lewat gestur." />
    <form method="GET" class="card mb-[18px] grid gap-[12px] md:grid-cols-[1fr_auto]">
        <x-select label="Penugasan" name="assignment_id">
            <option value="">Pilih</option>
            @foreach ($assignments as $item)
                <option value="{{ $item->id }}" @selected(request('assignment_id') == $item->id)>{{ $item->schoolClass->name }} · {{ $item->subject->name }} · {{ $item->semester->name }}</option>
            @endforeach
        </x-select>
        <div class="self-end"><button class="btn btn-secondary">Tampilkan siswa</button></div>
    </form>
    @if ($assignment)
        <form method="POST" action="{{ route('attendance.store') }}" class="card space-y-[18px] bg-white" x-data="{ mark(value) { document.querySelectorAll('[data-attendance]').forEach((el) => { el.value = value }) } }">
            @csrf
            <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
            <div class="grid gap-[12px] md:grid-cols-2">
                <x-input label="Tanggal" name="date" type="date" :value="$session->date?->toDateString()" required />
                <x-input label="Sesi" name="session_label" :value="$session->session_label" required />
            </div>
            <div class="flex flex-wrap gap-[12px]">
                <button type="button" class="btn btn-secondary" @click="mark('hadir')">Tandai semua hadir</button>
                <button type="button" class="btn btn-secondary" @click="mark('alpa')">Tandai semua alpa</button>
            </div>
            @if ($students->isEmpty())
                <p>Tidak ada siswa aktif di kelas ini.</p>
            @else
                <div class="space-y-[12px]">
                    @foreach ($students as $student)
                        <div class="grid items-center gap-[12px] rounded-[12px] bg-surface p-[12px] md:grid-cols-[1fr_180px]">
                            <p class="font-medium">{{ $student->name }} <span class="text-[13px] text-muted">{{ $student->nis }}</span></p>
                            <label class="sr-only" for="status-{{ $student->id }}">Status {{ $student->name }}</label>
                            <select id="status-{{ $student->id }}" name="status[{{ $student->id }}]" class="field" data-attendance required>
                                <option value="">Pilih status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
                <button class="btn btn-primary" data-submit>Simpan absensi</button>
            @endif
        </form>
    @endif
</x-app-layout>
