<x-app-layout>
    <x-page-header title="Profil" description="Perbarui identitas akun dan kata sandi Anda." />
    <div class="grid gap-[18px] lg:grid-cols-2">
        <form method="POST" action="{{ route('profile.update') }}" class="card space-y-[18px]">
            @csrf
            @method('PUT')
            <h2 class="font-headline text-[20px] font-semibold">Data akun</h2>
            <x-input label="Nama" name="name" :value="$user->name" required />
            <x-input label="Nama pengguna" name="username" :value="$user->username" required />
            <x-input label="Email" name="email" type="email" :value="$user->email" required />
            @if ($user->teacher)
                <x-input label="Telepon" name="phone" :value="$user->teacher->phone" />
            @endif
            <button class="btn btn-primary" data-submit>Simpan profil</button>
        </form>
        <form method="POST" action="{{ route('profile.password') }}" class="card space-y-[18px]">
            @csrf
            @method('PUT')
            <h2 class="font-headline text-[20px] font-semibold">Kata sandi</h2>
            <x-input label="Kata sandi saat ini" name="current_password" type="password" required />
            <x-input label="Kata sandi baru" name="password" type="password" required />
            <x-input label="Ulangi kata sandi baru" name="password_confirmation" type="password" required />
            <button class="btn btn-primary" data-submit>Perbarui kata sandi</button>
        </form>
    </div>
</x-app-layout>
