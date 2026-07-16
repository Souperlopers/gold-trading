import { Logo, ThemeSwitch } from "@/app/index";
import Navigation from "@/app/_components/main/NavigationMenu";

export default function Header() {
	return (
		<header dir="rtl" className="bg-surface h-18.75 fixed w-full border-b border-x rounded-b-xl border-border flex justify-center">
			<div className="px-5 flex items-center justify-between gap-7 w-full h-full max-w-360 center">
				<Logo />
				<Navigation />
				<ThemeSwitch />
			</div>
		</header>
	)
}
