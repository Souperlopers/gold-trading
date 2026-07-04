import { useAppSelector } from "@/store/hooks"
import type { User } from "@/types/Auth"

type BannerConfig = {
	message: string
	tone: "info" | "warning" | "error"
	action?: { label: string; href: string }
}

function getBannerConfig(user: User): BannerConfig | null {
	if (!user.approved_at) {
		return {
			message:
				"Your account is awaiting admin approval. You can browse, but trading is disabled.",
			tone: "info",
		}
	}

	if (!user.phone_verified_at) {
		return {
			message: "Verify your phone number to enable trading.",
			tone: "warning",
			action: { label: "Verify phone", href: "/verify-phone" },
		}
	}

	if (!user.national_id_verified_at) {
		return {
			message: "Verify your national ID to enable trading.",
			tone: "warning",
			action: { label: "Verify national ID", href: "/verify-national-id" },
		}
	}

	return null
}

const toneStyles: Record<BannerConfig["tone"], string> = {
	info: "bg-blue-50 text-blue-800 border-blue-200",
	warning: "bg-amber-50 text-amber-800 border-amber-200",
	error: "bg-red-50 text-red-800 border-red-200",
}

export function StatusBanner() {
	const user = useAppSelector((s) => s.auth.user)

	if (!user) return null

	const config = getBannerConfig(user)
	if (!config) return null

	return (
		<div
			role="status"
			className={`flex items-center justify-between gap-3 border px-4 py-3 text-sm ${toneStyles[config.tone]}`}
		>
			<span>{config.message}</span>
			{config.action && (
				<a
					href={config.action.href}
					className="shrink-0 font-medium underline underline-offset-2 hover:no-underline"
				>
					{config.action.label}
				</a>
			)}
		</div>
	)
}
