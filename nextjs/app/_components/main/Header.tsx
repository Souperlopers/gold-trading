import { Logo, ThemeSwitch } from "@/app/index";
import Navigation from "@/app/_components/main/NavigationMenu";

export default function Header() {
	return (
		<header dir="rtl" className="bg-base-200 h-18.75 sticky top-0 w-full border-b-3 border-x rounded-b-xl border-base-300 flex justify-center">
			<div className="px-10 flex items-center justify-between gap-16 w-full h-full max-w-360">
				<Logo />
				<Navigation />
				<ThemeSwitch />
			</div>
		</header>
	)
}
