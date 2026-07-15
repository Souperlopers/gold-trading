import { Logo, ThemeSwitch } from "@/app/index";

export default function Header() {
	return (
		<header dir="rtl"
			className="
				bg-surface h-18.75 flex justify-center fixed w-full
				border-b border-x rounded-b-xl border-border
			"
			
		>
			<div className="relative w-full p-5 flex items-center justify-between">
				<Logo />
				{/* <div className="w-17.5 h-8 bg-background border border-border rounded-full relative">
					<div className="h-6 w-6 bg-primary rounded-full absolute top-1 right-1"></div>
				</div> */}
				<ThemeSwitch />
			</div>
		</header>
	)
}
