import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
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
        },
    },

    plugins: [forms, daisyui],

    daisyui: {
        themes: [
            {
                light: {
                    primary: '#1D9BF0',
                    'primary-content': '#ffffff',

                    secondary: '#111827',
                    'secondary-content': '#ffffff',

                    accent: '#14b8a6',
                    'accent-content': '#06201c',

                    neutral: '#1f2937',
                    'neutral-content': '#ffffff',

                    'base-100': '#ffffff',
                    'base-200': '#f6f8fb',
                    'base-300': '#e6eaf0',
                    'base-content': '#0f172a',

                    info: '#38bdf8',
                    success: '#22c55e',
                    warning: '#f59e0b',
                    error: '#ef4444',

                    '--rounded-box': '1rem',
                    '--rounded-btn': '0.85rem',
                    '--rounded-badge': '1rem',

                    '--border-btn': '1px',
                    '--border-input': '1px',

                    '--btn-text-case': 'none',
                    '--btn-focus-scale': '0.98',

                    '--animation-btn': '0.15s',
                    '--animation-input': '0.15s',
                },
            },
        ],
        darkTheme: 'light',
    },
};
