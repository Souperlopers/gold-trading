import Logo from "@/app/_components/main/Logo"
import ThemeSwitch from "@/app/_components/main/ThemeSwitch"

export default function Header() {
	return (
		<header
			className="
				bg-surface dark:bg-surface-d h-[75px] flex justify-center
				border-b border-x rounded-b-xl border-border dark:border-border-d
			"
		>
			<div className="relative w-7xl px-4 py-2 flex items-center justify-start">
				<Logo />
				<ThemeSwitch />
			</div>
		</header>
	)
}
