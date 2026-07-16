import fetcher, { otpPurpose } from "@/app/_lib/api"
import { useMutation } from "@tanstack/react-query"

type Payload = {
	phone: string
	purpose: otpPurpose
}

export default () =>
	useMutation({
		mutationFn: (payload: Payload) =>
			fetcher({
				url: "/phone/send-otp",
				data: payload,
			}),
	})
