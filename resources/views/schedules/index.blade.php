<x-app-layout>
    @if (auth()->user()->isAdmin() && ! $selectedTeacher)
        <x-page-header title="Jadwal pelajaran" description="Setiap guru punya jadwal sendiri. Pilih guru untuk melihat dan mengubah jam mengajarnya." />
        @if ($teachers->isEmpty())
            <x-empty title="Belum ada guru" text="Tambahkan data guru terlebih dahulu." />
        @else
            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($teachers as $teacher)
                    <a href="{{ route('schedules.index', ['guru' => $teacher->id]) }}" class="card flex items-center gap-4 p-4 transition hover:border-primary">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full text-[16px] font-semibold text-primary" style="background: var(--nav-active-bg)">{{ mb_strtoupper(mb_substr($teacher->name, 0, 1)) }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-headline text-[16px] font-semibold">{{ $teacher->name }}</span>
                            <span class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-[12px] text-muted">
                                <span>{{ $scheduleCounts[$teacher->id] ?? 0 }} jam mengajar</span>
                                <span>{{ $teacher->assignments_count }} penugasan</span>
                            </span>
                        </span>
                        <svg class="shrink-0 text-muted" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                @endforeach
            </section>
        @endif
    @else
        <x-page-header
            :title="$selectedTeacher ? 'Jadwal '.$selectedTeacher->name : 'Jadwal pelajaran'"
            :description="$selectedTeacher && auth()->user()->isAdmin() ? 'Jam mengajar, kelas, dan mapel di halaman ini hanya milik '.$selectedTeacher->name.'.' : 'Jadwal mingguan kelas dan mata pelajaran yang Anda ampu.'"
        >
            @if (auth()->user()->isAdmin() && $selectedTeacher)
                <x-slot:actions>
                    <a class="btn btn-ghost" href="{{ route('schedules.index') }}">Semua guru</a>
                </x-slot:actions>
            @endif
        </x-page-header>

        @if (auth()->user()->isAdmin() && $selectedTeacher)
            <form method="POST" action="{{ route('assignments.store') }}" class="card mb-6 grid gap-4 p-5">
                    @csrf
                    <input type="hidden" name="teacher_id" value="{{ $selectedTeacher->id }}">
                    <div>
                        <h2 class="font-headline text-[18px] font-semibold">Penugasan</h2>
                        <p class="mt-1 text-[13px] text-muted">Kelas dan mapel yang diampu {{ $selectedTeacher->name }}.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="label" for="class_id">Kelas</label>
                            <select id="class_id" class="field" name="class_id" required>
                                <option value="">Pilih kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }} · {{ $class->academicYear->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label" for="subject_id">Mata pelajaran</label>
                            <select id="subject_id" class="field" name="subject_id" required>
                                <option value="">Pilih mapel</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label" for="semester_id">Semester</label>
                            <select id="semester_id" class="field" name="semester_id" required>
                                <option value="">Pilih semester</option>
                                @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" @selected(old('semester_id') == $semester->id)>{{ $semester->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-secondary" data-submit>Simpan penugasan</button>
                    </div>
                    @if ($assignments->isEmpty())
                        <p class="text-[13px] text-muted">Belum ada penugasan.</p>
                    @else
                        <ul class="divide-y divide-line border-t border-line">
                            @foreach ($assignments as $assignment)
                                <li class="flex items-center justify-between gap-3 py-3">
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium">{{ $assignment->schoolClass->name }}</span>
                                        <span class="block truncate text-[12px] text-muted">{{ $assignment->subject->name }} · {{ $assignment->semester->name }}</span>
                                    </span>
                                    <button type="button" class="shrink-0 text-[12px] font-semibold text-danger" @click="$dispatch('confirm', { action: @js(route('assignments.destroy', $assignment)), title: 'Hapus penugasan ini?' })">Hapus</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
            </form>
        @elseif ($assignments->isNotEmpty())
            <section class="card mb-6 p-5">
                <h2 class="font-headline text-[18px] font-semibold">Kelas yang diampu</h2>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($assignments as $assignment)
                        <li class="rounded-xl border border-line px-3 py-2">
                            <span class="block text-[14px] font-medium">{{ $assignment->schoolClass->name }}</span>
                            <span class="block text-[12px] text-muted">{{ $assignment->subject->name }} · {{ $assignment->semester->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @php $slotCount = $week->flatten()->count(); @endphp
        @if ($slotCount === 0 && ! auth()->user()->isAdmin())
            <x-empty title="Belum ada jadwal" text="Jadwal mengajar Anda belum diatur." />
        @else
            <section class="card overflow-hidden p-0">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h2 class="font-headline text-[18px] font-semibold">Jadwal mingguan</h2>
                    <p class="text-[13px] text-muted">{{ $slotCount }} jam</p>
                </div>
                <div class="grid grid-cols-1 gap-px bg-line sm:grid-cols-2 xl:grid-cols-6">
                        @foreach ($days as $number => $name)
                            @php $today = now()->dayOfWeekIso === (int) $number; @endphp
                            <div class="flex flex-col bg-card">
                                <h3 class="border-b border-line px-3 py-3 text-center text-[13px] font-semibold {{ $today ? 'text-primary' : '' }}" @if($today) style="background: var(--nav-active-bg)" @endif>{{ $name }}</h3>
                                <div class="flex flex-1 flex-col gap-2 p-2">
                                    @forelse ($week->get($number, collect()) as $item)
                                        <article class="rounded-xl px-3 py-2" style="background: var(--nav-active-bg)">
                                            <p class="text-[12px] font-semibold text-primary">{{ $item->timeRange() }}</p>
                                            <p class="mt-1 text-[14px] font-medium leading-5">{{ $item->assignment->subject->name }}</p>
                                            <p class="text-[12px] leading-5 text-muted">{{ $item->assignment->schoolClass->name }}{{ $item->room ? ' · '.$item->room : '' }}</p>
                                            @can('update', $item)
                                                <div class="mt-2 flex gap-3">
                                                    <a class="text-[12px] font-semibold text-primary" href="{{ route('schedules.edit', $item) }}">Ubah</a>
                                                    <button type="button" class="text-[12px] font-semibold text-danger" @click="$dispatch('confirm', { action: @js(route('schedules.destroy', $item)), title: 'Hapus jadwal ini?' })">Hapus</button>
                                                </div>
                                            @endcan
                                        </article>
                                    @empty
                                        <p class="px-2 py-6 text-center text-[12px] text-muted">Kosong</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                </div>
            </section>
        @endif
    @endif
</x-app-layout>
