import {Header, Navigation} from "@/app/index";

export default function MainLayout({
	children,
}: {
	children: React.ReactNode
}) {
	return (
		<main className="h-full min-h-dvh bg-background dark:bg-background-d text-text-primary dark:text-text-primary-d">
			<Header />
			<Navigation />
			{children}
		</main>
	)
}
