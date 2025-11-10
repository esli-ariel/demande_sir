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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Vos couleurs personnalisées basées sur le logo SIR
                'sir-green': '#2e8b57',
                'sir-orange': '#ff8c00',
            },
        },

    plugins: [forms],
    }
    
}
// tailwind.config.js
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  safelist: [
    {
      pattern: /bg-(red|green|orange|blue|gray|yellow|indigo|teal|pink|blue|purple)-(100|600|700)/,
    },
    {
      pattern: /border-(red|green|orange|blue|gray|yellow|indigo|teal|pink|blue|purple)-(600)/,
    },
    {
      pattern: /text-(red|green|orange|blue|gray|yellow|indigo|teal|pink|blue|purple)-(700)/,
    },
  ],
};
