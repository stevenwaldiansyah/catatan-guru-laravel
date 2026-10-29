<x-guest-layout>
    <div class="card bg-white shadow-medium">
        <p class="text-[12px] font-semibold uppercase tracking-wide text-primary">EduJurnal</p>
        <h1 class="mt-[6px] font-headline text-[32px] font-bold leading-10">Masuk</h1>
        <p class="mt-[6px] text-[15px] text-muted">Sistem administrasi kegiatan belajar mengajar.</p>
        <form method="POST" action="{{ route('login.store') }}" class="mt-[24px] space-y-[18px]">
            @csrf
            <x-input label="Email atau nama pengguna" name="login" autocomplete="username" required :value="old('login')" />
            <x-input label="Kata sandi" name="password" type="password" autocomplete="current-password" required />
            <x-checkbox label="Ingat saya di perangkat ini" name="remember" />
            <button type="submit" class="btn btn-primary w-full" data-submit>Masuk</button>
        </form>
    </div>
</x-guest-layout>
