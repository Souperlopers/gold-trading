import { Header, Navigation } from "@/app/index"
import "@/app/globals.css" // TODO_Z always use absolute routing like this instea of "../../globals.css"

export default function MainLayout({
	children,
}: {
	children: React.ReactNode
}) {
	return (
		<>
			<Header />
			{children}
		</>
	)
}
