export default {
  content: [
    "./resources/**/*.blade.php", 
    "./resources/js/**/*.js",        
    "./resources/js/**/*.vue",         
    "./node_modules/flowbite/**/*.js",
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
