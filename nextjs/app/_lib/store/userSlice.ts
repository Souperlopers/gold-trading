import { createSlice } from "@reduxjs/toolkit"

export type roles = "guest" | "trader" | "admin" | "owner"

const initialState: {
	loading?: boolean
	authToken?: string
	role: string
	name?: string
	phone?: string
	national_id?: string
} = {
	role: "guest",
}

const themeSlice = createSlice({
	name: "user",
	initialState,
	reducers: {
		setUserLoading: (state, action) => {
			state.loading = action.payload
		},
		setAuthToken: (state, action) => {
			state.loading = action.payload
		},
		setUserData: (state, action) => {
			state.role = action.payload.role
			state.name = action.payload.name
			state.phone = action.payload.phone
			state.national_id = action.payload.national_id
		},
	},
})

export const { setUserData, setUserLoading, setAuthToken } = themeSlice.actions
export default themeSlice.reducer
