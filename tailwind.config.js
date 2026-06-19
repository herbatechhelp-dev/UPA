import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                emerald: {
                    850: '#065f46',
                    950: '#022c22',
                },
            },
        },
    },

    plugins: [forms],
};
