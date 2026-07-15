module.exports = {
content: [
  "./app/**/*.{js,ts,jsx,tsx,mdx}",
  "./components/**/*.{js,ts,jsx,tsx,mdx}",
],
   darkMode: "class",
  theme: {
    extend: {
       fontFamily: {
          sans: ['Figtree', ...defaultTheme.fontFamily.sans],
          vazir: ['"vazir"', "sans-serif"],
        },
    },
  },

  plugins: [],
};
