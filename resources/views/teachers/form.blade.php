<x-app-layout>
    <x-page-header :title="$teacher->exists ? 'Ubah guru' : 'Tambah guru'" description="Akun dan profil guru disimpan bersama." />
    <form method="POST" action="{{ $teacher->exists ? route('teachers.update', $teacher) : route('teachers.store') }}" class="card grid gap-[18px] bg-white md:grid-cols-2">
        @csrf
        @if ($teacher->exists) @method('PUT') @endif
        <x-input label="Nama" name="name" :value="$teacher->name" required />
        <x-input label="NIP" name="nip" :value="$teacher->nip" />
        <x-input label="Nama pengguna" name="username" :value="$teacher->user?->username" required />
        <x-input label="Email" name="email" type="email" :value="$teacher->user?->email" required />
        <x-input label="{{ $teacher->exists ? 'Kata sandi baru (opsional)' : 'Kata sandi' }}" name="password" type="password" :required="! $teacher->exists" />
        <x-input label="Telepon" name="phone" :value="$teacher->phone" />
        <div class="md:col-span-2"><x-textarea label="Alamat" name="address" :value="$teacher->address" /></div>
        <x-checkbox label="Akun aktif" name="is_active" :checked="$teacher->user?->is_active ?? true" />
        <div class="md:col-span-2 flex flex-wrap gap-[12px]">
            <button class="btn btn-primary" data-submit>Simpan</button>
            <a class="btn btn-ghost" href="{{ route('teachers.index') }}">Batal</a>
        </div>
    </form>
</x-app-layout>
