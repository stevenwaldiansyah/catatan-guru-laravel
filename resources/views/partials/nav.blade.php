@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $mainItems = collect($navItems)->where('section', 'main');
    $systemItems = collect($navItems)->where('section', 'system');
@endphp
<div class="flex items-center gap-3 border-b border-line px-5 py-5">
    <img src="{{ asset('logo-edujurnal.png') }}" alt="EduJurnal" class="h-10 w-10 rounded-xl">
    <div class="min-w-0">
        <p class="font-headline text-[18px] font-semibold leading-6 text-ink">EduJurnal</p>
        <p class="truncate text-[12px] text-muted">{{ $schoolName }}</p>
    </div>
</div>
<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Menu utama">
    @foreach ($mainItems as $item)
        @php $active = request()->routeIs(...(array) $item['match']); @endphp
        <a href="{{ route($item['route']) }}" class="nav-link {{ $active ? 'nav-link-active' : '' }}" @if($active) aria-current="page" @endif>
            <i data-lucide="{{ $item['icon'] }}" class="h-[18px] w-[18px]"></i>
            {{ $item['label'] }}
        </a>
    @endforeach
    @if ($systemItems->isNotEmpty())
        <div class="mx-3 my-3 border-t border-line"></div>
        @foreach ($systemItems as $item)
            @php $active = request()->routeIs(...(array) $item['match']); @endphp
            <a href="{{ route($item['route']) }}" class="nav-link {{ $active ? 'nav-link-active' : '' }}" @if($active) aria-current="page" @endif>
                <i data-lucide="{{ $item['icon'] }}" class="h-[18px] w-[18px]"></i>
                {{ $item['label'] }}
            </a>
        @endforeach
    @endif
</nav>
<div class="border-t border-line p-3">
    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-hover">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-[13px] font-semibold text-white">{{ $initials }}</span>
        <span class="min-w-0">
            <span class="block truncate text-[14px] font-semibold text-ink">{{ $user->name }}</span>
            <span class="block text-[12px] text-muted">{{ $user->isAdmin() ? 'Admin' : 'Guru' }}</span>
        </span>
    </a>
    <form method="POST" action="{{ route('logout') }}" class="mt-1">
        @csrf
        <button type="submit" class="nav-link w-full">
            <i data-lucide="log-out" class="h-[18px] w-[18px]"></i>
            Keluar
        </button>
    </form>
</div>
