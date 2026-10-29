@props(['label', 'name', 'value' => null])
@php $id = $attributes->get('id', $name); $message = $errors->first($name); @endphp
<label class="block" for="{{ $id }}">
    <span class="label">{{ $label }}</span>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="4" @if($message) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif {{ $attributes->merge(['class' => 'field'.($message ? ' field-invalid' : '')]) }}>{{ old($name, $value) }}</textarea>
    @if ($message)
        <span id="{{ $id }}-error" class="field-error">{{ $message }}</span>
    @endif
</label>
