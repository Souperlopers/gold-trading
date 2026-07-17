"use client"

import "@/app/globals.css" // TODO_Z always use absolute routing like this instea of "../../globals.css"
import { Header } from "@/app/index"
import { useGetUser } from "@/app/_lib/api"
import { useAppDispatch, useAppSelector } from "@/app/_lib/store"
import { setUser } from "@/app/_lib/store/userSlice"
import { setLoading } from "@/app/_lib/store/appSlice"

export default function MainLayout({
	children,
}: {
	children: React.ReactNode
}) {
	const dispatch = useAppDispatch()
	const authToken = useAppSelector((s) => s.user.authToken)

	// check user data and access from server
	const getUser = useGetUser(authToken)

	// update app loading state
	dispatch(setLoading(getUser.isLoading))

	// update user in store
	if (getUser.isSuccess) {
		dispatch(setUser(getUser.data))
	}

	return (
		<>
			<Header />
			<main className="self-center w-full max-w-360 px-18 py-12">
				{children}
			</main>
		</>
	)
}
