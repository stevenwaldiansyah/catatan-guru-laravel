@props(['label', 'name', 'type' => 'text', 'value' => null])
@php $id = $attributes->get('id', $name); $message = $errors->first($name); @endphp
<label class="block" for="{{ $id }}">
    <span class="label">{{ $label }}</span>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @if($message) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif {{ $attributes->merge(['class' => 'field'.($message ? ' field-invalid' : '')]) }}>
    @if ($message)
        <span id="{{ $id }}-error" class="field-error">{{ $message }}</span>
    @endif
</label>
