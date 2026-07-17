'use client'

import { useAppSelector } from "./_lib/store"

export default function Home() {
	const user = useAppSelector((s) => s.user)

	return (
		<main className="flex flex-1 w-full max-w-3xl flex-col items-center justify-between py-32 px-16 sm:items-start">
			سلام{user.name && ` ${user.name}`}, نقش شما عبارت است از: {user.role}
		</main>
	)
}
