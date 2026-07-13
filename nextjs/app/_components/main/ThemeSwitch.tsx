import { setDarkMode, setLightMode, setSystemMode } from "@/app/_store/appSlice"
import { useAppDispatch, useAppSelector } from "@/app/_store/hooks"
import { useEffect } from "react"
import type { MouseEventHandler } from "react"

function Button({
	text,
	onClick,
	active,
	pos = "middle",
}: {
	text: string
	onClick: MouseEventHandler
	active: boolean
	pos: "left" | "middle" | "right"
}) {
	const app_theme = useAppSelector((s) => s.app.app_theme)
	const user_theme = useAppSelector((s) => s.app.user_theme)

	// set dark theme based on localstorage or system preference
	useEffect(() => {
		const isLocalDark = localStorage.theme === "dark"
		const themeInLocal = "theme" in localStorage
		const isSysDark = window.matchMedia("(prefers-color-scheme: dark)").matches
		const isDark = isLocalDark || (!themeInLocal && isSysDark)

		document.documentElement.classList.toggle("dark", isDark)
	}, [app_theme])

	// listen to system theme changes and update the theme accordingly
	useEffect(() => {
		const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)")
		const handleThemeChange = (e: MediaQueryListEvent) => {
			if (user_theme === "system")
				document.documentElement.classList.toggle("dark", e.matches)
		}

		mediaQuery.addEventListener("change", handleThemeChange)

		return () => {
			mediaQuery.removeEventListener("change", handleThemeChange)
		}
	}, [])

	return (
		<button
			className={`
				p-1
				${
					active
						? "bg-accent dark:bg-accent-d hover:bg-accent-hover dark:hover:bg-accent-hover-d"
						: "bg-secondary dark:bg-secondary-d hover:bg-secondary-hover dark:hover:bg-secondary-hover-d"
				}
				border-y border-r ${pos === "left" && "border-l"}
				border-border dark:border-border-d
				${pos === "left" && "rounded-l-md"} ${pos === "right" && "rounded-r-md"}
				font-light
				active:ring-2 active:ring-accent active:dark:ring-accent-d
				active:text-primary active:dark:text-primary-d
				dark:text-text-primary
			 `}
			type="button"
			onClick={onClick}
		>
			{text}
		</button>
	)
}

export default function ThemeSwitch() {
	const dispatch = useAppDispatch()
	const user_theme = useAppSelector((s) => s.app.user_theme)

	const setDarkHandler = () => {
		localStorage.theme = "dark"
		dispatch(setDarkMode())
	}
	const setLightHandler = () => {
		localStorage.theme = "light"
		dispatch(setLightMode())
	}
	const setSystemHandler = () => {
		localStorage.removeItem("theme")
		dispatch(setSystemMode())
	}

	return (
		<div className="absolute left-5" role="group">
			<Button
				active={user_theme === "light"}
				pos="right"
				onClick={setLightHandler}
				text="روشن"
			/>
			<Button
				active={user_theme === "system"}
				pos="middle"
				onClick={setSystemHandler}
				text="سیستم"
			/>
			<Button
				active={user_theme === "dark"}
				pos="left"
				onClick={setDarkHandler}
				text="تاریک"
			/>
		</div>
	)
}
