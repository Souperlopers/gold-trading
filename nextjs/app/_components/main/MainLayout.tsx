"use client"

import "@/app/globals.css" // TODO_Z always use absolute routing like this instead of "../../globals.css"
import Header from "@/app/_components/main/Header"
import { useGetUser } from "@/app/_lib/api"
import { useAppDispatch, useAppSelector } from "@/app/_lib/store"
import { setUserLoading } from "@/app/_lib/store/userSlice"
import { useEffect } from "react"

export default function MainLayout({
	children,
}: {
	children: React.ReactNode
}) {
	const dispatch = useAppDispatch()
	const auth = useAppSelector((s) => s.auth) // get user from store
	const getUser = useGetUser(auth.token) // check user data and access from server

	// update user in store
	useEffect(() => {
		dispatch(setUserLoading(getUser.isPending))
	}, [dispatch, getUser])

	return (
		<>
			<Header />
			<main className="self-center w-full max-w-360 px-18 py-12">
				{children}
			</main>
		</>
	)
}
