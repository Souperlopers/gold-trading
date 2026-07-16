import fetcher, { clientType } from "@/app/_lib/api"
import { useMutation } from "@tanstack/react-query"

type Payload = {
	name: string
	national_id: string
	otp_token: string
	password: string
	password_confirmation: string
	client: clientType
}

export default () =>
	useMutation({
		mutationFn: ({ client = "web", ...otherPars }: Payload) =>
			fetcher({
				url: "/auth/register",
				data: { client, otherPars },
			}),
	})
