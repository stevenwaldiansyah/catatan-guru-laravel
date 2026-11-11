<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => $title ?? 'Masuk · EduJurnal'])
</head>
<body class="bg-canvas text-ink{{ request()->routeIs('login') ? ' login-screen' : '' }}">
    @unless (request()->routeIs('login'))
    <div class="fixed right-4 top-4 z-20">
        @include('partials.theme-toggle')
    </div>
    @endunless
    <a href="#isi" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-card focus:px-3 focus:py-2">Lewati ke isi</a>
    @if (request()->routeIs('login'))
        {{ $slot }}
        @stack('scripts')
    @else
    <main id="isi" class="mx-auto flex min-h-screen w-full max-w-[480px] flex-col justify-center px-[18px] py-[36px]">
        <a href="{{ route('login') }}" class="btn btn-ghost mb-[18px] w-fit px-3" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Kembali
        </a>
        @if (session('success'))
            <div class="alert alert-success mb-[18px]" role="status">{{ session('success') }}</div>
        @endif
        {{ $slot }}
    </main>
    @endif
</body>
</html>
