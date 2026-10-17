<?php

namespace App\Services;

class PythonServiceBridge
{
    private static $pythonUrl = 'http://127.0.0.1:8000';

    /**
     * Safely query the Python microservice on port 8000 with instant fallback.
     */
    public static function call($endpoint, $payload = [], $fallbackData = [])
    {
        $url = self::$pythonUrl . '/' . ltrim($endpoint, '/');
        $jsonPayload = json_encode($payload);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 800); // 800ms fast timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500); // 500ms connect timeout

        $response = curl_exec($ch);
        $errNo = curl_errno($ch);
        curl_close($ch);

        if ($errNo === 0 && !empty($response)) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return array_merge($data, ['python_status' => 'online']);
            }
        }

        // Failsafe Native PHP/JS Fallback when Python port 8000 is offline
        return array_merge($fallbackData, [
            'python_status' => 'offline',
            'bridge_note' => 'Python port 8000 is offline. Seamless PHP/JS native engine engaged.'
        ]);
    }

    /**
     * Check if Python AI Microservice is online on port 8000.
     */
    public static function isOnline()
    {
        $ch = curl_init(self::$pythonUrl . '/api/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 300);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 300);
        $res = curl_exec($ch);
        $err = curl_errno($ch);
        curl_close($ch);
        return ($err === 0 && !empty($res));
    }
}
