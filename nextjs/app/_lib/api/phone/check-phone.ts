import fetcher from "@/app/_lib/api"
import { useMutation } from "@tanstack/react-query"

type Payload = {
	phone: string
}

export default () =>
	useMutation({
		mutationFn: (payload: Payload) =>
			fetcher({
				url: "/phone",
				method: "get",
				params: payload,
			}),
	})
