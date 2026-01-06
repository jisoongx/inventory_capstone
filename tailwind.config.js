export default {
  content: [
    "./resources/**/*.blade.php", 
    "./resources/js/**/*.js",        
    "./resources/js/**/*.vue",         
    "./node_modules/flowbite/**/*.js",
  ],
  safelist: [
    {
      pattern: /bg-(red|blue|green|yellow|purple|pink|indigo|orange|teal|cyan|lime|emerald|violet|fuchsia|rose|sky|amber|slate)-500/,
    },
    
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'sans-serif'],
      },
    },
  },
  corePlugins: {
    preflight: true, 
  },
  plugins: [
    require('flowbite/plugin')
  ],
}
