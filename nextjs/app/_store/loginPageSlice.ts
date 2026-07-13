import { createSlice, createAsyncThunk } from "@reduxjs/toolkit"
import { api, ensureCsrfCookie } from "@/lib/api"
import { isNative } from "@/lib/platform"
import { tokenStorage } from "@/lib/tokenStorage"
import type { User } from "@/types/Auth"

export type loginPageState = {
	status:
		| "phone"
		| "register-otp"
		| "register-fill"
		| "login-password"
		| "login-otp"
		| "login-otp-password"
	error: string | null
}

const initialState: loginPageState = {
	status: "phone",
	error: null,
}

export const phoneAvailable = createAsyncThunk(
	"auth/phoneAvailable",
	async (payload: { phone: string }) => {
		const { data } = await api.post("/auth/check-phone", payload)
		return data.available as boolean
	},
)

export const sendPhoneOtp = createAsyncThunk("auth/sendPhoneOtp", async () => {
	const { data } = await api.post("/user/verify-phone/send")
	return data as { expires_at: string }
})

export const verifyPhone = createAsyncThunk(
	"auth/verifyPhone",
	async (code: string, { rejectWithValue }) => {
		try {
			const { data } = await api.post("/user/verify-phone", { code })
			return data.user as User
		} catch (err: any) {
			return rejectWithValue(err.response?.data)
		}
	},
)

export const verifyNationalId = createAsyncThunk(
	"auth/verifyNationalId",
	async (nationalId: string, { rejectWithValue }) => {
		try {
			const { data } = await api.post("/user/verify-national-id", {
				national_id: nationalId,
			})
			return data.user as User
		} catch (err: any) {
			return rejectWithValue(err.response?.data)
		}
	},
)

export const registerUser = createAsyncThunk(
	"auth/register",
	async (payload: {
		name: string
		phone: string
		password: string
		password_confirmation: string
	}) => {
		await ensureCsrfCookie()
		const { data } = await api.post("/register", payload)
		if (isNative() && data.token) await tokenStorage.set(data.token)
		return data.user as User
	},
)

export const loginUser = createAsyncThunk(
	"auth/login",
	async (payload: { phone: string; password: string }) => {
		await ensureCsrfCookie()
		const { data } = await api.post("/auth/login", payload)
		if (isNative() && data.token) await tokenStorage.set(data.token)
		return data.user as User
	},
)

export const fetchCurrentUser = createAsyncThunk("auth/fetchUser", async () => {
	const { data } = await api.get("/user")
	return data as User
})

export const logoutUser = createAsyncThunk("/auth/logout", async () => {
	await api.post("/logout")
	if (isNative()) await tokenStorage.clear()
})

const loginPageSlice = createSlice({
	name: "auth",
	initialState,
	reducers: {},
	extraReducers: (builder) => {
		builder
			.addCase(registerUser.pending, (state) => {
				state.status = "loading"
			})
			.addCase(registerUser.fulfilled, (state, action) => {
				state.status = "authenticated"
				state.user = action.payload
			})
			.addCase(registerUser.rejected, (state, action) => {
				state.status = "error"
				state.error = action.error.message ?? "Registration failed"
			})

			.addCase(loginUser.pending, (state) => {
				state.status = "loading"
			})
			.addCase(loginUser.fulfilled, (state, action) => {
				state.status = "authenticated"
				state.user = action.payload
			})
			.addCase(loginUser.rejected, (state, action) => {
				state.status = "error"
				state.error = action.error.message ?? "Login failed"
			})

			.addCase(fetchCurrentUser.pending, (state) => {
				state.status = "loading"
			})
			.addCase(fetchCurrentUser.fulfilled, (state, action) => {
				state.status = "authenticated"
				state.user = action.payload
			})
			.addCase(fetchCurrentUser.rejected, (state) => {
				state.status = "unauthenticated"
				state.user = null
			})

			.addCase(logoutUser.fulfilled, (state) => {
				state.status = "unauthenticated"
				state.user = null
			})
			.addCase(verifyPhone.fulfilled, (state, action) => {
				state.user = action.payload
			})
			.addCase(verifyNationalId.fulfilled, (state, action) => {
				state.user = action.payload
			})
	},
})

export default loginPageSlice.reducer
