/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
        base: "#9C978D",       // warna dasar halaman (kedua dari kanan)
        nav: "#CCC7BD",        // navbar (paling kiri)
        card: "#C3B9B1",       // kontainer/card
        accent: "#1D1D1B",     // aksen utama
        text: "#030000",       // teks utama
        ink: "#1D1D1B",
        },
      },
      fontFamily: {
        ui: ["Pontano Sans", "Segoe UI", "Roboto", "Helvetica", "Arial", "sans-serif"],
        display: ["Stint Ultra Expanded", "Georgia", "serif"],
      },
      boxShadow: {
        soft: "0 8px 24px rgba(0,0,0,.06)",
      },
      borderRadius: {
        xl2: "1.25rem",
      },
    },
  },
  plugins: [],
};
