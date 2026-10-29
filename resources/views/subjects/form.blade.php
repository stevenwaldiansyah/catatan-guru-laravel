<x-app-layout>
    <x-page-header :title="$subject->exists ? 'Ubah mapel' : 'Tambah mapel'" />
    <form method="POST" action="{{ $subject->exists ? route('subjects.update', $subject) : route('subjects.store') }}" class="card grid max-w-[720px] gap-[18px] bg-white">
        @csrf
        @if ($subject->exists) @method('PUT') @endif
        <x-input label="Kode" name="code" :value="$subject->code" required />
        <x-input label="Nama" name="name" :value="$subject->name" required />
        <x-textarea label="Deskripsi" name="description" :value="$subject->description" />
        <x-checkbox label="Aktif" name="is_active" :checked="$subject->is_active ?? true" />
        <div class="flex gap-[12px]"><button class="btn btn-primary" data-submit>Simpan</button><a class="btn btn-ghost" href="{{ route('subjects.index') }}">Batal</a></div>
    </form>
</x-app-layout>
