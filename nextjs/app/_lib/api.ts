import axios from "axios"
import { isNative } from "./platform"
import { tokenStorage } from "./tokenStorage"

const baseURL = import.meta.env.VITE_API_URL as string // was process.env.NEXT_PUBLIC_API_URL

export const api = axios.create({
	baseURL,
	withCredentials: true,
})

api.interceptors.request.use(async (config) => {
	if (isNative()) {
		const token = await tokenStorage.get()
		if (token) config.headers.Authorization = `Bearer ${token}`
	}
	return config
})

api.interceptors.response.use(
	(res) => res,
	async (error) => {
		if (error.response?.status === 401) {
			if (isNative()) await tokenStorage.clear()
		}
		return Promise.reject(error)
	},
)

export async function ensureCsrfCookie() {
	if (!isNative()) {
		await axios.get(
			`${(import.meta.env.VITE_API_URL as string).replace("/api", "")}/sanctum/csrf-cookie`,
			{ withCredentials: true },
		)
	}
}
