<div class="border-b border-line px-[18px] py-[24px]">
    <p class="text-[12px] font-semibold uppercase tracking-wide text-primary">EduJurnal</p>
    <p class="mt-[6px] font-headline text-[20px] font-semibold leading-7">{{ $schoolName }}</p>
</div>
<nav class="flex-1 space-y-[6px] overflow-y-auto px-[12px] py-[18px]" aria-label="Menu utama">
    @foreach ($navItems as $item)
        @php $active = request()->routeIs($item['match']); @endphp
        <a href="{{ route($item['route']) }}" class="nav-link {{ $active ? 'nav-link-active' : '' }}" @if($active) aria-current="page" @endif>
            <i data-lucide="{{ $item['icon'] }}" class="h-[18px] w-[18px]"></i>
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
<div class="border-t border-line p-[12px]">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="nav-link w-full">
            <i data-lucide="log-out" class="h-[18px] w-[18px]"></i>
            Keluar
        </button>
    </form>
</div>
