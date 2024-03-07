import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        'text-rose-500', 'bg-rose-100', 'hover:bg-rose-200',
        'text-sky-500', 'bg-sky-100', 'hover:bg-sky-200',
        'text-emerald-500', 'bg-emerald-100', 'hover:bg-emerald-200',
        'text-gray-500', 'bg-gray-100', 'hover:bg-gray-200',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
            },
            width: {
                'currencyWidth': '600px'
            },
        },
    },

    plugins: [forms],
};
