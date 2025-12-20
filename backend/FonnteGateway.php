<?php
class FonnteGateway {
    private $token;
    private $baseUrl = 'https://api.fonnte.com';

    public function __construct() {
        if (!defined('FONNTE_TOKEN')) {
            throw new Exception("FONNTE_TOKEN tidak ditemukan di konfigurasi.");
        }
        $this->token = FONNTE_TOKEN;
    }

    public function normalizePhoneNumber($phone) {
         if (!$phone) return null;

    // Remove everything except digits
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // 08xxxx → 628xxxx
         if (strpos($phone, '0') === 0) {
           return '62' . substr($phone, 1);
    }

    // already 62xxxx
         if (strpos($phone, '62') === 0) {
            return $phone;
    }

    return '62' . $phone;
    }

    public function sendMessage($target, $message) {
        if (!$target || empty(trim($target))) {
            error_log("Fonnte: Target nomor telepon kosong");
            return ['status' => false, 'error' => 'Target nomor kosong', 'code' => 'EMPTY_TARGET'];
        }

        if (!$message || empty(trim($message))) {
            error_log("Fonnte: Pesan kosong");
            return ['status' => false, 'error' => 'Pesan kosong', 'code' => 'EMPTY_MESSAGE'];
        }

        // Normalize phone number
        $normalizedTarget = $this->normalizePhoneNumber($target);
        if (!$normalizedTarget) {
            error_log("Fonnte: Gagal normalisasi nomor: $target");
            return ['status' => false, 'error' => 'Format nomor tidak valid', 'code' => 'INVALID_PHONE'];
        }

        error_log("TEST SEND TO: " . $normalizedTarget);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . '/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $normalizedTarget,
                'message' => $message,
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            error_log("Fonnte cURL Error: $error");
            return [
                'status' => false,
                'error' => $error,
                'code' => 'CURL_ERROR',
                'phone' => $normalizedTarget
            ];
        }

        $decoded = json_decode($response, true);
        if (!$decoded) {
            error_log("Fonnte invalid response: $response (HTTP: $httpCode)");
            return [
                'status' => false,
                'error' => 'Invalid response: ' . $response,
                'code' => 'INVALID_RESPONSE',
                'http_code' => $httpCode,
                'phone' => $normalizedTarget
            ];
        }

        if (isset($decoded['status']) && $decoded['status'] == true) {
            error_log("Fonnte message sent successfully to {$normalizedTarget}. ID: " . ($decoded['data']['id'] ?? 'N/A'));
            return [
                'status' => true,
                'message_id' => $decoded['data']['id'] ?? null,
                'phone' => $normalizedTarget
            ];
        } else {
            error_log("Fonnte API error: " . json_encode($decoded));
            return [
                'status' => false,
                'error' => $decoded['reason'] ?? 'Unknown error',
                'code' => 'API_ERROR',
                'phone' => $normalizedTarget,
                'raw_response' => $decoded
            ];
        }
    }
}
