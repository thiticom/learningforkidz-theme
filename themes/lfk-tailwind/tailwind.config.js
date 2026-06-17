module.exports = {
  content: [
    './themes/lfk-tailwind/**/*.php',
    './themes/lfk-tailwind/assets/js/**/*.js'
  ],
  theme: {
    extend: {
      colors: {
        lfk: {
          blue: '#2f78b8',
          blueDark: '#0f5fa9',
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

