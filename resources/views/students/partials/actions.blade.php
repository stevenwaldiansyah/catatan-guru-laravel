@can('view', $student)
    <a class="btn btn-secondary" href="{{ route('students.show', $student) }}">Lihat</a>
@endcan
@can('delete', $student)
    <button type="button" class="btn btn-ghost" @click="$dispatch('confirm', { action: @js(route('students.destroy', $student)), title: @js('Hapus data '.$student->name.'?') })">Hapus</button>
@endcan
