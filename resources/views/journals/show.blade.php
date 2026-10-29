<x-app-layout>
    <x-page-header :title="$journal->date->translatedFormat('d M Y')" :description="$journal->schoolClass->name.' · '.$journal->subject->name.' · '.$journal->session_label">
        <x-slot:actions>
            @can('update', $journal)
                <a class="btn btn-secondary" href="{{ route('journals.edit', $journal) }}">Ubah</a>
            @endcan
            @can('delete', $journal)
                <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('journals.destroy', $journal)), title: 'Arsipkan jurnal draf ini?' })">Hapus</button>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <article class="card space-y-[12px]">
        <p><x-badge :tone="$journal->status->value === 'completed' ? 'success' : 'warning'">{{ $journal->status->label() }}</x-badge></p>
        <p><span class="text-muted">Guru.</span> {{ $journal->teacher->name }}</p>
        <p><span class="text-muted">Materi.</span> {{ $journal->material }}</p>
        <p><span class="text-muted">Kegiatan.</span> {{ $journal->activities ?: '-' }}</p>
        <p><span class="text-muted">Metode.</span> {{ $journal->method ?: '-' }}</p>
        <p><span class="text-muted">Kendala.</span> {{ $journal->notes ?: '-' }}</p>
        <p class="text-[13px] text-muted">Diperbarui {{ $journal->updated_at->translatedFormat('d M Y H:i') }}</p>
        @if ($journal->attendanceSession)
            <a class="text-primary" href="{{ route('attendance.show', $journal->attendanceSession) }}">Lihat absensi sesi ini</a>
        @endif
    </article>
</x-app-layout>
