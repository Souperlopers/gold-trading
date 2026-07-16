import { Header, Navigation } from "@/app/index"
import "../../globals.css"

export default function MainLayout({
	children,
}: {
	children: React.ReactNode
}) {
	return (
		<>
			<Header />
			<Navigation />
			{children}
		</>
	)
}
