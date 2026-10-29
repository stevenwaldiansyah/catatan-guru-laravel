@props(['label', 'name'])
@php $id = $attributes->get('id', $name); $message = $errors->first($name); @endphp
<label class="block" for="{{ $id }}">
    <span class="label">{{ $label }}</span>
    <select id="{{ $id }}" name="{{ $name }}" @if($message) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif {{ $attributes->merge(['class' => 'field'.($message ? ' field-invalid' : '')]) }}>
        {{ $slot }}
    </select>
    @if ($message)
        <span id="{{ $id }}-error" class="field-error">{{ $message }}</span>
    @endif
</label>
