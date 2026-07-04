import { createFileRoute, Navigate, useNavigate } from "@tanstack/react-router"
import { useAppSelector } from "@/store/hooks"

export const Route = createFileRoute("/")({
	component: RootRedirect,
})

function RootRedirect() {
	// const { status } = useAppSelector((s) => s.auth)
	// const navigate = useNavigate()

	// if (status === "idle" || status === "loading") return null

	return (
		<>
			{/* <div>Authentication status: {status}</div>

			<button onClick={() => navigate({ to: "/auth/login" })}>
				Login / Sign up
			</button> */}
		</>
	)
}
