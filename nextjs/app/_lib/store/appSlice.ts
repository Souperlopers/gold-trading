import { createSlice } from "@reduxjs/toolkit"

const initialState: {
	isLoading: boolean
} = {
	isLoading: false
}

const appSlice = createSlice({
	name: "app",
	initialState,
	reducers: {
		setLoading: (state, action) => {
			state.isLoading = action.payload
		},
	},
})

export const { setLoading } = appSlice.actions
export default appSlice.reducer
