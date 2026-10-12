/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        navy: {
          900: '#0B1F38',
          800: '#142B4A',
          700: '#1E3A5F',
        },
        azure: {
          500: '#1687E8',
          600: '#0F6FD6',
          50: '#EAF4FF',
        },
        page: '#F5F8FC',
      },
      screens: {
        'xs': '475px',
        '3xl': '1600px',
        '4xl': '1920px'
      }
    },
  },
  plugins: [],
}
