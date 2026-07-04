import Logo from "@/components/main/Logo"
import ThemeSwitch from "@/components/main/ThemeSwitch"

export default function Header() {
	return (
		<header
			className="
				h-[75px]
				bg-surface dark:bg-surface-d
				border-b border-x rounded-b-xl border-border dark:border-border-d
				flex justify-center
			"
		>
			<div className="relative w-7xl px-4 py-2 flex items-center justify-start">
				<Logo />
				<ThemeSwitch />
			</div>
		</header>
	)
}
