<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $matched = collect($navItems ?? [])->first(fn (array $item) => request()->routeIs(...(array) $item['match']));
        $pageTitle = $title ?? ($matched['label'] ?? (request()->routeIs('profile.*') ? 'Profil' : 'EduJurnal'));
    @endphp
    @include('partials.head', ['title' => $pageTitle === 'EduJurnal' ? 'EduJurnal' : $pageTitle.' · EduJurnal'])
</head>
<body class="bg-canvas text-ink" x-data="{ nav: false, account: false, confirmOpen: false, confirmAction: '', confirmTitle: '', confirmMethod: 'POST' }" @confirm.window="confirmOpen = true; confirmAction = $event.detail.action; confirmTitle = $event.detail.title; confirmMethod = $event.detail.method || 'DELETE'" @keydown.escape.window="nav = false; account = false; confirmOpen = false" x-effect="document.body.classList.toggle('overflow-hidden', nav || confirmOpen)">
    <a href="#isi" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-card focus:px-3 focus:py-2">Lewati ke isi</a>

    <aside class="fixed inset-y-0 left-0 z-30 hidden w-[264px] flex-col border-r border-line sidebar-surface md:flex">
        @include('partials.nav')
    </aside>

    <div x-show="nav" x-cloak class="fixed inset-0 z-40 md:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="absolute inset-0 bg-black/60" @click="nav = false"></div>
        <aside class="absolute inset-y-0 left-0 flex w-[min(100%,300px)] flex-col sidebar-surface shadow-large">
            @include('partials.nav')
        </aside>
    </div>

    <div class="min-w-0 md:pl-[264px]">
        <header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-line sidebar-surface px-4 py-3 backdrop-blur md:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" class="btn btn-ghost md:hidden" @click="nav = true" aria-label="Buka menu">
                    <i data-lucide="menu"></i>
                </button>
                <div class="min-w-0">
                    <p class="truncate text-[12px] text-muted">
                        <a href="{{ route('dashboard') }}" class="hover:text-primary">Dasbor</a>
                        @unless (request()->routeIs('dashboard'))
                            <span aria-hidden="true"> / </span>
                            <span>{{ $pageTitle }}</span>
                        @endunless
                    </p>
                    <p class="truncate font-headline text-[18px] font-semibold leading-6">{{ $pageTitle }}</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <form method="GET" action="{{ route('students.index') }}" class="relative hidden lg:block">
                    <label class="sr-only" for="cari-global">Cari siswa</label>
                    <input id="cari-global" class="field w-[220px] pl-10" name="q" placeholder="Cari siswa" autocomplete="off">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                </form>
                <p class="hidden text-[13px] text-muted xl:block">{{ now()->translatedFormat('d M Y') }}</p>
                @include('partials.theme-toggle')
                <div class="relative">
                    <button type="button" class="btn btn-ghost" aria-label="Akun" @click="account = !account">
                        <i data-lucide="circle-user"></i>
                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    </button>
                    <div x-show="account" x-cloak @click.outside="account = false" class="popover absolute right-0 z-30 mt-2 w-[220px] grid gap-1 p-2">
                        <a class="nav-link" href="{{ route('profile.edit') }}">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link w-full">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main id="isi" class="mx-auto w-full min-w-0 max-w-[1360px] px-4 py-6 pb-24 md:px-6 md:pb-8 lg:px-8">
            @php
                $onMainMenu = collect($navItems ?? [])->contains(fn (array $item) => request()->routeIs($item['route']));
                $previous = url()->previous();
                $backUrl = str_starts_with($previous, url('/')) && $previous !== url()->current() && ! str_contains($previous, '/login')
                    ? $previous
                    : null;
            @endphp
            @unless ($onMainMenu)
                <a href="{{ $backUrl ?? route('dashboard') }}" class="btn btn-ghost mb-[18px] w-fit px-3" @unless($backUrl) onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }" @endunless>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    Kembali
                </a>
            @endunless
            @if (session('success'))
                <div class="alert alert-success mb-[18px]" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error mb-[18px]" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error mb-[18px]" role="alert">
                    <p>Periksa kembali isian berikut.</p>
                    <ul class="mt-[6px] list-disc pl-[18px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-canvas md:hidden" style="padding-bottom: env(safe-area-inset-bottom)" aria-label="Navigasi cepat">
        @php $bottomItems = collect($navItems)->where('bottom', true); @endphp
        <ul class="grid" style="grid-template-columns: repeat({{ $bottomItems->count() + 1 }}, minmax(0, 1fr))">
            @foreach ($bottomItems as $item)
                @php $active = request()->routeIs(...(array) $item['match']); @endphp
                <li>
                    <a href="{{ route($item['route']) }}" class="flex min-h-[56px] flex-col items-center justify-center gap-[2px] text-[12px] {{ $active ? 'text-primary' : 'text-muted' }}" @if($active) aria-current="page" @endif>
                        <i data-lucide="{{ $item['icon'] }}" class="h-[18px] w-[18px]"></i>
                        {{ $item['short'] ?? $item['label'] }}
                    </a>
                </li>
            @endforeach
            <li>
                <button type="button" class="flex min-h-[56px] w-full flex-col items-center justify-center gap-[2px] text-[12px] text-muted" @click="nav = true">
                    <i data-lucide="menu" class="h-[18px] w-[18px]"></i>
                    Menu
                </button>
            </li>
        </ul>
    </nav>

    <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 p-[18px] sm:items-center" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
        <div class="w-full max-w-[420px] rounded-2xl border border-line bg-card p-6 shadow-large" @click.outside="confirmOpen = false">
            <h2 id="confirm-title" class="font-headline text-[20px] font-semibold leading-7" x-text="confirmTitle"></h2>
            <p class="mt-[12px] text-[15px] text-muted">Tindakan ini baru dijalankan setelah Anda menekan tombol konfirmasi.</p>
            <form method="POST" :action="confirmAction" class="mt-[18px] flex flex-wrap gap-[12px]">
                @csrf
                <template x-if="confirmMethod !== 'POST'"><input type="hidden" name="_method" :value="confirmMethod"></template>
                <button type="submit" class="btn btn-danger" data-submit>Ya, lanjutkan</button>
                <button type="button" class="btn btn-secondary" @click="confirmOpen = false">Batal</button>
            </form>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
