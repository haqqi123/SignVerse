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
                // Identitas visual SignVerse (dipertahankan dari project lama)
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Design tokens dari ui.py project lama
                primary: {
                    DEFAULT: '#6366f1',
                    dark: '#4f46e5',
                },
                secondary: {
                    DEFAULT: '#0ea5b7',
                },
                surface: {
                    DEFAULT: '#f7f6fd',
                    border: '#e5e4f0',
                },
                ink: {
                    DEFAULT: '#1e1b3a',
                    muted: '#6b6a86',
                },
            },
            borderRadius: {
                card: '14px',
            },
            boxShadow: {
                card: '0 1px 3px rgba(30, 27, 58, 0.04)',
            },
        },
    },

    plugins: [forms],
};
