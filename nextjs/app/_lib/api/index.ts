import axios from "axios"

export type clientType = "web" | "mobile"
export type otpPurpose = "password_reset" | "registration"
export type queryKeys = "price" | ""
type methodType = "get" | "delete" | "post" | "put"

export type fetcherKey = {
	url: string
	method?: methodType
	headers?: object
	params?: object
	data?: object
}

export default ({
	url,
	method = "post",
	headers = {},
	params = {},
	data = {},
}: fetcherKey) =>
	axios({
		url: `http://localhost:8000/api${url}`,
		params,
		method,
		headers,
		data,
		timeout: 10_000, // 10 seconds
	}).then((res) => res.data)

export { default as useLogin } from "@/app/_lib/api/auth/login"
export { default as useRegister } from "@/app/_lib/api/auth/register"
export { default as useLogout } from "@/app/_lib/api/auth/logout"
export { default as useResetPass } from "@/app/_lib/api/auth/reset-password"
export { default as useCheckPhone } from "@/app/_lib/api/phone/check-phone"
export { default as useSendOtp } from "@/app/_lib/api/phone/send-otp"
export { default as useVerifyPhone } from "@/app/_lib/api/phone/verify-phone"
export { default as useGetUser } from "@/app/_lib/api/user/get-user"
