@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-ink-400 dark:border-ink-600 text-start text-base font-medium text-ink-700 dark:text-ink-300 bg-ink-50 dark:bg-ink-900/50 focus:outline-none focus:text-ink-800 dark:focus:text-ink-200 focus:bg-ink-100 dark:focus:bg-ink-900 focus:border-ink-700 dark:focus:border-ink-300 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-ink-600 dark:text-ink-400 hover:text-ink-800 dark:hover:text-ink-200 hover:bg-ink-50 dark:hover:bg-ink-700 hover:border-ink-300 dark:hover:border-ink-600 focus:outline-none focus:text-ink-800 dark:focus:text-ink-200 focus:bg-ink-50 dark:focus:bg-ink-700 focus:border-ink-300 dark:focus:border-ink-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
