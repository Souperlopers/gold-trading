import fetcher, { clientType } from "@/app/_lib/api"
import { useMutation } from "@tanstack/react-query"

type Payload = {
	authToken: string
	client: clientType
}

export default () =>
	useMutation({
		mutationFn: ({ client = "web", authToken }: Payload) =>
			fetcher({
				url: "/auth/logout",
				headers: { Authorization: `Bearer ${authToken}` },
				data: { client },
			}),
	})
