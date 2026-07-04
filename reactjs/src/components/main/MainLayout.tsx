import Header from "@/components/main/Header"
import NavigationMenu from "@/components/main/NavigationMenu"

export default function MainLayout({
	children,
}: {
	children: React.ReactNode
}) {
	return (
		<main
			className="
				h-full
				bg-background dark:bg-background-d
				text-text-primary dark:text-text-primary-d
			"
		>
			<Header />
			<NavigationMenu />
			{children}
		</main>
	)
}
