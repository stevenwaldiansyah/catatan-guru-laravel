<x-app-layout>
    <x-page-header :title="$student->name" :description="($student->nis ? 'NIS '.$student->nis : 'Tanpa NIS').' · '.($student->gender === 'L' ? 'Laki-laki' : 'Perempuan')">
        <x-slot:actions>
            @can('update', $student)
                <a class="btn btn-secondary" href="{{ route('students.edit', $student) }}">Ubah</a>
            @endcan
            @can('delete', $student)
                <button type="button" class="btn btn-danger" @click="$dispatch('confirm', { action: @js(route('students.destroy', $student)), title: @js('Hapus data '.$student->name.'?') })">Hapus</button>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <section class="card">
        <h2 class="font-headline text-[20px] font-semibold">Data siswa</h2>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-[13px] text-muted">NIS</dt>
                <dd class="mt-1">{{ $student->nis ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-[13px] text-muted">NISN</dt>
                <dd class="mt-1">{{ $student->nisn ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-[13px] text-muted">Jenis kelamin</dt>
                <dd class="mt-1">{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
            </div>
            <div>
                <dt class="text-[13px] text-muted">Status</dt>
                <dd class="mt-1"><x-badge :tone="$student->status === 'aktif' ? 'success' : 'neutral'">{{ ucfirst($student->status) }}</x-badge></dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-[13px] text-muted">Riwayat kelas</dt>
                @if ($student->enrollments->isEmpty())
                    <dd class="mt-1 text-muted">Belum ada kelas.</dd>
                @else
                    <dd class="mt-2 divide-y divide-line">
                        @foreach ($student->enrollments as $enrollment)
                            <div class="flex min-h-[44px] items-center justify-between gap-[12px] py-[8px]">
                                <span>{{ $enrollment->schoolClass->name }} · {{ $enrollment->academicYear->name }}</span>
                                <x-badge :tone="$enrollment->status->value === 'active' ? 'success' : 'neutral'">{{ $enrollment->status->label() }}</x-badge>
                            </div>
                        @endforeach
                    </dd>
                @endif
            </div>
        </dl>
    </section>
</x-app-layout>
