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
                // Design tokens SignVerse — indigo sebagai warna brand utama
                primary: {
                    50: '#eef0ff',
                    100: '#e0e3ff',
                    DEFAULT: '#5b5ff0',
                    dark: '#4338ca',
                },
                secondary: {
                    50: '#e6f8fa',
                    DEFAULT: '#0ea5b7',
                    dark: '#0b7f8d',
                },
                surface: {
                    DEFAULT: '#f5f6fb',
                    border: '#e6e7f1',
                    muted: '#fafbff',
                },
                ink: {
                    DEFAULT: '#171533',
                    muted: '#64647e',
                    soft: '#9a9ab0',
                },
            },
            borderRadius: {
                card: '1.25rem',
            },
            boxShadow: {
                card: '0 1px 2px rgba(23, 21, 51, 0.04), 0 4px 16px -6px rgba(23, 21, 51, 0.08)',
                lift: '0 2px 4px rgba(23, 21, 51, 0.04), 0 12px 32px -8px rgba(23, 21, 51, 0.16)',
                glow: '0 8px 24px -6px rgba(91, 95, 240, 0.45)',
            },
        },
    },

    plugins: [forms],
};
