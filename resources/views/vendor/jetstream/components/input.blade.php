@props(['disabled' => false])

<input placeholder=" " {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => '']) !!}>