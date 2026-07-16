import { Header, Navigation } from "@/app/index"
import "./globals.css"

export default function MainLayout({
	children,
}: {
	children: React.ReactNode
}) {
	return (
		<html lang="fa">
			<body>
				<Header />
				<Navigation />
				{children}
			</body>
		</html>
	)
}
