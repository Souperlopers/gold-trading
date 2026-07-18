"use client"

import Link from "next/link"
import { useAppSelector } from "@/app/_lib/store"

export default function ProfileButton() {
	const userLoading = useAppSelector((s) => s.user.loading)
	const userAuth = useAppSelector((s) => s.user.authToken)

	if (userLoading) {
		return <div className="dui-skeleton h-9 w-[109.16px] cursor-not-allowed border border-primary"></div>
	}

	return (
		<Link
			href={userAuth ? "/profile" : "/login"}
			className={`${userLoading ? "dui-skeleton" : "dui-btn dui-btn-primary"} h-9`}
		>
			{!userLoading && (
				<p className="text-center text-nowrap text-primary-content">
					{userAuth ? "حساب کاربری" : "ورود / ثبت‌نام"}
				</p>
			)}
		</Link>
	)
}
