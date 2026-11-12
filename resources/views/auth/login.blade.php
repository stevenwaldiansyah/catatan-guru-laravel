<x-guest-layout>
    @php
        $remembered = session()->hasOldInput() ? (string) old('remember') === '1' : false;
    @endphp
    <div class="login-orb login-orb-a" aria-hidden="true"></div>
    <div class="login-orb login-orb-b" aria-hidden="true"></div>
    <div class="login-orb login-orb-c" aria-hidden="true"></div>
    <div class="login-toggle">
        @include('partials.theme-toggle')
    </div>

    <div class="login-shell">
        <section class="login-brand" aria-label="Tentang EduJurnal">
            <div class="login-in" style="animation-delay: 40ms">
                <img src="{{ asset('logo-edujurnal.png') }}" alt="EduJurnal" class="login-logo h-16 w-16 rounded-2xl">
                <p class="mt-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-primary">EduJurnal</p>
            </div>
            <div class="login-lead">
                <h1 class="login-in login-headline" style="animation-delay: 120ms">Kelola Kegiatan Mengajar Lebih Terorganisir.</h1>
                <p class="login-in login-copy" style="animation-delay: 200ms">Pendamping digital untuk membantu guru mengelola administrasi dan kegiatan belajar mengajar dengan lebih mudah, terstruktur, dan efisien.</p>
            </div>
            <div class="login-stage" data-login-stage>
                <div class="login-float login-float-a" style="--depth: 1.1">
                    <div class="login-chip">
                        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M10 14h20a4 4 0 0 1 4 4v18H14a4 4 0 0 1-4-4V14Z"/><path d="M14 14v18a4 4 0 0 0 4 4"/><path d="M18 22h10M18 28h8"/></svg>
                        <span>Jurnal</span>
                    </div>
                </div>
                <div class="login-float login-float-b" style="--depth: 0.7">
                    <div class="login-chip">
                        <svg viewBox="0 0 48 48" aria-hidden="true"><rect x="10" y="12" width="28" height="26" rx="4"/><path d="M10 20h28M18 12v-2M30 12v-2M18 28h4M26 28h4M18 33h4"/></svg>
                        <span>Jadwal</span>
                    </div>
                </div>
                <div class="login-float login-float-c" style="--depth: 1.4">
                    <div class="login-chip">
                        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M16 12h16l6 6v18a2 2 0 0 1-2 2H16a2 2 0 0 1-2-2V14a2 2 0 0 1 2-2Z"/><path d="M30 12v6h6M18 26h12M18 32h8"/></svg>
                        <span>Administrasi</span>
                    </div>
                </div>
                <div class="login-float login-float-d" style="--depth: 0.5">
                    <div class="login-chip">
                        <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M14 34V16l10-4 10 4v18"/><path d="M24 12v22M14 20h20"/></svg>
                        <span>Materi</span>
                    </div>
                </div>
            </div>
        </section>

        <main id="isi" class="login-panel">
            <div class="login-card login-in" style="animation-delay: 80ms">
                <img src="{{ asset('logo-edujurnal.png') }}" alt="" class="login-logo mx-auto h-14 w-14 rounded-2xl">
                <p class="mt-3 text-center text-[12px] font-semibold uppercase tracking-[0.14em] text-primary">EduJurnal</p>
                <h2 class="login-in mt-3 text-center font-headline text-[28px] font-semibold leading-9" style="animation-delay: 160ms">Selamat Datang Kembali</h2>
                <p class="login-in mx-auto mt-2 max-w-[36ch] text-center text-[14px] leading-6 text-muted" style="animation-delay: 220ms">Masuk untuk melanjutkan aktivitas administrasi pembelajaran.</p>

                @if (session('success'))
                    <div class="alert alert-success login-note mt-5" data-autodismiss role="status">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-6 grid gap-4">
                    @csrf
                    @if ($errors->any())
                        <div class="alert alert-error login-note" role="alert">{{ $errors->first() }}</div>
                    @endif
                    <label class="login-in block" style="animation-delay: 260ms" for="login">
                        <span class="label">Email atau nama pengguna</span>
                        <span class="login-control">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.2"/><path d="M5 19c1.4-3 3.8-4.5 7-4.5S17.6 16 19 19"/></svg>
                            <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" required class="field login-input{{ $errors->has('login') ? ' field-invalid' : '' }}" @if($errors->has('login')) aria-invalid="true" aria-describedby="login-error" @endif>
                        </span>
                        @error('login')
                            <span id="login-error" class="sr-only">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="login-in" style="animation-delay: 320ms" x-data="{ show: false }">
                        <label class="block" for="password">
                            <span class="label">Kata sandi</span>
                            <span class="login-control">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                                <input id="password" name="password" type="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required class="field login-input pr-12{{ $errors->has('password') ? ' field-invalid' : '' }}" @if($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>
                                <button type="button" class="login-eye" @click="show = !show" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" :aria-pressed="show">
                                    <svg x-show="!show" x-transition.opacity.duration.150ms viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    <svg x-show="show" x-cloak x-transition.opacity.duration.150ms viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.5 6.2A10 10 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-3.2 3.8"/><path d="M6.1 6.1C3.6 7.8 2 12 2 12s3.5 6 10 6c1.3 0 2.5-.2 3.6-.6"/><path d="M9.9 9.9a2.5 2.5 0 0 0 3.2 3.2"/></svg>
                                </button>
                            </span>
                        </label>
                        @error('password')
                            <span id="password-error" class="sr-only">{{ $message }}</span>
                        @enderror
                    </div>

                    <label class="login-check login-in" style="animation-delay: 380ms">
                        <input type="checkbox" name="remember" value="1" @checked($remembered)>
                        <span>Ingat saya di perangkat ini</span>
                    </label>

                    <div class="login-in" style="animation-delay: 440ms">
                        <button type="submit" class="btn btn-primary login-submit w-full" data-submit>
                            <span class="login-spinner" aria-hidden="true"></span>
                            <span data-submit-label>Masuk</span>
                        </button>
                    </div>
                </form>
            </div>

            <footer class="login-foot login-in" style="animation-delay: 500ms">
                <p>Butuh bantuan? Hubungi administrator.</p>
                <p>© 2026 EduJurnal</p>
            </footer>
        </main>
    </div>

    @push('scripts')
        <script>
            (function () {
                const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                document.querySelectorAll('[data-autodismiss]').forEach(function (notice) {
                    window.setTimeout(function () {
                        if (reduceMotion) {
                            notice.remove();
                            return;
                        }
                        notice.classList.add('is-leaving');
                        notice.addEventListener('animationend', function () {
                            notice.remove();
                        }, { once: true });
                    }, 4200);
                });
                if (reduceMotion) {
                    return;
                }
                const stage = document.querySelector('[data-login-stage]');
                if (!stage) {
                    return;
                }
                stage.addEventListener('pointermove', function (event) {
                    const rect = stage.getBoundingClientRect();
                    const x = ((event.clientX - rect.left) / rect.width - 0.5) * 14;
                    const y = ((event.clientY - rect.top) / rect.height - 0.5) * 10;
                    stage.style.setProperty('--px', x.toFixed(2) + 'px');
                    stage.style.setProperty('--py', y.toFixed(2) + 'px');
                });
            })();
        </script>
    @endpush
</x-guest-layout>
