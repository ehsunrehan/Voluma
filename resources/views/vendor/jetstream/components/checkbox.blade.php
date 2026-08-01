@props(['disabled' => false])

<input type="checkbox" {{ $disabled ? 'disabled' : '' }} {{ $attributes }}>