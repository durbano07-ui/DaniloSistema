@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-ink-400 dark:border-ink-600 text-sm font-medium leading-5 text-ink-900 dark:text-ink-100 focus:outline-none focus:border-ink-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-300 hover:border-ink-300 dark:hover:border-ink-700 focus:outline-none focus:text-ink-700 dark:focus:text-ink-300 focus:border-ink-300 dark:focus:border-ink-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
