import { createRootRoute, Outlet } from "@tanstack/react-router"
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools"
import { Provider } from "react-redux"
import { store } from "@/store/store"

import MainLayout from "@/components/main/MainLayout"

export const Route = createRootRoute({
	component: RootRoute,
})

function RootRoute() {
	return (
		<Provider store={store}>
			<MainLayout>
				<Outlet />
			</MainLayout>
			{import.meta.env.DEV && <TanStackRouterDevtools />}
		</Provider>
	)
}
