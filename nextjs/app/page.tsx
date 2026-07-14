import Button from "./_components/ui/Button"

export default function Home() {
	return (
		<main className="flex flex-1 w-full max-w-3xl flex-col items-center justify-between py-32 px-16 bg-white dark:bg-black sm:items-start">
			<Button title="Test" />
			<button className="bg-red-500">123</button>
		</main>
	)
}
