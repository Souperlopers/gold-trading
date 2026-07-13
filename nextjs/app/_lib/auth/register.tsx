import { createFileRoute, useNavigate, Navigate } from "@tanstack/react-router"
import { useState } from "react"
import { useAppDispatch, useAppSelector } from "@/store/hooks"
import { registerUser } from "@/store/authSlice"
import axios from "axios"

export const Route = createFileRoute("/auth/register")({
	component: RegisterPage,
})

function RegisterPage() {
	const { status } = useAppSelector((s) => s.auth)
	if (status === "authenticated") return <Navigate to="/" />

	const dispatch = useAppDispatch()
	const navigate = useNavigate()
	const [form, setForm] = useState({
		name: "",
		phone: "",
		national_id: "",
		password: "",
		password_confirmation: "",
	})
	const [errors, setErrors] = useState<Record<string, string>>({})
	const [loading, setLoading] = useState(false)

	function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
		setForm((f) => ({ ...f, [e.target.name]: e.target.value }))
	}

	async function handleSubmit(e: React.FormEvent) {
		e.preventDefault()
		setLoading(true)
		setErrors({})
		try {
			await dispatch(registerUser(form)).unwrap()
			navigate({ to: "/dashboard" })
		} catch (err) {
			if (axios.isAxiosError(err)) {
				setErrors(
					err.response?.data?.errors ?? { general: "Registration failed." },
				)
			}
		} finally {
			setLoading(false)
		}
	}

	return (
		<div className="mx-auto max-w-sm px-4 py-16">
			<h1 className="mb-8 text-2xl font-bold">Create account</h1>
			<form onSubmit={handleSubmit} className="space-y-4">
				{(["name", "phone", "password", "password_confirmation"] as const).map(
					(field) => (
						<div key={field}>
							<input
								name={field}
								type={
									field.includes("password")
										? "password"
										: field === "phone"
											? "number"
											: "text"
								}
								placeholder={
									field === "password_confirmation"
										? "Confirm password"
										: field.charAt(0).toUpperCase() + field.slice(1)
								}
								value={form[field]}
								onChange={handleChange}
								className="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-yellow-500"
							/>
							{errors[field] && (
								<p className="mt-1 text-xs text-red-600">{errors[field]}</p>
							)}
						</div>
					),
				)}
				{errors.general && (
					<p className="text-sm text-red-600">{errors.general}</p>
				)}
				<button
					type="submit"
					disabled={loading}
					className="w-full rounded-lg bg-yellow-500 py-3 font-semibold text-white disabled:opacity-50"
				>
					{loading ? "Creating account…" : "Create account"}
				</button>
			</form>
		</div>
	)
}
