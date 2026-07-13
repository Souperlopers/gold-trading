module.exports = {
content: [
  "./app/**/*.{js,ts,jsx,tsx,mdx}",
  "./components/**/*.{js,ts,jsx,tsx,mdx}",
],
   darkMode: "class",
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: "#D4A017",
          hover: "#B8860B",
          dark: "#F0C040",
          "hover-dark": "#D4A017",
        },

        secondary: {
          DEFAULT: "#1E293B",
          dark: "#FFD700",
        },

        accent: {
          DEFAULT: "#E6B422",
          dark: "#1A1408",
        },

        background: {
          DEFAULT: "#FFF8E7",
          secondary: "#FFF1CC",
          dark: "#2B210F",
          "secondary-dark": "#302616",
        },

        surface: {
          DEFAULT: "#FFFFFF",
          dark: "#302616",
        },

        border: {
          DEFAULT: "#E6C35C",
          dark: "#8B6914",
        },

        text: {
          primary: "#000000",
          secondary: "#6B4F1A",
          "primary-dark": "#FFF1CC",
          "secondary-dark": "#D4A017",
        },

        error: {
          DEFAULT: "#A31515",
          dark: "#FF6B6B",
        },

        success: {
          DEFAULT: "#2E7D32",
          dark: "#66BB6A",
        },

        chart: {
          bullish: "#4CAF50",
          bearish: "#C62828",
          "bearish-dark": "#EF5350",
        },
      },
    },
  },

  plugins: [],
};
