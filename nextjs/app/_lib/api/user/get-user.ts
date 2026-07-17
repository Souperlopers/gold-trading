import fetcher from "@/app/_lib/api"
import { useQuery } from "@tanstack/react-query"

type Payload = {
	phone: string
}

export default (authToken?: string) =>
	useQuery({
		queryKey: ["todos", authToken],
		queryFn: () =>
			fetcher({
				url: "/user",
				method: "get",
				headers: authToken ? { Authorization: `Bearer ${authToken}` } : {},
			}),
	})
