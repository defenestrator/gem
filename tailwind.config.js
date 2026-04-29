import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                green: {
                    '50':  '#EEF3ED',
                    '100': '#D4E4D0',
                    '200': '#82AC77',
                    '300': '#ADCBA5',
                    '400': '#5C8B50',
                    '500': '#3b7d2b',
                    '600': '#3e6534',
                    '700': '#29571e',
                    '800': '#183f0d',
                    '900': '#0A2602',
                },
                 orange: {
                    '100': '#fbcaaa',
                    '200': '#ffa673',
                    '300': '#f18a4b',
                    '400': '#f37a30',
                    '500': '#d4631c',
                    '600': '#ce4f00',
                    '700': '#933c06',
                    '800': '#722c00',
                    '900': '#3a1600'                 
                 }
            },
            fontFamily: {
                sans: ['Montserrat', ...defaultTheme.fontFamily.sans],
                serif: ['"Fauna One"', ...defaultTheme.fontFamily.serif]
            },
        },
    },

    plugins: [forms, typography],
}
