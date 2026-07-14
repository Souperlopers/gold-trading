import { createFileRoute, useNavigate, Navigate } from "@tanstack/react-router"
import { useState } from "react";
import { useAppDispatch,useAppSelector } from "@/app/_store/hooks";
import { loginUser } from "@/app/_store/authSlice";
import axios from "axios"

export const Route = createFileRoute("/auth/login")({
	component: LoginPage,
})

function LoginPage() {
	const dispatch = useAppDispatch()
	const navigate = useNavigate()
	const { status } = useAppSelector((s) => s.auth)

	const [phone, setPhone] = useState("")
	const [otp, setOtp] = useState("")
	const [password, setPassword] = useState("")
	const [error, setError] = useState<string | null>(null)
	const [loading, setLoading] = useState(false)

	if (status === "authenticated") return <Navigate to="/" />

	async function handleSubmit(e: React.SubmitEvent) {
		e.preventDefault()
		setLoading(true)
		setError(null)
		try {
			await dispatch(loginUser({ phone, password })).unwrap()
			navigate({ to: "/" })
		} catch (err) {
			if (axios.isAxiosError(err)) {
				setError(err.response?.data?.errors?.phone?.[0] ?? "Login failed.")
			}
		} finally {
			setLoading(false)
		}
	}

	return (
		<div className="mx-auto max-w-sm px-4 py-16">
			<h1 className="mb-8 text-2xl font-bold">Sign in</h1>
			<form onSubmit={handleSubmit} className="space-y-4">
				<input
					type="tel"
					placeholder="Phone number"
					value={phone}
					onChange={(e) => setPhone(e.target.value)}
					className="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-yellow-500"
				/>
				<input
					type="password"
					placeholder="Password"
					value={password}
					onChange={(e) => setPassword(e.target.value)}
					className="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-yellow-500"
				/>
				{error && <p className="text-sm text-red-600">{error}</p>}
				<button
					type="submit"
					disabled={loading}
					className="w-full rounded-lg bg-yellow-500 py-3 font-semibold text-white disabled:opacity-50"
				>
					{loading ? "Signing in…" : "Sign in"}
				</button>
			</form>
		</div>
	)
}
