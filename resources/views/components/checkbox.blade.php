@props(['label', 'name', 'checked' => false])
@php $isChecked = session()->hasOldInput() ? (string) old($name) === '1' : (bool) $checked; @endphp
<label class="check">
    <input type="checkbox" name="{{ $name }}" value="1" class="checkbox" @checked($isChecked)>
    <span>{{ $label }}</span>
</label>
