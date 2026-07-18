import Navigation from "@/app/_components/main/NavigationMenu"
import ProfileButton from "@/app/_components/ui/ProfileButton"
import Logo from "@/app/_components/ui/Logo"
import ThemeSwitch from "@/app/_components/ui/ThemeSwitch"

export default function Header() {
	return (
		<header
			dir="rtl"
			className="bg-base-200 h-18.75 sticky top-0 w-full border-b-3 border-x rounded-b-xl border-base-300 flex justify-center"
		>
			<div className="lg:px-10 md:px-7 px-5 flex items-center justify-between lg:gap-16 md:gap-7 w-full h-full max-w-360">
				<Logo />
				<Navigation />
				<div className="flex justify-between lg:gap-10 gap-5 items-center">
					<ThemeSwitch />
					<ProfileButton />
				</div>
			</div>
		</header>
	)
}
