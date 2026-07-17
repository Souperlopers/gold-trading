import { configureStore, combineReducers } from "@reduxjs/toolkit"
import {
	useDispatch,
	useSelector,
	type TypedUseSelectorHook,
} from "react-redux"
import { persistStore, persistReducer } from "redux-persist"
import storage from "redux-persist/lib/storage"
import themeReducer from "@/app/_lib/store/themeSlice"
import userReducer from "@/app/_lib/store/userSlice"
import appReducer from "@/app/_lib/store/appSlice"

const rootReducer = combineReducers({
	theme: themeReducer,
	user: userReducer,
	app: appReducer,
})

const persistConfig = {
	key: "root",
	storage,
	whitelist: ["theme"],
}

const persistedReducer = persistReducer(persistConfig, rootReducer)

export const store = configureStore({
	reducer: persistedReducer,

	middleware: (getDefaultMiddleware) =>
		getDefaultMiddleware({
			serializableCheck: false,
		}),
})

export const persistor = persistStore(store)

export type RootState = ReturnType<typeof store.getState>
type AppDispatch = typeof store.dispatch

export const useAppDispatch: () => AppDispatch = useDispatch
export const useAppSelector: TypedUseSelectorHook<RootState> = useSelector
