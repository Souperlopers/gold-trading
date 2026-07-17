import Link from "next/link"

export default function NavigationMenu() {
	const navigationList = [
		{ title: "خانه", href: "" },
		{ separator: true },
		{ title: "گزارشات", href: "" },
		{ separator: true },
		{ title: "شارژ", href: "" },
		{ separator: true },
		{ title: "حواله", href: "" },
		{ separator: true },
		{ title: "تنظیمات", href: "" },
	]

	return (
		<nav className="fixed bottom-0 right-0 w-full h-12 border-t border-x rounded-t-xl bg-surface border-border md:static md:border-none">
			<ul className="flex justify-around md:justify-start md:justify-between items-center md:gap-10 gap-0 h-full">
				{navigationList.map((item, index) =>
					item.separator ? (
						<div
							key={index}
							className="md:border-none h-2/3 border-border border-l-2"
						></div>
					) : (
						<li key={index}>
							<Link href={item.href} className="list-none text-text-primary">
								{item.title}
							</Link>
						</li>
					),
				)}
			</ul>
		</nav>
	)
}
