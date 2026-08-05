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
                    solid: 'rgb(var(--success-solid) / <alpha-value>)',
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
