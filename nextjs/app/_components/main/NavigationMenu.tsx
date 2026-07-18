import Link from "next/link"

export default function NavigationMenu() {
	const navigationList = [
		{ title: "خانه", href: "/" },
		{ separator: true, href: "/" },
		{ title: "گزارشات", href: "/" },
		{ separator: true, href: "/" },
		{ title: "شارژ", href: "/" },
		{ separator: true, href: "/" },
		{ title: "حواله", href: "/" },
		{ separator: true, href: "/" },
		{ title: "تنظیمات", href: "/" },
	]

	return (
		<nav className={`fixed bottom-0 right-0 w-full h-16 border-t-3 border-x rounded-t-xl bg-base-200 border-base-300 md:static md:border-none`}>
			<ul className={`flex justify-around md:justify-start items-center lg:gap-10 md:gap-5 gap-0 h-full px-5 md:px-0`}>
				{navigationList.map((item, index) =>
					item.separator ? (
						<div
							key={index}
							className={`md:hidden h-2/3 border-accent border-3 rounded-full`}
						></div>
					) : (
						<li key={index}>
							<Link href={item.href} className={`list-none transition-colors duration-300 hover:text-accent active:text-accent`}>
								{item.title}
							</Link>
						</li>
					),
				)}
			</ul>
		</nav>
	)
}
