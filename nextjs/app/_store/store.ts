import { configureStore } from "@reduxjs/toolkit"
import appReducer from "./appSlice"
import themeReducer from "./themeSlice";

// states
export const store = configureStore({
	reducer: {
		theme: themeReducer,
	},
})

//get state
export type RootState = ReturnType<typeof store.getState>
//change state
export type AppDispatch = typeof store.dispatch
