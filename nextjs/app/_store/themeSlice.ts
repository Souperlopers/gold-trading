import { createSlice } from "@reduxjs/toolkit";

export type theme = "light" | "dark" | "system";

interface themeState {
    theme: theme
}

const initialState:themeState = {
    theme: "light"
}

const themeSlice = createSlice({
    name: "theme",
    initialState: initialState,
    reducers:{
        setTheme(state,action){
           state.theme = action.payload
        }
    }
})

export const {setTheme} = themeSlice.actions;
export default themeSlice.reducer;