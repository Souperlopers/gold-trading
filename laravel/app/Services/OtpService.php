<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * @return array{success:bool,body:?mixed}
     * - null: Network timeout or connection error (no response at all)
     * - success: bool - true if OTP was sent, false if API returned an error
     * - response: body of the response
     */
    public function __invoke(string $phone, string $operation, string $code): array
    {
        try {
            $response = Http
                ::withOptions([
                    'verify' => !config('app.debug')
                ])
                ->timeout(config('services.otp.timeout'))
                ->connectTimeout(config('services.otp.connection_timeout'))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'text/plain',
                    'X-Api-Key' => config('services.otp.api_key'),
                ])
                ->post(config('services.otp.url'), [
                    "mobile" => (string) substr($phone, 1), // remove first 0
                    "templateId" => (int) (config('app.debug')
                        ? '123456'
                        : config('services.otp.template_id')),
                    "parameters" => [
                        [
                            "name" => "OPERATION",
                            "value" => (string) $operation,
                        ],
                        [
                            "name" => "CODE",
                            "value" => (string) $code,
                        ],
                        [
                            "name" => "TIME",
                            "value" => (string) config('services.otp.expiry'),
                        ],
                    ]
                ]);



            $body = $response->json();

            // if the API returns a 4xx or 5xx response, or any status other than 1, treat it as failure
            if (!$response->successful() || $body['status'] != "1") {
                Log::warning(Lang::get('auth.otp.send.error'), [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $body,
                ]);
                return ['success' => false, 'body' => $body];
            }

            return ['success' => true, 'body' => $body];
        } catch (ConnectionException | RequestException $e) {
            Log::warning(Lang::get('auth.otp.send.timeout'), [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return ['success' => false];
        }
    }
}
