import { createFileRoute, Outlet, Navigate } from "@tanstack/react-router"
import { useAppSelector } from "@/store/hooks"

export const Route = createFileRoute("/auth/verify")({
	component: AppLayout,
})

function AppLayout() {
	const { status: authStatus } = useAppSelector((s) => s.auth)

	if (authStatus !== "authenticated") {
		return <Navigate to="/auth/login" />
	}

	return <Outlet />
}
