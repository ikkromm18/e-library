@props(['class' => ''])

<div
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    class="{{ $class }}"
>
    <button
        type="button"
        aria-label="Toggle light / dark mode"
        @click="
            dark = !dark;
            document.documentElement.classList.toggle('dark', dark);
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        "
        class="relative inline-flex h-8 w-14 items-center rounded-full border border-border bg-surface-muted px-0.5 transition-colors"
    >
        <span
            class="inline-block h-6 w-6 rounded-full bg-accent text-accent-fg shadow transition-transform duration-200 flex items-center justify-center"
            :class="dark ? 'translate-x-6' : 'translate-x-0'"
        >
            <svg x-show="!dark" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.9 2.1a1 1 0 10-1.4-1.4l-.7.7a1 1 0 001.4 1.4l.7-.7zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 4.7a1 1 0 100 1.4l.7-.7a1 1 0 00-1.4-1.4l-.7.7zM2 10a1 1 0 011-1h1a1 1 0 010 2H3a1 1 0 01-1-1zm12 5.3a1 1 0 011.4 0l.7.7a1 1 0 01-1.4 1.4l-.7-.7a1 1 0 010-1.4zM11 16a1 1 0 100 2h1a1 1 0 010-2h-1z"/></svg>
            <svg x-show="dark" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
        </span>
    </button>
</div>
