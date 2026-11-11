@props(['label', 'value', 'hint' => null, 'icon' => null])
<article class="card flex items-start justify-between gap-4">
    <div>
        <p class="text-[13px] font-medium text-muted">{{ $label }}</p>
        <p class="kpi mt-2">{{ $value }}</p>
        @if ($hint)
            <p class="mt-[6px] text-[12px] leading-5 text-muted">{{ $hint }}</p>
        @endif
    </div>
    @if ($icon)
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-primary" style="background: var(--nav-active-bg)">
            <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>
        </span>
    @endif
</article>
