import { useState, useEffect, useCallback } from "react"
import { createFileRoute, useNavigate } from "@tanstack/react-router"
import { useAppDispatch, useAppSelector } from "@/store/hooks"
import { sendPhoneOtp, verifyPhone } from "@/store/authSlice"

export const Route = createFileRoute('/auth/verify/phone')({
	component: VerifyPhonePage,
})

const OTP_LENGTH = 6
const RESEND_COOLDOWN = 120 // seconds

function VerifyPhonePage() {
	const dispatch = useAppDispatch()
	const navigate = useNavigate()
	const user = useAppSelector((s) => s.auth.user)

	const [code, setCode] = useState("")
	const [error, setError] = useState<string | null>(null)
	const [remaining, setRemaining] = useState<number | null>(null)
	const [cooldown, setCooldown] = useState(0)
	const [loading, setLoading] = useState(false)
	const [otpSent, setOtpSent] = useState(false)

	// Redirect if already verified
	useEffect(() => {
		if (user?.phone_verified_at) navigate({ to: "/", replace: true })
	}, [user, navigate])

	// Countdown timer for code expiry
	useEffect(() => {
		if (!otpSent) return
		const interval = setInterval(() => {
			setRemaining((prev) => {
				if (prev === null || prev <= 1) {
					clearInterval(interval)
					return 0
				}
				return prev - 1
			})
		}, 1000)
		return () => clearInterval(interval)
	}, [otpSent])

	// Resend cooldown
	useEffect(() => {
		if (cooldown <= 0) return
		const t = setTimeout(() => setCooldown((c) => c - 1), 1000)
		return () => clearTimeout(t)
	}, [cooldown])

	const handleSend = useCallback(async () => {
		setError(null)
		setLoading(true)
		try {
			const result = await dispatch(sendPhoneOtp()).unwrap()
			const expiresAt = new Date(result.expires_at)
			const secondsLeft = Math.floor((expiresAt.getTime() - Date.now()) / 1000)
			setRemaining(secondsLeft)
			setOtpSent(true)
			setCooldown(RESEND_COOLDOWN)
			setCode("")
		} catch (err: any) {
			setError(err?.message ?? "Failed to send code.")
		} finally {
			setLoading(false)
		}
	}, [dispatch])

	const handleVerify = async () => {
		if (code.length !== OTP_LENGTH) return
		setError(null)
		setLoading(true)
		try {
			await dispatch(verifyPhone(code)).unwrap()
			navigate({ to: "/verify-national-id", replace: true })
		} catch (err: any) {
			const data = err as { message?: string; remaining?: number }
			setError(data?.message ?? "Verification failed.")
			if (data?.remaining !== undefined) setRemaining(null) // lock UI on 0 remaining
		} finally {
			setLoading(false)
		}
	}

	const formatTime = (s: number) =>
		`${String(Math.floor(s / 60)).padStart(2, "0")}:${String(s % 60).padStart(2, "0")}`

	return (
		<div className="mx-auto max-w-sm px-4 py-12">
			<h1 className="mb-2 text-2xl font-semibold">Verify your phone</h1>
			<p className="mb-8 text-sm text-gray-500">
				We'll send a 6-digit code to{" "}
				<span className="font-medium text-gray-700">{user?.phone}</span>.
			</p>

			{!otpSent ? (
				<button
					onClick={handleSend}
					disabled={loading}
					className="w-full rounded-lg bg-black px-4 py-3 text-sm font-medium text-white disabled:opacity-50"
				>
					{loading ? "Sending…" : "Send code"}
				</button>
			) : (
				<div className="space-y-4">
					<div>
						<input
							type="text"
							inputMode="numeric"
							maxLength={OTP_LENGTH}
							value={code}
							onChange={(e) => setCode(e.target.value.replace(/\D/g, ""))}
							placeholder="______"
							className="w-full rounded-lg border px-4 py-3 text-center text-xl tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-black"
						/>
					</div>

					{remaining !== null && remaining > 0 && (
						<p className="text-center text-sm text-gray-500">
							Code expires in{" "}
							<span className="font-medium tabular-nums text-gray-700">
								{formatTime(remaining)}
							</span>
						</p>
					)}

					{remaining === 0 && (
						<p className="text-center text-sm text-red-500">
							Code expired.{" "}
							<button
								onClick={handleSend}
								disabled={cooldown > 0 || loading}
								className="font-medium underline disabled:no-underline disabled:opacity-40"
							>
								{cooldown > 0 ? `Resend in ${cooldown}s` : "Resend"}
							</button>
						</p>
					)}

					{error && (
						<p className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">
							{error}
						</p>
					)}

					<button
						onClick={handleVerify}
						disabled={code.length !== OTP_LENGTH || loading || remaining === 0}
						className="w-full rounded-lg bg-black px-4 py-3 text-sm font-medium text-white disabled:opacity-50"
					>
						{loading ? "Verifying…" : "Confirm"}
					</button>

					{remaining !== null && remaining > 0 && (
						<p className="text-center text-xs text-gray-400">
							Didn't receive it?{" "}
							<button
								onClick={handleSend}
								disabled={cooldown > 0 || loading}
								className="underline disabled:no-underline disabled:opacity-40"
							>
								{cooldown > 0 ? `Resend in ${cooldown}s` : "Resend"}
							</button>
						</p>
					)}
				</div>
			)}
		</div>
	)
}
