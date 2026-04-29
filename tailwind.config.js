/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./views/**/*.php",
    "./public/**/*.php"
  ],
  theme: {
    extend: {
      keyframes: {
        verticalLoop: {
          from: { transform: "translateY(0)" },
          to: { transform: "translateY(-50%)" },
        },
      },
      animation: {
        "vertical-loop-fast": "verticalLoop 12s linear infinite",
        "vertical-loop-mid": "verticalLoop 15s linear infinite",
        "vertical-loop-slow": "verticalLoop 18s linear infinite",
      },
    },
  },
  plugins: [],
}