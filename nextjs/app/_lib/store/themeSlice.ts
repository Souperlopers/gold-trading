import { createSlice } from "@reduxjs/toolkit"

type ThemeType = "light" | "dark" | "system"

const initialState: { theme: ThemeType } = {
	theme: "system",
}

const themeSlice = createSlice({
	name: "theme",
	initialState,
	reducers: {
		setTheme: (state, action) => {
			state.theme = action.payload
		},
	},
})

export const { setTheme } = themeSlice.actions
export default themeSlice.reducer
