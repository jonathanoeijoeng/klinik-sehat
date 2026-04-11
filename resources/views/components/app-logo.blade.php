@props([
    'sidebar' => false,
])

@if ($sidebar)
    <flux:sidebar.brand name="Klinik Sehat" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center justify-center h-10 w-10">
            <img src="{{ asset('logo/favicon.png') }}" class="w-10 h-10" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Laravel Starter Kit" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center justify-center h-24 w-24">
            <x-app-logo-icon class="h-24 w-24 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
