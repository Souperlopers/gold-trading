import { createFileRoute, useNavigate, Navigate } from "@tanstack/react-router"
import { useState } from "react"
import { useAppDispatch, useAppSelector } from "@/store/hooks"
import { phoneAvailable } from "@/store/authSlice"
import axios from "axios"

export const Route = createFileRoute("/login")({
	component: LoginPage,
})

function LoginPage() {
	const { status } = useAppSelector((s) => s.auth)
	if (status === "authenticated") return <Navigate to="/" />

	const dispatch = useAppDispatch()
	const navigate = useNavigate()

	const [step, setStep] = useState("phone")
	const [accountExist, setAccountExist] = useState()

	const [phone, setPhone] = useState("")
	const [password, setPassword] = useState("")
	const [name, setName] = useState("")
	const [nationalId, setNationalId] = useState("")

	async function handleSubmit(e: React.SubmitEvent) {
		e.preventDefault()

		switch (step) {
			case "phone":
				try {
					const available = await dispatch(phoneAvailable({ phone })).unwrap()
					setAccountExist(!available)
					setStep(available ? "otp" : "login")
				} catch (err) {
					if (axios.isAxiosError(err)) {
						setError(err.response?.data?.errors?.phone?.[0] ?? "Login failed.")
					}
				} finally {
					setLoading(false)
				}
				break

			case "register-otp":
				break

			case "register-fill":
				break

			case "phone":
				break

			default:
				break
		}
	}

	return (
		<div className="mx-auto max-w-sm px-4 py-16">
			<h1 className="mb-8 text-2xl font-bold">Sign in</h1>
			<form onSubmit={handleSubmit} className="space-y-4">
				{step === "phone" && (
					<input
						type="tel"
						placeholder="Phone number"
						value={phone}
						onChange={(e) => setPhone(e.target.value)}
						className="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-yellow-500"
					/>
				)}

				{step === "phone" && (
					<input
						type="password"
						placeholder="Password"
						value={password}
						onChange={(e) => setPassword(e.target.value)}
						className="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-yellow-500"
					/>
				)}
				{step === "phone" && 1}
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
