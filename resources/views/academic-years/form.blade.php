<x-app-layout>
    <x-page-header :title="$year->exists ? 'Ubah tahun ajaran' : 'Tambah tahun ajaran'" />
    <form method="POST" action="{{ $year->exists ? route('academic-years.update', $year) : route('academic-years.store') }}" class="card grid max-w-[720px] gap-[18px] bg-white">
        @csrf
        @if ($year->exists) @method('PUT') @endif
        <x-input label="Nama" name="name" :value="$year->name" placeholder="2026/2027" required />
        <x-input label="Mulai" name="starts_on" type="date" :value="$year->starts_on?->toDateString()" required />
        <x-input label="Selesai" name="ends_on" type="date" :value="$year->ends_on?->toDateString()" required />
        @if ($year->exists)
            @foreach ($year->semesters as $index => $semester)
                <fieldset class="grid gap-[12px] rounded-[12px] border border-line p-[12px]">
                    <legend class="px-[6px] text-[13px] font-medium">Semester {{ $semester->name }}</legend>
                    <input type="hidden" name="semesters[{{ $index }}][id]" value="{{ $semester->id }}">
                    <x-input label="Mulai semester" name="semesters[{{ $index }}][starts_on]" type="date" :value="$semester->starts_on->toDateString()" />
                    <x-input label="Selesai semester" name="semesters[{{ $index }}][ends_on]" type="date" :value="$semester->ends_on->toDateString()" />
                </fieldset>
            @endforeach
        @endif
        <div class="flex gap-[12px]"><button class="btn btn-primary" data-submit>Simpan</button><a class="btn btn-ghost" href="{{ route('academic-years.index') }}">Batal</a></div>
    </form>
</x-app-layout>
