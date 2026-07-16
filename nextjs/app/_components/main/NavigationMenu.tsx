import Link from "next/link"

export default function NavigationMenu() {
	const navigationList = [
		{ title: "خانه", href: "" },
		{ title: "گزارشات", href: "" },
		{ title: "شارژ", href: "" },
		{ title: "حواله", href: "" },
		{ title: "تنظیمات", href: "" },
	]

	const isLastItem = (index: number) => index + 1 === navigationList.length

	return (
		<nav className="fixed bottom-0 right-0 w-full h-12 border-t border-x rounded-t-xl bg-surface border-border md:static md:border-none">
			<ul className="flex md:justify-start justify-between items-center md:gap-10 gap-0 h-full">
				{navigationList.map((item, index) => (
					<Link
						key={item.title}
						href={item.href}
						className={`${isLastItem(index) ? "" : "md:border-none border-l-2 border-border md:pl-0 sm:pl-10 pl-5"} list-none cursor-pointer text-text-primary hover:text-accent-dark`}
					>
						{item.title}
					</Link>
				))}
			</ul>
		</nav>
	)
}
