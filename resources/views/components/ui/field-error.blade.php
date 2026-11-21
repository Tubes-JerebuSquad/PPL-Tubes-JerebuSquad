@props(['name'])

@error($name)
    <p id="{{ $name }}-error" {{ $attributes->merge(['class' => 'field-error']) }}>{{ $message }}</p>
@enderror
