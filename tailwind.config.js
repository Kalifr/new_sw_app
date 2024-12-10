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
                cocomat: ['Cocomat Pro', 'Helvetica Neue', 'Arial', ...defaultTheme.fontFamily.sans],
                futura: ['Futura LT', 'Helvetica', 'Arial', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                selina: {
                    DEFAULT: '#339966',
                    light: '#E6F0EB',
                },
                wamucii: {
                    DEFAULT: '#0E5641',
                },
                accent: {
                    yellow: '#F4D03F',
                },
            },
            letterSpacing: {
                brand: '0.16em',
            },
        },
    },

    plugins: [forms],
};
