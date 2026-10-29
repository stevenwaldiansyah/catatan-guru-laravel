@props(['title', 'text' => null])
<div class="card py-[36px] text-center">
    <i data-lucide="inbox" class="mx-auto text-muted"></i>
    <h2 class="mt-[12px] font-headline text-[20px] font-semibold">{{ $title }}</h2>
    @if ($text)
        <p class="mx-auto mt-[6px] max-w-[42ch] text-[13px] leading-5 text-muted">{{ $text }}</p>
    @endif
    @if (trim($slot))
        <div class="mt-[18px]">{{ $slot }}</div>
    @endif
</div>
