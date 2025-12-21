import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import containerQueries from '@tailwindcss/container-queries';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Sora', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                twitter: {
                    DEFAULT: '#0E6F6D',
                    600: '#0B5957',
                },
                primary: '#0E6F6D',
                secondary: '#1E2B37',
                accent: '#F08B3E',
                neutral: '#4B5B70',
                'base-100': '#FBF7F0',
                'base-200': '#F2E6D6',
                'base-300': '#E5D4C1',
                'base-content': '#0F172A',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-6px)' },
                },
                heartBeat: {
                    '0%, 100%': { transform: 'scale(1)' },
                    '25%': { transform: 'scale(1.3)' },
                    '50%': { transform: 'scale(1.1)' },
                },
                wiggle: {
                    '0%, 100%': { transform: 'rotate(0deg)' },
                    '25%': { transform: 'rotate(-10deg)' },
                    '75%': { transform: 'rotate(10deg)' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-1000px 0' },
                    '100%': { backgroundPosition: '1000px 0' },
                },
            },
            animation: {
                float: 'float 6s ease-in-out infinite',
                heartBeat: 'heartBeat 0.6s ease-in-out',
                wiggle: 'wiggle 1s ease-in-out infinite',
                shimmer: 'shimmer 1.5s linear infinite',
            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                shimmer: 'linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent)',
            },
            backdropBlur: {
                xs: '2px',
            },
            boxShadow: {
                'inner-lg': 'inset 0 2px 4px 0 rgb(0 0 0 / 0.1)',
                glow: '0 0 20px rgba(11, 107, 107, 0.45)',
                'glow-sm': '0 0 10px rgba(11, 107, 107, 0.3)',
            },
            gridTemplateColumns: {
                feed: 'minmax(0, 1fr) 350px',
                layout: '275px minmax(0, 1fr) 350px',
            },
        },
    },

    plugins: [
        forms,
        typography,
        containerQueries,
        daisyui,

        function ({ addUtilities, addComponents }) {
            addUtilities({
                '.scrollbar-hide': {
                    '-ms-overflow-style': 'none',
                    'scrollbar-width': 'none',
                    '&::-webkit-scrollbar': {
                        display: 'none',
                    },
                },
                '.scrollbar-thin': {
                    'scrollbar-width': 'thin',
                },
            });

            addComponents({
                '.tweet-card': {
                    '@apply bg-base-100 border-b border-base-300 hover:bg-base-200/50 transition-colors cursor-pointer p-4': {},
                },
                '.btn-twitter': {
                    '@apply btn btn-primary border-none text-primary-content shadow-md hover:shadow-lg transition': {},
                    backgroundImage:
                        'linear-gradient(120deg, rgba(var(--hero-sky), 0.95), rgba(var(--hero-sun), 0.9), rgba(var(--hero-sky), 0.95))',
                    backgroundSize: '200% 100%',
                },
                '.btn-twitter:hover': {
                    backgroundPosition: '100% 0',
                },
            });
        },
    ],

    daisyui: {
        themes: [
            {
                light: {
                    primary: '#0E6F6D',
                    'primary-content': '#F8FAFC',

                    secondary: '#1E2B37',
                    'secondary-content': '#F8FAFC',

                    accent: '#F08B3E',
                    'accent-content': '#1F2937',

                    neutral: '#4B5B70',
                    'neutral-content': '#FFFFFF',

                    'base-100': '#FBF7F0',
                    'base-200': '#F2E6D6',
                    'base-300': '#E5D4C1',
                    'base-content': '#0F172A',

                    info: '#2AA7DE',
                    success: '#16A34A',
                    warning: '#F59E0B',
                    error: '#EF4444',

                    '--rounded-box': '1.2rem',
                    '--rounded-btn': '1.05rem',
                    '--rounded-badge': '999px',

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
