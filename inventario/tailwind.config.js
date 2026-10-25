import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Inter"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Azul de marca: azul real para acciones, marino profundo para acentos oscuros
                brand: {
                    50: '#F0F4FF',
                    100: '#E1E9FE',
                    200: '#C3D2FD',
                    300: '#97B0FA',
                    400: '#6485F4',
                    500: '#3F62EB',
                    600: '#2747D6',
                    700: '#1F38B0',
                    800: '#1D318C',
                    900: '#1B2C6E',
                    950: '#0B1B3F',
                },
                // Neutros fríos con un leve tinte azul marino
                ink: {
                    50: '#F7F8FB',
                    100: '#EFF1F6',
                    200: '#E3E7EF',
                    300: '#CBD2DE',
                    400: '#98A2B5',
                    500: '#667085',
                    600: '#4A5468',
                    700: '#343D50',
                    800: '#1F2739',
                    900: '#121A2C',
                    950: '#0B1220',
                },
            },
            boxShadow: {
                xs: '0 1px 2px 0 rgb(18 26 44 / 0.05)',
                card: '0 1px 2px 0 rgb(18 26 44 / 0.04), 0 1px 3px 0 rgb(18 26 44 / 0.04)',
                elevated: '0 12px 32px -12px rgb(18 26 44 / 0.18), 0 2px 6px -2px rgb(18 26 44 / 0.06)',
                'brand-glow': '0 1px 2px 0 rgb(31 56 176 / 0.30), inset 0 1px 0 0 rgb(255 255 255 / 0.14)',
            },
            letterSpacing: {
                tightest: '-0.03em',
            },
        },
    },

    plugins: [forms],
};
