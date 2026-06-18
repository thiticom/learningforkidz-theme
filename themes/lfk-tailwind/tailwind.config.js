module.exports = {
  content: [
    './themes/lfk-tailwind/**/*.php',
    './themes/lfk-tailwind/assets/js/**/*.js'
  ],
  safelist: [
    'lfk-static-page-about-us',
    'lfk-static-page-refund',
    'lfk-static-page-how-to-orders',
    'lfk-static-page-privacy-policy',
    'typo'
  ],
  theme: {
    extend: {
      colors: {
        lfk: {
          blue: '#0076be',
          blueDark: '#005c99',
          yellow: '#fac735',
          gold: '#d99a35',
          sky: '#a9d9f6',
          ink: '#1f2937'
        }
      },
      fontFamily: {
        sans: ['Anuphan', 'Tahoma', 'Arial', 'sans-serif']
      },
      boxShadow: {
        lfk: '0 0 10px rgba(0, 0, 0, 0.15)'
      }
    }
  },
  plugins: []
};
