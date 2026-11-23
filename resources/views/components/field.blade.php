@props(['name', 'label', 'type' => 'text', 'value' => '', 'hint' => null])
<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        @if(! in_array($type, ['password', 'file'])) value="{{ old($name, $value) }}" @endif
        @if($hint || $errors->has($name)) aria-describedby="{{ $name }}-help" @endif
        @if($errors->has($name)) aria-invalid="true" @endif
        {{ $attributes }}>
    @if($hint || $errors->has($name))
        <small id="{{ $name }}-help" @class(['field-error' => $errors->has($name)])>{{ $errors->first($name) ?: $hint }}</small>
    @endif
</div>
