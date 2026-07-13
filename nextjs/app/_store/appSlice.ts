import { createSlice } from "@reduxjs/toolkit"

export type appState = {
	user_theme: "light" | "dark" | "system"
	app_theme: "light" | "dark"
}

const initialState: appState = {
	user_theme: localStorage.theme ?? "system",
	app_theme:
		localStorage.theme ??
		(window.matchMedia("(prefers-color-scheme: dark)").matches
			? "dark"
			: "light"),
}

const appSlice = createSlice({
	name: "app",
	initialState,
	reducers: {
		setDarkMode: (state) => {
			state.user_theme = "dark"
			state.app_theme = "dark"
		},
		setLightMode: (state) => {
			state.user_theme = "light"
			state.app_theme = "light"
		},
		setSystemMode: (state) => {
			state.user_theme = "system"
			state.app_theme = window.matchMedia("(prefers-color-scheme: dark)")
				.matches
				? "dark"
				: "light"
		},
	},
})

export const { setDarkMode, setLightMode, setSystemMode } = appSlice.actions

export default appSlice.reducer
