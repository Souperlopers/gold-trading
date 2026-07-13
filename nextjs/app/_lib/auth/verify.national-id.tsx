import { useState, useEffect } from "react"
import { useAppDispatch, useAppSelector } from "@/store/hooks"
import { verifyNationalId } from "@/store/authSlice"
import { useNavigate, createFileRoute } from "@tanstack/react-router"

export const Route = createFileRoute("/auth/verify/national-id")({
	component: VerifyNationalIdPage,
})

function VerifyNationalIdPage() {
	const dispatch = useAppDispatch()
	const navigate = useNavigate()
	const user = useAppSelector((s) => s.auth.user)

	const [nationalId, setNationalId] = useState("")
	const [error, setError] = useState<string | null>(null)
	const [loading, setLoading] = useState(false)

	// Redirect if already done
	useEffect(() => {
		if (user?.national_id_verified_at) navigate({ to: "/", replace: true })
	}, [user, navigate])

	// Guard: phone must be verified first
	useEffect(() => {
		if (user && !user.phone_verified_at)
			navigate({ to: "/auth/verify/phone", replace: true })
	}, [user, navigate])

	const handleSubmit = async () => {
		setError(null)
		setLoading(true)
		try {
			await dispatch(verifyNationalId(nationalId)).unwrap()
			navigate({ to: "/" })
		} catch (err: any) {
			setError(
				err?.message ?? "Verification failed. Check the ID and try again.",
			)
		} finally {
			setLoading(false)
		}
	}

	return (
		<div className="mx-auto max-w-sm px-4 py-12">
			<h1 className="mb-2 text-2xl font-semibold">Verify your identity</h1>
			<p className="mb-8 text-sm text-gray-500">
				Enter your national ID number. This is required before you can trade.
			</p>

			<div className="space-y-4">
				<div>
					<label
						htmlFor="national-id"
						className="mb-1.5 block text-sm font-medium"
					>
						National ID
					</label>
					<input
						id="national-id"
						type="text"
						inputMode="numeric"
						maxLength={10}
						value={nationalId}
						onChange={(e) => setNationalId(e.target.value.replace(/\D/g, ""))}
						placeholder="Enter your 10-digit national ID"
						className="w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black"
					/>
				</div>

				{error && (
					<p className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">
						{error}
					</p>
				)}

				<button
					onClick={handleSubmit}
					disabled={nationalId.length < 8 || loading}
					className="w-full rounded-lg bg-black px-4 py-3 text-sm font-medium text-white disabled:opacity-50"
				>
					{loading ? "Verifying…" : "Confirm"}
				</button>

				<p className="text-xs text-gray-400">
					Your national ID is encrypted and used only for identity verification.
					It will not be shared with third parties.
				</p>
			</div>
		</div>
	)
}
