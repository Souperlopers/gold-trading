import { Preferences } from "@capacitor/preferences"

const KEY = "auth_token"

export const tokenStorage = {
	async get(): Promise<string | null> {
		const { value } = await Preferences.get({ key: KEY })
		return value
	},
	async set(token: string) {
		await Preferences.set({ key: KEY, value: token })
	},
	async clear() {
		await Preferences.remove({ key: KEY })
	},
}
