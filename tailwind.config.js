/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'scanner-red': '#ff4a5a',
        'scanner-dark': '#0f1115',
      }
    },
  },
  plugins: [],
}