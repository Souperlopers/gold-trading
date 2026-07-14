import Logo from "@/app/_components/main/Logo"

export default function Header() {
	return (
		<header
			className="
				bg-surface dark:bg-surface-d h-18.75 flex justify-center
				border-b border-x rounded-b-xl border-border dark:border-border-d
			"
		>
			<div className="relative w-7xl px-4 py-2 flex items-center justify-start">
				<Logo />
				themSwitcher
			</div>
		</header>
	)
}
