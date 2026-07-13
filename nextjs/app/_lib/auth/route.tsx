import {
	createFileRoute,
	Outlet,
	Navigate,
	useLocation,
} from "@tanstack/react-router"
import { useAppSelector } from "@/store/hooks"
import { StatusBanner } from "@/components/StatusBanner"

export const Route = createFileRoute("/auth")({
	component: AppLayout,
})

function AppLayout() {
	const { status: authStatus } = useAppSelector((s) => s.auth)
	const location = useLocation()

	if (authStatus === "idle" || authStatus === "loading") {
		return (
			<div className="flex h-screen items-center justify-center text-gray-400">
				Loading…
			</div>
		)
	}

	return (
		<div className="min-h-screen bg-gray-50">
			<StatusBanner />
			<main className="mx-auto max-w-2xl px-4 py-8">
				<Outlet />
			</main>
		</div>
	)
}
