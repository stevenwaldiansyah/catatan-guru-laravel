<x-app-layout>
    <x-page-header :title="$student->name" :description="($student->nis ? 'NIS '.$student->nis : 'Tanpa NIS').' · '.($student->gender === 'L' ? 'Laki-laki' : 'Perempuan')">
        <x-slot:actions>
            @can('update', $student)
                <a class="btn btn-secondary" href="{{ route('students.edit', $student) }}">Ubah</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <section class="card">
        <h2 class="font-headline text-[20px] font-semibold">Riwayat kelas</h2>
        <ul class="mt-[12px] divide-y divide-[#F3F4F6]">
            @foreach ($student->enrollments as $enrollment)
                <li class="flex min-h-[52px] items-center justify-between gap-[12px] py-[12px]">
                    <span>{{ $enrollment->schoolClass->name }} · {{ $enrollment->academicYear->name }}</span>
                    <x-badge :tone="$enrollment->status->value === 'active' ? 'success' : 'neutral'">{{ $enrollment->status->label() }}</x-badge>
                </li>
            @endforeach
        </ul>
    </section>
    <section class="mt-[18px] card">
        <h2 class="font-headline text-[20px] font-semibold">Catatan yang boleh Anda lihat</h2>
        @if ($notes->isEmpty())
            <p class="mt-[12px] text-muted">Belum ada catatan.</p>
        @else
            <ul class="mt-[12px] space-y-[12px]">
                @foreach ($notes as $note)
                    <li><a class="text-primary" href="{{ route('notes.show', $note) }}">{{ $note->date->translatedFormat('d M Y') }} · {{ $note->categoryLabel() }}</a></li>
                @endforeach
            </ul>
        @endif
    </section>
</x-app-layout>
