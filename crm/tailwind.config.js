/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{js,jsx}"],
  theme: {
    extend: {
      colors: {
        navy:  { DEFAULT: "#0D1B2A", 2: "#162336", 3: "#1E2F45" },
        gold:  { DEFAULT: "#F5A623", d: "#D4901F" },
      },
      fontFamily: {
        sans:    ["Inter", "sans-serif"],
        heading: ["Poppins", "sans-serif"],
      },
    },
  },
  plugins: [],
}
