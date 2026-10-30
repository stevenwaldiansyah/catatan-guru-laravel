<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => $title ?? 'Masuk · EduJurnal'])
</head>
<body class="bg-canvas text-ink">
    <a href="#isi" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-card focus:px-3 focus:py-2">Lewati ke isi</a>
    <main id="isi" class="mx-auto flex min-h-screen w-full max-w-[480px] flex-col justify-center px-[18px] py-[36px]">
        @if (session('success'))
            <div class="alert alert-success mb-[18px]" role="status">{{ session('success') }}</div>
        @endif
        {{ $slot }}
    </main>
</body>
</html>
