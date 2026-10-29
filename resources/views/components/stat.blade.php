@props(['label', 'value', 'hint' => null])
<article class="card">
    <p class="text-[12px] font-semibold uppercase tracking-wide text-muted">{{ $label }}</p>
    <p class="mt-[6px] font-headline text-[32px] font-bold leading-10">{{ $value }}</p>
    @if ($hint)
        <p class="mt-[6px] text-[13px] leading-5 text-muted">{{ $hint }}</p>
    @endif
</article>
