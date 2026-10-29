@props(['title', 'description' => null])
<header class="mb-[24px] flex flex-col gap-[12px] sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="font-headline text-[24px] font-bold leading-8">{{ $title }}</h1>
        @if ($description)
            <p class="mt-[6px] max-w-[62ch] text-[13px] leading-5 text-muted">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-[12px]">{{ $actions }}</div>
    @endisset
</header>
