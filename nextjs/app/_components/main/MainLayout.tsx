"use client"

import { Header, Navigation } from "@/app/index"
import "@/app/globals.css" // TODO_Z always use absolute routing like this instea of "../../globals.css"
import { useGetUser } from "@/app/_lib/api"
import { useAppDispatch, useAppSelector } from "@/app/_lib/store"
import { setUser } from "@/app/_lib/store/userSlice"

export default function MainLayout({
	children,
}: {
	children: React.ReactNode
}) {
	const dispatch = useAppDispatch()
	const authToken = useAppSelector((s) => s.user.authToken)
	const { isPending, isError, isSuccess, ...result } = useGetUser(authToken)

	if (isPending) {
		return <>loading...</>
	}

	if (isError) {
		console.log(result.error);
		return <>error</>
	}

	if (isSuccess) {
		console.log(result);
		dispatch(setUser(result.data))
		return (
			<>
				<Header />
				{children}
			</>
		)
	}
}
