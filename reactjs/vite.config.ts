import { defineConfig } from "vite"
import react from "@vitejs/plugin-react"
import tanstackRouter from "@tanstack/router-plugin/vite"
import { VitePWA } from "vite-plugin-pwa"
import path from "path"
import tailwindcss from "@tailwindcss/vite"

export default defineConfig({
	plugins: [
		tanstackRouter(),
		tailwindcss(),
		react(),
		VitePWA({
			registerType: "autoUpdate",
			manifest: {
				name: "Atlas Gold Trading App",
				short_name: "Gold",
				theme_color: "#EAB308",
				icons: [
					{ src: "/icon-192.png", sizes: "192x192", type: "image/png" },
					{ src: "/icon-512.png", sizes: "512x512", type: "image/png" },
				],
			},
		}),
	],
	resolve: {
		alias: { "@": path.resolve(__dirname, "src") },
	},
})
