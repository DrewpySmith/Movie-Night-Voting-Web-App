import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                surface: {
                    DEFAULT: '#171719',
                    elevated: '#202838',
                    black: '#09090b',
                },
            },
            animation: {
                rise: 'rise-in 800ms cubic-bezier(0.2, 0.8, 0.2, 1) both',
                'poster-drift': 'poster-drift 38s linear infinite',
                'poster-drift-reverse': 'poster-drift-reverse 42s linear infinite',
            },
            keyframes: {
                'rise-in': {
                    from: { opacity: '0', transform: 'translateY(18px) scale(0.98)' },
                    to: { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
                'poster-drift': {
                    from: { transform: 'translateY(0) rotate(-4deg)' },
                    to: { transform: 'translateY(-50%) rotate(-4deg)' },
                },
                'poster-drift-reverse': {
                    from: { transform: 'translateY(-50%) rotate(4deg)' },
                    to: { transform: 'translateY(0) rotate(4deg)' },
                },
            },
        },
    },

    darkMode: 'class',

    plugins: [forms],
};
