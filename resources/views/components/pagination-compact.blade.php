@props(['paginator'])

@php
    $shouldShow = method_exists($paginator, 'lastPage') ? $paginator->lastPage() > 1 : $paginator->hasMorePages();
@endphp

@if ($shouldShow)
    <div class="md:hidden px-3">

        <div class="flex items-center justify-between">

            {{-- Page Info --}}
            <div class="text-xs text-gray-400">
                {{ $paginator->currentPage() }}
                @if (method_exists($paginator, 'lastPage'))
                    / {{ $paginator->lastPage() }}
                @endif
            </div>

            {{-- Controls --}}
            <div class="flex items-center gap-3">

                {{-- Prev --}}
                @if ($paginator->onFirstPage())
                    <span
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-500 text-gray-300">
                        ‹
                    </span>
                @else
                    <button wire:click="previousPage" wire:loading.attr="disabled"
                        class="w-9 h-9 flex items-center justify-center rounded-full
                           bg-gray-100 dark:bg-gray-500 dark:hover:bg-gray-600 hover:bg-gray-200 transition active:scale-95 cursor-pointer">
                        ‹
                    </button>
                @endif

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <button wire:click="nextPage" wire:loading.attr="disabled"
                        class="w-9 h-9 flex items-center justify-center rounded-full cursor-pointer
                           bg-gray-100 dark:bg-gray-500 dark:hover:bg-gray-600 hover:bg-gray-200 text-gray-800 shadow-sm
                           active:scale-95 transition">
                        ›
                    </button>
                @else
                    <span
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-600  dark:bg-gray-500 dark:hover:bg-gray-600">
                        ›
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif
