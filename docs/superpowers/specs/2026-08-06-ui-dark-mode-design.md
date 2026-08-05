# E-Library UI Redesign + Dark Mode

## Goal

Redesign all app UI using a light/dark theme system, keeping the current blue accent and Breeze/Tailwind structure. Introduce a robust, token-driven theming approach with system-follow + manual toggle persisted in localStorage.

## Decisions (from user)

- **Accent**: keep blue (`bg-blue-600` family).
- **Mode selection**: follow system `prefers-color-scheme` by default; manual toggle overrides and persists in `localStorage`.
- **Scope**: all app pages — layouts, nav, dashboard, 13 CRUD blades, profile, auth, welcome. PDF report views untouched (print-only).

## Approach

**Approach 1 — CSS variable tokens + Tailwind class dark mode.**

- `darkMode: 'class'` in `tailwind.config.js`.
- Semantic colors driven by CSS variables in `app.css`, light defaults + `.dark` overrides, exposed as Tailwind utility colors.

Semantic tokens defined as CSS custom properties in `resources/css/app.css`, with light defaults and `.dark` overrides.

### Tokens

```css
:root {
  --color-surface: #ffffff;         /* cards, modals, table bg */
  --color-surface-muted: #f3f4f6;   /* inputs, muted blocks, table head */
  --color-text-primary: #111827;    /* headings, strong text */
  --color-text-secondary: #6b7280;  /* labels, helper text */
  --color-border: #e5e7eb;
  --color-accent: #2563eb;          /* blue-600 */
  --color-accent-hover: #1d4ed8;    /* blue-700 */
  --color-accent-soft: #eff6ff;     /* blue-50 tint */
  --color-success: #dcfce7;         /* green-100 (badge bg) */
  --color-success-fg: #166534;      /* green-800 */
  --color-danger: #fee2e2;          /* red-100 (badge bg) */
  --color-danger-fg: #991b1b;       /* red-800 */
  --color-danger-solid: #dc2626;    /* red-600 solid */
  --color-warning: #fef3c7;         /* amber-100 */
  --color-warning-fg: #92400e;      /* amber-800 */
  --color-body: #f3f4f6;            /* page background (gray-100) */
}

.dark {
  --color-surface: #1f2937;         /* gray-800 */
  --color-surface-raised: #111827;  /* gray-900 modals, raised */
  --color-surface-muted: #374151;   /* gray-700 inputs/table head */
  --color-text-primary: #f9fafb;    /* gray-50 */
  --color-text-secondary: #d1d5db;  /* gray-300 */
  --color-border: #374151;          /* gray-700 */
  --color-accent: #3b82f6;          /* blue-500 */
  --color-accent-hover: #60a5fa;    /* blue-400 */
  --color-accent-soft: rgba(59,130,246,0.15);
  --color-success: rgba(34,197,94,0.15);
  --color-success-fg: #86efac;      /* green-300 */
  --color-danger: rgba(239,68,68,0.15);
  --color-danger-fg: #fca5a5;       /* red-300 */
  --color-warning: rgba(245,158,11,0.15);
  --color-warning-fg: #fcd34d;      /* amber-300 */
  --color-body: #111827;
}
```

### Tailwind token mapping

Expose tokens as utility colors in `tailwind.config.js` so blades use readable names:

```js
theme: {
  extend: {
    colors: {
      surface: 'var(--color-surface)',
      'surface-raised': 'var(--color-surface-raised)',
      'surface-muted': 'var(--color-surface-muted)',
      'text-primary': 'var(--color-text-primary)',
      'text-secondary': 'var(--color-text-secondary)',
      border: 'var(--color-border)',
      accent: {
        DEFAULT: 'var(--color-accent)',
        hover: 'var(--color-accent-hover)',
        soft: 'var(--color-accent-soft)',
      },
      success: { DEFAULT: 'var(--color-success)', fg: 'var(--color-success-fg)' },
      danger: { DEFAULT: 'var(--color-danger)', fg: 'var(--color-danger-fg)', solid: 'var(--color-danger-solid)' },
      warning: { DEFAULT: 'var(--color-warning)', fg: 'var(--color-warning-fg)' },
      body: 'var(--color-body)',
    },
    fontFamily: { sans: ['Figtree', ...defaultTheme.fontFamily.sans] },
  },
}
```

This keeps blades readable (`bg-accent`, `bg-surface`, `text-text-secondary`, `border-border`) and automatically theme-aware — no `dark:` variants scattered everywhere.

## Theme toggle mechanism

1. **Anti-flash inline script** in `<head>` of `app.blade.php` and `guest.blade.php`:
   - Read `localStorage.theme`. If `'light'`/`'dark'`, apply.
   - Else resolve `prefers-color-scheme` (`'dark'` if `dark`).
   - Add/remove `dark` class on `document.documentElement` before paint.
2. **Toggle button** (Alpine `data` in navigation) with sun/moon icon. On click: flip current, persist to localStorage, toggle class. Two-state manual override plus system default. Button icon reflects stored/system state. System default applies on first visit (when no stored override).

Implementation detail: store explicit `'light' | 'dark' | null` (null = follow system). Button toggles to the other explicit value. This keeps behavior predictable.

## File changes

### Config / assets
- `tailwind.config.js` — add `darkMode:'class'`, token colors, keep Figtree.
- `resources/css/app.css` — token CSS vars, color-scheme per mode, base surface/text defaults, reduced-motion safety, transition for theme switch.

### Layouts
- `layouts/app.blade.php` — anti-flash script; `bg-body`; nav include; header dark-aware; theme toggle available.
- `layouts/guest.blade.php` — anti-flash script; `bg-body`; card `bg-surface` dark-aware.
- `layouts/navigation.blade.php` — restyle `bg-white`→`bg-surface`? (nav on body; use `bg-surface`), text/border tokens, add theme toggle button beside user dropdown, hamburger dark-aware.

### Shared components (dark-aware)
- `primary-button`, `secondary-button`, `danger-button` — token surfaces/text.
- `nav-link`, `responsive-nav-link` — active `border-accent`, text tokens, dark-aware.
- `dropdown`, `dropdown-link`, `modal`, `text-input`, `input-label`, `input-error`, `application-logo`, `auth-session-status` — surface + text tokens.
- New: `components/theme-toggle.blade.php` — Alpine sun/moon toggle.

### Pages / Livewire SFCs
- `dashboard.blade.php` + `components/dashboard/dashboard.blade.php` — cards, chart bars (`bg-accent`), shortcuts, stat colors.
- `buku/buku.blade.php` — search/filter card, form, detail modal, table, badges, pagination.
- `kategori/kategori.php` + blade
- `rak/rak` + blade
- `anggota/anggota` + blade, `anggota/member-import` + blade
- `peminjaman/peminjaman` + blade
- `pengembalian/pengembalian` + blade
- `laporan/laporan` + blade
- `pengguna/pengguna` + blade
- `setting/setting` + blade
- `profile/edit` + partials
- `auth/login`, `register`, `forgot-password`, `reset-password`, `verify-email`, `confirm-password`
- `welcome.blade.php`

Each: swap hardcoded color classes to semantic tokens + `bg-surface`, `text-text-*`, `border-border`, token badges/buttons. Livewire components untouched in logic; Blade/class edits only.

## What we are NOT doing

- No layout restructure (no sidebar, keep Breeze top nav).
- No mobile nav rework.
- No new npm deps.
- No changes to app/ logic or behavior.
- PDF report blades (`reports/pdf/*`) untouched.
- No per-user DB theme persistence (localStorage only).

## Testing

- `composer test` (config:clear + full suite) — must stay green.
- `vendor/bin/pint` — formatting.
- Manual: toggle light↔dark; verify nav, cards, tables, forms, modals, badges, auth, welcome in both modes; verify no flash on reload; verify system-follow on fresh visit; reduced-motion respected.