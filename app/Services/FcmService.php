<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send a push notification to a specific device token.
     *
     * @param string $token Device FCM Token
     * @param string $title Notification Title
     * @param string $body Notification Body
     * @param array $data Additional dynamic data/actions for interactive clicks
     * @return bool
     */
    public static function sendPush($token, $title, $body, array $data = [])
    {
        if (empty($token)) {
            Log::warning('FCM Send: Token is empty.');
            return false;
        }

        try {
            $accessToken = self::getAccessToken();
            if (!$accessToken) {
                Log::error('FCM Send: Failed to retrieve Google access token.');
                return false;
            }

            $projectId = self::getProjectId();
            if (!$projectId) {
                Log::error('FCM Send: Firebase Project ID is missing.');
                return false;
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // Map data values to string format because FCM v1 data payload only accepts string values
            $stringData = [];
            foreach ($data as $key => $value) {
                $stringData[(string) $key] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;
            }

            // Standardize dynamic actions for buttons (e.g. key: "click_action", "action_buttons", etc.)
            // The mobile app can capture this payload to draw notification action buttons.
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $stringData,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default',
                            'channel_id' => 'high_importance_channel', // For Flutter local notifications
                        ]
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                                'content-available' => 1,
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("FCM Notification sent successfully to token: {$token}");
                return true;
            }

            Log::error('FCM Send Error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Get OAuth2 Access Token for Google APIs using the Service Account JSON
     */
    private static function getAccessToken()
    {
        $serviceAccountPath = config('services.firebase.service_account_path');

        if (!file_exists($serviceAccountPath)) {
            // Try fallback to root path
            $serviceAccountPath = base_path('firebase_credentials.json');
            if (!file_exists($serviceAccountPath)) {
                Log::error("FCM Service Account JSON not found at: " . config('services.firebase.service_account_path'));
                return null;
            }
        }

        try {
            $credentials = json_decode(file_get_contents($serviceAccountPath), true);
            if (!isset($credentials['private_key']) || !isset($credentials['client_email'])) {
                Log::error("FCM Credentials file format is incorrect. 'private_key' and 'client_email' are required.");
                return null;
            }

            $privateKey = $credentials['private_key'];
            $clientEmail = $credentials['client_email'];

            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claimSet = json_encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now
            ]);

            $base64UrlHeader = self::base64UrlEncode($header);
            $base64UrlClaimSet = self::base64UrlEncode($claimSet);

            $signatureInput = $base64UrlHeader . "." . $base64UrlClaimSet;
            $signature = '';

            if (!openssl_sign($signatureInput, $signature, $privateKey, 'SHA256')) {
                Log::error("FCM Token openssl_sign failed.");
                return null;
            }

            $base64UrlSignature = self::base64UrlEncode($signature);
            $jwt = $signatureInput . "." . $base64UrlSignature;

            // Request Token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            Log::error("FCM Google Token Request Failed: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("FCM Token Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper to base64UrlEncode
     */
    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Get Firebase Project ID from config or credentials file
     */
    private static function getProjectId()
    {
        $projectId = config('services.firebase.project_id');
        if ($projectId) {
            return $projectId;
        }

        $serviceAccountPath = config('services.firebase.service_account_path') ?: base_path('firebase_credentials.json');
        if (file_exists($serviceAccountPath)) {
            $credentials = json_decode(file_get_contents($serviceAccountPath), true);
            return $credentials['project_id'] ?? null;
        }

        return null;
    }
}
