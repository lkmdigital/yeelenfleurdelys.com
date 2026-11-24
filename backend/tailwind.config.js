/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './app/Support/Fmt.php',
    './app/View/**/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/tailwind.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        // Variables CSS (canaux RGB) : couleurs du groupe par défaut, remplacées par celles de
        // l'établissement sur sa fiche et son tableau de bord (App\Support\Brand).
        primary: {
          DEFAULT: 'rgb(var(--brand) / <alpha-value>)',
          dark: 'rgb(var(--brand-dark) / <alpha-value>)',
          light: 'rgb(var(--brand-light) / <alpha-value>)',
        },
        secondary: 'rgb(var(--brand-accent) / <alpha-value>)',
        onaccent: 'rgb(var(--brand-on-accent) / <alpha-value>)',
        accent: { DEFAULT: '#0F0F0F', dark: '#060606' },
      },
      fontFamily: {
        sans: ['"Source Sans 3"', 'system-ui', 'sans-serif'],
        title: ['Montserrat', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
