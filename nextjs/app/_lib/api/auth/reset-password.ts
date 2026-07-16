import fetcher, { clientType } from "@/app/_lib/api"
import { useMutation } from "@tanstack/react-query"

type Payload = {
	logout_others: boolean
	otp_token: string
	password: string
	password_confirmation: string
	client: clientType
}

export default () =>
	useMutation({
		mutationFn: ({ client = "web", ...otherPars }: Payload) =>
			fetcher({
				url: "/auth/reset-password",
				data: { client, otherPars },
			}),
	})
