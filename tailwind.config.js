export default {
  content: [
    "./resources/**/*.blade.php", 
    "./resources/js/**/*.js",        
    "./resources/js/**/*.vue",         
    "./node_modules/flowbite/**/*.js",
  ],
  safelist: [
    'bg-green-100','bg-green-300','bg-green-400',
    'bg-yellow-100','bg-yellow-300','bg-yellow-400',
    'bg-red-100','bg-red-300','bg-red-400',
    'bg-pink-300','bg-gray-50','bg-gray-400',
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
