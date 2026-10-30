<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#121212">
<script>
    (function () {
        var theme = 'dark';
        try {
            var stored = localStorage.getItem('edujurnal-theme');
            if (stored === 'light' || stored === 'dark') {
                theme = stored;
            } else if (window.matchMedia('(prefers-color-scheme: light)').matches) {
                theme = 'light';
            }
        } catch (error) {}
        document.documentElement.setAttribute('data-theme', theme);
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) {
            meta.setAttribute('content', theme === 'light' ? '#f4f4f5' : '#121212');
        }
    })();
</script>
<title>{{ $title ?? 'EduJurnal' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
