<x-app-layout>
    <x-page-header :title="$note->student->name" :description="$note->date->translatedFormat('d M Y').' · '.$note->categoryLabel()">
        <x-slot:actions>
            @can('update', $note)
                <a class="btn btn-secondary" href="{{ route('notes.edit', $note) }}">Ubah</a>
                <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('notes.destroy', $note)), title: 'Arsipkan catatan ini?' })">Hapus</button>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <article class="card space-y-[12px]">
        <p><span class="text-muted">Guru.</span> {{ $note->teacher->name }}</p>
        <p><span class="text-muted">Kelas.</span> {{ $note->schoolClass->name }}</p>
        <p>{{ $note->description }}</p>
        <p><span class="text-muted">Tindak lanjut.</span> {{ $note->follow_up ?: '-' }}</p>
        <p class="text-[13px] text-muted">Diperbarui {{ $note->updated_at->translatedFormat('d M Y H:i') }}</p>
    </article>
</x-app-layout>
