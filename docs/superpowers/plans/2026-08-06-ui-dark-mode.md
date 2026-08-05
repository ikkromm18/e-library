# UI Redesign + Dark Mode Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Introduce a light/dark theme across the whole Laravel e-library UI using token-driven CSS variables, keeping the blue accent, with system-follow + manual toggle persisted in localStorage.

**Architecture:** Enable `darkMode: 'class'` and map semantic color tokens (surface, text, border, accent, status) to CSS custom properties via one `:root` + `.dark` block in `app.css`. Rewrite all Blade color literals to those token utilities so components are automatically theme-aware — no scattered `dark:` variants. A tiny inline script applies the persisted/system theme before first paint, and an Alpine toggle switches it.

**Tech Stack:** Tailwind CSS v3 (`tailwind.config.js` ESM), Alpine.js 3, Blade SFC (Livewire 4), Figtree font, Vite.

## Global Constraints

- `tailwind.config.js` uses ESM `export default`.
- Tailwind v3 — set `darkMode: 'class'` (NOT `'media'`).
- Keep blue accent (blue-600 light / blue-500 dark).
- No new npm/composer dependencies.
- Do NOT edit `app/` logic or `.php` Livewire classes — Blade/classes only.
- Do NOT touch `resources/views/reports/pdf/*` (print-only) or `welcome.blade.php` redesign beyond dark-aware surface/text swap.
- Run `vendor/bin/pint` before each commit.
- Run `composer test` (full suite) at end; must stay green.

### Color Mapping Reference (apply in every page task)

Swap each left-hand class for the right-hand token class everywhere it appears.

| Old | New |
|-----|-----|
| `bg-white` (cards, tables, modal, nav, header card, dropdown content) | `bg-surface` |
| `bg-gray-50` | `bg-surface-muted` |
| `bg-gray-100` (inputs, muted blocks, page bg) | `bg-surface-muted` |
| `bg-gray-100` (outer page wrapper in `app.blade.php`/`guest.blade.php`) | `bg-body` |
| `bg-gray-200` | `bg-surface-muted` |
| `bg-gray-300` (cancel buttons) | `bg-surface-muted text-text-primary hover:bg-surface-raised` |
| `bg-gray-700`/`bg-gray-900` | `bg-surface-raised` |
| `bg-gray-800` | `bg-surface-raised` |
| `bg-blue-600` | `bg-accent` |
| `hover:bg-blue-700` | `hover:bg-accent-hover` |
| `bg-blue-700` (base) | `bg-accent` |
| `bg-blue-50` | `bg-accent-soft` |
| `bg-blue-100` | `bg-accent-soft` |
| `text-gray-800` | `text-text-primary` |
| `text-gray-900` | `text-text-primary` |
| `text-gray-700` | `text-text-primary` |
| `text-gray-600` | `text-text-secondary` |
| `text-gray-500` | `text-text-secondary` |
| `text-gray-400` | `text-text-secondary` |
| `text-white` (on `bg-accent`) | `text-white` (keep) |
| `text-black` | `text-text-primary` |
| `text-blue-600` | `text-accent` |
| `text-blue-700` | `text-accent` |
| `text-blue-800`/`text-blue-900` | `text-accent` |
| `hover:text-blue-900` | `hover:text-accent-hover` |
| `hover:text-gray-900`/`hover:text-gray-700` | `hover:text-text-primary` |
| `border-gray-300` (input borders) | `border-border` |
| `border-gray-200` | `border-border` |
| `border-gray-100` | `border-border` |
| `border-gray-400` | `border-border` |
| `border-white`/`border-black` | `border-border` |
| `hover:border-gray-300` | `hover:border-border` |
| `hover:border-gray-400`/`hover:border-gray-200` | `hover:border-border` |
| `divide-gray-200`/`divide-gray-100` | `divide-border` |
| `focus:border-blue-500` | `focus:border-accent` |
| `focus:ring-blue-500` | `focus:ring-accent` |
| `focus:border-indigo-700`/`focus:border-indigo-500` | `focus:border-accent` |
| `focus:ring-indigo-500` | `focus:ring-accent` |
| `focus:bg-gray-100` | `focus:bg-surface-muted` |
| `border-indigo-400`/`border-indigo-700` (active nav) | `border-accent` |
| `text-indigo-800` | `text-accent` |
| `bg-indigo-100` | `bg-accent-soft` |
| `bg-green-100` (success badge) | `bg-success text-success-fg` |
| `text-green-600`/`text-green-700`/`text-green-800`/`text-green-900` | `text-success-fg` |
| `border-green-400` (success alert) | `border-success text-success-fg` |
| `bg-green-700` (button) | `bg-accent` |
| `hover:bg-green-700` | `hover:bg-accent-hover` |
| `text-green-500` | `text-success-fg` |
| `bg-red-100` (danger badge) | `bg-danger text-danger-fg` |
| `bg-red-600`/`bg-red-700` (solid danger button) | `bg-danger-solid` |
| `hover:bg-red-700`/`hover:bg-red-500` | `hover:bg-red-600` (keep red) |
| `text-red-500`/`text-red-600`/`text-red-700`/`text-red-800`/`text-red-900` | `text-danger-fg` |
| `border-red-400` (error alert) | `border-danger-fg text-danger-fg` |
| `bg-amber-100` (warning badge) | `bg-warning text-warning-fg` |
| `text-amber-800`/`text-amber-900` | `text-warning-fg` |
| `border-amber-*/text-amber-*` alerts | `border-warning text-warning-fg` |
| `shadow` on cards | `shadow-sm` |
| modal scrim `bg-black bg-opacity-50` | `bg-black/50` keep |

Note about route/`!` — do not convert infrastructure classes (`flex`,`grid`,`px-`…). Only colors per table.

---

## Task 1: Tailwind config + token CSS

**Files:**
- Modify: `tailwind.config.js`
- Modify: `resources/css/app.css`

**Interfaces:**
- Produces: `darkMode:'class'`, token color utilities (`surface`,`surface-raised`,`surface-muted`,`text-primary`,`text-secondary`,`border`,`accent{ DEFAULT/hover/soft }`,`success{ fg }`,`danger{ solid,fg }`,`warning{ fg }`,`body`) consumed by every later task.

- [ ] **Step 1: Enable dark mode + tokens in config**

`tailwind.config.js` — replace whole `theme.extend.colors` block:

```js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                surface: 'rgb(var(--surface) / <alpha-value>)',
                'surface-raised': 'rgb(var(--surface-raised) / <alpha-value>)',
                'surface-muted': 'rgb(var(--surface-muted) / <alpha-value>)',
                'text-primary': 'rgb(var(--text-primary) / <alpha-value>)',
                'text-secondary': 'rgb(var(--text-secondary) / <alpha-value>)',
                border: 'rgb(var(--border) / <alpha-value>)',
                body: 'rgb(var(--body) / <alpha-value>)',
                accent: {
                    DEFAULT: 'rgb(var(--accent) / <alpha-value>)',
                    hover: 'rgb(var(--accent-hover) / <alpha-value>)',
                    soft: 'rgb(var(--accent-soft) / <alpha-value>)',
                    fg: 'rgb(var(--accent-fg) / <alpha-value>)',
                },
                success: {
                    DEFAULT: 'rgb(var(--success) / <alpha-value>)',
                    fg: 'rgb(var(--success-fg) / <alpha-value>)',
                },
                danger: {
                    solid: 'rgb(var(--danger-solid) / <alpha-value>)',
                    fg: 'rgb(var(--danger-fg) / <alpha-value>)',
                    DEFAULT: 'rgb(var(--danger) / <alpha-value>)',
                },
                warning: {
                    DEFAULT: 'rgb(var(--warning) / <alpha-value>)',
                    fg: 'rgb(var(--warning-fg) / <alpha-value>)',
                },
            },
        },
    },

    plugins: [forms],
};
```

- [ ] **Step 2: Reset + tokens in `app.css`**

Replace `resources/css/app.css` entirely:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
    :root {
        color-scheme: light;
        --body: 241 245 249;           /* gray-100 */
        --surface: 255 255 255;       /* white */
        --surface-raised: 255 255 255;/* white (modals) */
        --surface-muted: 243 244 246; /* gray-100 */
        --text-primary: 17 24 39;     /* gray-900 */
        --text-secondary: 75 85 99;   /* gray-600 */
        --border: 229 231 235;        /* gray-200 */
        --accent: 37 99 235;          /* blue-600 */
        --accent-hover: 29 78 216;    /* blue-700 */
        --accent-soft: 239 246 255;   /* blue-50 */
        --accent-fg: 255 255 255;
        --success: 220 252 231;       /* green-100 */
        --success-fg: 22 101 52;      /* green-800 */
        --danger: 254 226 226;        /* red-100 */
        --danger-fg: 153 27 27;       /* red-800 */
        --danger-solid: 220 38 38;    /* red-600 */
        --warning: 254 243 199;       /* amber-100 */
        --warning-fg: 146 64 14;      /* amber-800 */
    }

    .dark {
        color-scheme: dark;
        --body: 17 24 39;             /* gray-900 */
        --surface: 31 41 55;          /* gray-800 */
        --surface-raised: 15 23 42;   /* near slategray-900 for modals */
        --surface-muted: 55 65 81;    /* gray-700 */
        --text-primary: 249 250 251;  /* gray-50 */
        --text-secondary: 209 213 219;/* gray-300 */
        --border: 55 65 81;           /* gray-700 */
        --accent: 96 165 250;         /* blue-400 */
        --accent-hover: 147 197 253;  /* blue-300 */
        --accent-soft: 30 58 138;     /* blue-900 */
        --accent-fg: 15 23 42;
        --success: 6 46 32 / 0.25;
        --success-fg: 134 239 172;    /* green-300 */
        --danger: 69 10 10 / 0.25;
        --danger-fg: 252 165 165;     /* red-300 */
        --danger-solid: 239 68 68;    /* red-500 */
        --warning: 68 40 6 / 0.25;
        --warning-fg: 252 211 77;     /* amber-300 */
    }

    body {
        @apply bg-body text-text-primary;
    }
}

@layer utilities {
    .theme-transition *,
    .theme-transition {
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }
}
```

- [ ] **Step 3: Verify token build**

Run: `npm run build`
Expected: compiles without error; `darkMode` warning absent.

- [ ] **Step 4: Commit**

```bash
git add tailwind.config.js resources/css/app.css
git commit -m "build: add dark-mode tokens and semantic color system"
```

---

## Task 2: Anti-flash theme script + layouts

**Files:**
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/layouts/guest.blade.php`

**Interfaces:**
- Consumes: `darkMode:'class'` + `.dark` from Task 1.
- Produces: inline script that toggles `document.documentElement.classList`; callable `setThemeIcon()` for Task 3.

- [ ] **Step 1: Add theme script to head of `app.blade.php`**

Insert before `@livewireStyles`:

```blade
<script>
    (function () {
        const stored = localStorage.getItem('theme');
        const dark = stored === 'dark'
            || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', dark);
        window.themePreference = stored || 'system';
    })();
</script>
```

- [ ] **Step 2: Application-shell body theme**

In `app.blade.php` change `<body class="font-sans antialiased">` → `<body class="font-sans antialiased theme-transition">`, and the wrapper `min-h-screen bg-gray-100` → `min-h-screen bg-body`.

- [ ] **Step 3: Header dark-aware**

Change `<header class="bg-white shadow">` → `<header class="bg-surface border-b border-border shadow-sm">`.

- [ ] **Step 4: Guest layout anti-flash + tokens**

Add same inline script before closing `</head>` in `guest.blade.php`. Change `<body class="font-sans text-gray-900 antialiased">` → `<body class="font-sans text-text-primary antialiased theme-transition">`. Change wrapper `bg-gray-100` → `bg-body`, card `bg-white shadow-md overflow-hidden` → `bg-surface shadow-md overflow-hidden`.

- [ ] **Step 5: Verify**

Run: `composer test` — green (renders bare HTML). Manual: reload page, `document.documentElement` gets `dark` per system.

- [ ] **Step 6: Commit**

```bash
git add resources/views/layouts/app.blade.php resources/views/layouts/guest.blade.php
git commit -m "feat(layouts): anti-flash theme script + dark-aware base layouts"
```

---

## Task 3: Theme toggle component

**Files:**
- Create: `resources/views/components/theme-toggle.blade.php`

**Interfaces:**
- Consumes: `window.themePreference`.
- Produces: expressive button; `@click` toggler used in nav.

- [ ] **Step 1: Create toggle**

```blade
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
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/theme-toggle.blade.php
git commit -m "feat: add theme-toggle component"
```

---

## Task 4: Navigation bar

**Files:**
- Modify: `resources/views/layouts/navigation.blade.php`

- [ ] **Step 1: nav surface + border**

`<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">` → `<nav x-data="{ open: false }" class="bg-surface border-b border-border">`

- [ ] **Step 2: user dropdown trigger**

`class="... text-gray-500 bg-white hover:text-gray-700 ..."` → text-gray-500→`text-text-secondary`, drop `bg-white` (button over surface), `hover:text-gray-700`→`hover:text-text-primary`.

- [ ] **Step 3: responsive user block**

`font-medium text-base text-gray-800`→`text-text-primary`; `font-medium text-sm text-gray-500`→`text-text-secondary`; `border-t border-gray-200`→`border-t border-border`.

- [ ] **Step 4: hamburger**

`text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:bg-gray-100` → `text-text-secondary hover:text-text-primary hover:bg-surface-muted focus:bg-surface-muted`.

- [ ] **Step 5: insert toggle**

Place `<x-theme-toggle />` just before the user dropdown block:

```html
<div class="flex items-center">
    <x-theme-toggle />
</div>
```

- [ ] **Step 6: Commit**

```bash
git add resources/views/layouts/navigation.blade.php
git commit -m "feat: dashboard nav dark-aware + theme toggle"
```
---

## Task 5: Shared form/UI components

**Files:**
- Modify: `resources/views/components/primary-button.blade.php`
- Modify: `resources/views/components/secondary-button.blade.php`
- Modify: `resources/views/components/danger-button.blade.php`
- Modify: `resources/views/components/text-input.blade.php`
- Modify: `resources/views/components/modal.blade.php`
- Modify: `resources/views/components/dropdown.blade.php`
- Modify: `resources/views/components/nav-link.blade.php`
- Modify: `resources/views/components/responsive-nav-link.blade.php`
- Modify: `resources/views/components/input-label.blade.php`
- Modify: `resources/views/components/input-error.blade.php`
- Modify: `resources/views/components/dropdown-link.blade.php`

- [ ] **Step 1: primary-button**

`bg-gray-800 ... hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:ring-indigo-500` → `bg-accent text-white hover:bg-accent-hover focus:bg-accent-hover active:bg-accent-hover focus:ring-accent`. New class string:

```blade
class="inline-flex items-center px-4 py-2 bg-accent border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-accent-hover focus:bg-accent-hover active:bg-accent-hover focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 transition ease-in-out duration-150"
```

- [ ] **Step 2: secondary-button**

`bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-indigo-500` → `bg-surface border border-border text-text-primary hover:bg-surface-muted focus:ring-accent`.

- [ ] **Step 3: danger-button**

`bg-red-600 hover:bg-red-500 active:bg-red-700 focus:ring-red-500` → `bg-danger-solid hover:bg-red-500 active:bg-red-700 focus:ring-red-500`.

- [ ] **Step 4: text-input**

`border-gray-300 focus:border-indigo-500 focus:ring-indigo-500` → `border-border bg-surface-muted focus:border-accent focus:ring-accent text-text-primary`. (Add `bg-surface-muted` so inputs visible on surface.)

- [ ] **Step 5: modal**

Line 63 `<div class="absolute inset-0 bg-gray-500 opacity-75"></div>` → `<div class="absolute inset-0 bg-black/50"></div>`. Line 68 `mb-6 bg-white ... shadow-xl` → `mb-6 bg-surface ... shadow-xl`.

- [ ] **Step 6: dropdown**

Default prop `'contentClasses' => 'py-1 bg-white'` → `'contentClasses' => 'py-1 bg-surface'`. Line 28 `ring-black ring-opacity-5` → `ring-border`. Line 31 outer container same ring class → `ring-border`.

- [ ] **Step 7: nav-link**

In `nav-link.blade.php` replace $classes strings per mapping:
Active: `border-b-2 border-indigo-400 text-gray-900 focus:border-indigo-700` → `border-b-2 border-accent text-text-primary focus:border-accent`.
Inactive: `border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300` → `border-transparent text-text-secondary hover:text-text-primary hover:border-border focus:text-text-primary focus:border-border`.

- [ ] **Step 8: responsive-nav-link**

Active: `border-l-4 border-indigo-400 text-indigo-700 bg-indigo-50 focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700` → `border-l-4 border-accent text-accent bg-accent-soft focus:text-accent focus:bg-accent-soft focus:border-accent`.
Inactive: `text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300` → `text-text-secondary hover:text-text-primary hover:bg-surface-muted hover:border-border focus:text-text-primary focus:bg-surface-muted focus:border-border`.

- [ ] **Step 9: input-label**

Read `input-label.blade.php` — if it uses `text-gray-700`, change → `text-text-primary`. If `text-gray-900`, → `text-text-primary`.

- [ ] **Step 10: input-error**

Read `input-error.blade.php` — `text-red-600` → `text-danger-fg`.

- [ ] **Step 11: dropdown-link**

Read `dropdown-link.blade.php` — root classes use `text-gray-700 hover:text-gray-900 hover:bg-gray-100` → `text-text-primary hover:bg-surface-muted`; if it has `text-gray-500`/`text-gray-600` → `text-text-secondary`.

- [ ] **Step 12: Verify + commit**

Run: `npm run build`, `composer test`. Green.

```bash
git add resources/views/components/
git commit -m "feat(components): dark-aware shared buttons/inputs/modal/dropdown/nav"
```

---

## Task 6: Dashboard + page headers

**Files:**
- Modify: `resources/views/dashboard.blade.php`
- Modify: `resources/views/components/dashboard/dashboard.blade.php`
- Modify: `resources/views/{buku,kategori,rak,anggota,import,peminjaman,pengembalian,laporan,pengguna,setting}/*/index.blade.php`

- [ ] **Step 1: dashboard stat cards**

Six stat cards `bg-white rounded-lg shadow p-4` → `bg-surface rounded-lg shadow-sm p-4`. Inner `text-3xl font-bold text-gray-800` → `text-text-primary`. Labels `text-sm text-gray-500` → `text-sm text-text-secondary`. "Terlambat" number `text-red-600` → `text-danger-fg`.

- [ ] **Step 2: chart card**

`bg-white rounded-lg shadow p-4 mb-6` → `bg-surface rounded-lg shadow-sm p-4 mb-6`. Title `font-semibold text-gray-800` → `text-text-primary`. Bar `bg-blue-500` → `bg-accent`. Bar value/date `text-gray-500` → `text-text-secondary`.

- [ ] **Step 3: shortcut cards**

Three cards `bg-white rounded-lg shadow p-4 text-center text-blue-600 hover:bg-blue-50` → `bg-surface rounded-lg shadow-sm p-4 text-center text-accent hover:bg-accent-soft`.

- [ ] **Step 4: verify**

`npm run build`, `composer test`.

- [ ] **Step 5: Commit**

```bash
git add resources/views/dashboard.blade.php resources/views/components/dashboard/dashboard.blade.php
git commit -m "feat(dashboard): token-driven cards + dark-aware shortcuts"
```

---

## Task 7: Master Buku view

**Files:**
- Modify: `resources/views/components/buku/buku.blade.php`

- [ ] **Step 1: heading + primary action**

`text-2xl font-bold text-gray-800` → `text-text-primary`. `bg-blue-600 hover:bg-blue-700` → `bg-accent hover:bg-accent-hover`.

- [ ] **Step 2: alerts**

Success `bg-green-100 border border-green-400 text-green-700` → `bg-success border border-success text-success-fg`. Error `bg-red-100 border border-red-400 text-red-700` → `bg-danger border border-danger-fg text-danger-fg`.

- [ ] **Step 3: search/filter card**

`bg-white rounded-lg shadow p-4 mb-6` → `bg-surface rounded-lg shadow-sm p-4 mb-6`. All inputs `border-gray-300 ... focus:border-blue-500 focus:ring-blue-500` → `border-border ... focus:border-accent focus:ring-accent`. (6 fields.)

- [ ] **Step 4: detail modal**

Scrim `bg-black bg-opacity-50` → `bg-black/50`. Card `bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6` → `bg-surface-raised rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6`. `text-lg font-semibold` → add `text-text-primary`. Close button `text-gray-400 hover:text-gray-600` → `text-text-secondary hover:text-text-primary`. Labels `font-medium text-gray-500` → `font-medium text-text-secondary`. Status badge `bg-green-100 text-green-800`/`bg-red-100 text-red-800` → `bg-success text-success-fg`/`bg-danger text-danger-fg`.

- [ ] **Step 5: form card**

`bg-white rounded-lg shadow p-6 mb-6` → `bg-surface rounded-lg shadow-sm p-6 mb-6`. `text-lg font-semibold mb-4` → add `text-text-primary`. All labels `block text-sm font-medium text-gray-700` → `text-text-primary`. All error `<span class="text-red-500 text-sm">` → `text-danger-fg`. Readonly kode input `bg-gray-50` → keep `bg-surface-muted`. Submit `bg-blue-600 hover:bg-blue-700` → `bg-accent hover:bg-accent-hover`; Batal `bg-gray-300 hover:bg-gray-400 text-gray-700` → `bg-surface-muted hover:bg-surface-raised text-text-primary`. Cover file input `file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100` → `file:bg-accent-soft file:text-accent hover:file:bg-accent`.

- [ ] **Step 6: table**

`bg-white rounded-lg shadow overflow-hidden` → `bg-surface border border-border shadow-sm overflow-hidden` — keep table. `thead bg-gray-50` → `bg-surface-muted`; th `text-gray-500` → `text-text-secondary`. `tbody` divider `divide-gray-200` → `divide-border`; cell title `text-gray-900` → `text-text-primary`; `text-gray-500` → `text-text-secondary`. Badges `bg-green-100 text-green-800`/`bg-red-100 text-red-800` → tokens. Row actions Detail `text-green-600`→ `text-accent`, Edit `text-blue-600`→`text-accent`, Hapus `text-red-600`→`text-danger-fg`; hovers `text-gray-900`→`text-text-primary`, `text-blue-900`→`text-accent-hover`, `text-red-900`→`text-danger-fg`. Empty row `text-gray-500`→`text-text-secondary`. Pagination wrapper `border-t`→`border-t border-border`.

- [ ] **Step 7: Verify + commit**

```bash
git add resources/views/components/buku/buku.blade.php
git commit -m "feat(buku): dark-aware master list, filters, form, modal"
```

- [ ] **Step 8: Commit**

```bash
git add resources/views/components/buku/buku.blade.php
git commit -m "feat(buku): dark-aware token table, search, form, modal"
```

---

## Task 8: Kategori, Rak, Modal-less

**Files:**
- Modify: `resources/views/components/kategori/kategori.blade.php`
- Modify: `resources/views/components/rak/rak.blade.php`

Apply the Color Mapping Reference everywhere.

- [ ] **Step 1: kategori blade**

Heading `text-gray-800`→`text-text-primary`; primary button `bg-blue-600 hover:bg-blue-700`→`bg-accent hover:bg-accent-hover`; form/table `bg-white`→`bg-surface`, `shadow`→`shadow-sm`; table head `bg-gray-50`→`bg-surface-muted`, `border-gray-*`→`border-border`; action text `text-blue-600`→`text-accent`, `text-red-600`→`text-danger-fg`; badges `bg-green-100 text-green-800`→`bg-success text-success-fg`. Alerts `bg-green-100 border-green-400 text-green-700`→`bg-success text-success-fg` etc.

- [ ] **Step 2: rak blade**

Same sweep (heading, button, alert, table/surface, badges).

- [ ] **Step 3: Verify + commit**

```bash
git commit -am "feat(kategori,rak): dark-aware tables and forms"
```

---

## Task 9: Anggota + import

**Files:**
- Modify: `resources/views/components/anggota/anggota.blade.php`
- Modify: `resources/views/components/anggota/member-import/member-import.blade.php`

- [ ] **Step 1: anggota blade**

Full mapping sweep: heading/buttons, search card, form, table, dropdowns, alerts. Import-related success/badge classes → token variants. Status badge success/danger tokens.

- [ ] **Step 2: member-import blade**

`bg-white` cards→`bg-surface`, alert `bg-green-100 border-green-400 text-green-700`→tokens, `bg-red-100 border-red-400 text-red-700`→tokens, inputs→`border-border focus:border-accent focus:ring-accent`, upload button `bg-blue-600 hover:bg-blue-700`→`bg-accent hover:bg-accent-hover`.

- [ ] **Step 3: Verify + commit**

```bash
git add resources/views/components/anggota/
git commit -m "feat(anggota): dark-aware member list, import, template"
```

---

## Task 10: Peminjaman + Pengembalian

**Files:**
- Modify: `resources/views/components/peminjaman/peminjaman.blade.php`
- Modify: `resources/views/components/pengembalian/pengembalian.blade.php`

- [ ] **Step 1: peminjaman**

Full mapping sweep (search/book picker table, form, status badges success/danger/warning), alert tokens. Any `text-amber-*/bg-amber-*` → `text-warning-fg`/`bg-warning`.

- [ ] **Step 2: pengembalian**

Same sweep; overdue indicators → `bg-danger text-danger-fg` / `text-danger-fg`.

- [ ] **Step 3: Verify + commit**

```bash
git add resources/views/components/peminjaman/ resources/pengembalian/
git commit -m "feat(peminjaman, pengembalian): dark-aware borrow/return flows"
```

---

## Task 11: Laporan, Pengguna, Setting

**Files:**
- Modify: `resources/views/components/laporan` + blade, `resources/views/components/pengguna/*.blade.php`, `resources/views/components/setting/setting.blade.php`

- [ ] **Step 1: laporan**

Form cards `bg-white shadow`→`bg-surface shadow-sm`; buttons `bg-blue-*`→`bg-accent`; export buttons accent/blue-800; table headings tokens.

- [ ] **Step 2: pengguna (dm)**

Table tokens (`bg-white`→`bg-surface`, `bg-gray-50`→`bg-surface-muted`, `text-gray-*`→tokens, `border-gray-*`→`border-border`, badges).

- [ ] **Step 3: setting (`setting.blade.php`)**

`bg-white`→`bg-surface`, inputs border tokens, toggles `bg-blue-*`→`bg-accent`.

- [ ] **Step 4: commit**

```bash
git add resources/views/components/laporan resources/views/components/pengguna resources/views/components/setting
git commit -m "feat(laporan, pengguna, setting): dark-aware forms"
```

---

## Task 12: Auth + profile + welcome

**Files:**
- `resources/views/auth/*.blade.php` (login, register, forgot-password, reset-password, verify-email, confirm-password)
- `resources/views/profile/*`
- `resources/views/welcome.blade.php`

- [ ] **Step 1: auth blades**

card wrapper on guest page is threaded from `guest.blade.php` (already `bg-surface`). Inside forms: `auth-session-status` won't render dark; labels/inputs come from shared components (already done). Replace any stray `text-gray-700`→`text-text-primary`, login button uses `<x-primary-button>` (already token).

- [ ] **Step 2: welcome**

header/nav uses hardcoded colors — top nav: swap `text-gray-500`→`text-text-secondary`, `text-gray-700`→token, hero `text-gray-900`→`text-text-primary`; `text-blue-600` buttons, `bg-blue-600/700`→`accent`. `shadow` remaining—welcome has heavy custom — sweep per mapping; do not restructure.

- [ ] **Step 3: verify + commit**

```bash
git add resources/views/auth resources/views/profile resources/views/welcome.blade.php
npm run build && composer test
git commit -m "feat: dark-aware auth, profile, welcome pages"
```

---

## Task 13: Full sweep verdict + verification

**Files:** none (audit).

- [ ] **Step 1: grep for leaked color literals in app views (exclude PDF + welcome)**

Run:
```bash
cd resources/views && rg -l 'bg-white|text-gray-|border-gray-|bg-gray-50|bg-blue-|bg-green-100|bg-red-100' --include=*.blade.php . | grep -v welcome.blade.php | grep -v 'reports/pdf'
```
Expected: only `welcome.blade.php` and PDFs remain.

- [ ] **Step 2: full test suite**

```bash
composer test
```
Expected: all green.

- [ ] **Step 3: build**

```bash
npm run build
```

- [ ] **Step 4: Manual dark check**

Serve dev (`composer dev`), toggle theme; confirm nav, tables, forms, modals, badges, auth, welcome readable in both modes; no flash on reload; system-follow on fresh visit.

- [ ] **Step 5: Commit any audit fixes**

```bash
git commit -am "fix: audit leaked color literals"
```

---

## Notes

- **Gamma** (`bg-black/50`, `bg-black bg-opacity-50`) is fine in both modes.
- Any `hover:bg-gray-400` (Batal buttons) swap to `hover:bg-surface-raised`.
- `shadow` → `shadow-sm` for cards; keep `shadow-lg`/`shadow-xl` for dropdowns/modals.
- `ring-black/ring-gray-*` on dropdown borders → `ring-border`.
- Follow AGENTS.md: no `app/Livewire`, SFC pattern. Run `composer test` before each commit.
