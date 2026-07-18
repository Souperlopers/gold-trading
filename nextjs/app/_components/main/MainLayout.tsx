"use client"

import "@/app/globals.css" // TODO_Z always use absolute routing like this instead of "../../globals.css"
import Header from "@/app/_components/main/Header"
import { useGetUser } from "@/app/_lib/api"
import { useAppDispatch, useAppSelector } from "@/app/_lib/store"
import { setUserLoading } from "@/app/_lib/store/userSlice"

export default function MainLayout({
	children,
}: {
	children: React.ReactNode
}) {
	const user = useAppSelector((s) => s.user) // get user from store
	const getUser = useGetUser(user.authToken) // check user data and access from server
	
	// update user in store
	const dispatch = useAppDispatch()
	dispatch(setUserLoading(getUser.isPending))
	
	return (
		<>
			<Header />
			<main className="self-center w-full max-w-360 px-18 py-12">
				{children}
			</main>
		</>
	)
}
