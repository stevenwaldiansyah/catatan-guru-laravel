<x-app-layout>
    <x-page-header title="Catatan siswa" description="Guru hanya melihat catatan yang mereka tulis. Admin dapat melihat seluruh catatan.">
        <x-slot:actions>
            @if (auth()->user()->teacher)
                <a class="btn btn-primary" href="{{ route('notes.create') }}">Tambah catatan</a>
            @endif
        </x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-[18px] flex gap-[12px]">
        <input class="field" name="q" value="{{ request('q') }}" placeholder="Cari siswa atau isi catatan" aria-label="Cari catatan">
        <button class="btn btn-secondary">Cari</button>
    </form>
    @if ($notes->isEmpty())
        <x-empty title="Belum ada catatan" />
    @else
        <div class="space-y-[12px]">
            @foreach ($notes as $note)
                <article class="card">
                    <a class="font-headline text-[20px] font-semibold text-primary" href="{{ route('notes.show', $note) }}">{{ $note->student->name }}</a>
                    <p class="text-[13px] text-muted">{{ $note->date->translatedFormat('d M Y') }} · {{ $note->categoryLabel() }} · {{ $note->schoolClass->name }}</p>
                    <p class="mt-[6px]">{{ \Illuminate\Support\Str::limit($note->description, 180) }}</p>
                </article>
            @endforeach
        </div>
        <div class="mt-[18px]">{{ $notes->links() }}</div>
    @endif
</x-app-layout>
