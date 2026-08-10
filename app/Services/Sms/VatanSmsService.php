<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VatanSmsService
{
    protected string $kno;
    protected string $kulad;
    protected string $sifre;
    protected string $gonderen;
    protected string $apiUrl = 'http://panel.vatansms.com/panel/smsgonder1Npost.php';

    public function __construct(array $credentials)
    {
        $this->kno      = $credentials['kno']      ?? $credentials['username'] ?? '';
        $this->kulad    = $credentials['kulad']    ?? $credentials['username'] ?? '';
        $this->sifre    = $credentials['password'] ?? '';
        $this->gonderen = $credentials['sender']   ?? 'LATTESSA';
    }

    public function send(string $phone, string $message): array
    {
        try {
            $phone = $this->normalizePhone($phone);

            $xml = "<sms>"
                . "<kno>{$this->kno}</kno>"
                . "<kulad>{$this->kulad}</kulad>"
                . "<sifre>{$this->sifre}</sifre>"
                . "<gonderen>{$this->gonderen}</gonderen>"
                . "<mesaj>{$message}</mesaj>"
                . "<numaralar>{$phone}</numaralar>"
                . "<tur>Turkce</tur>"
                . "</sms>";

            $response = Http::timeout(15)
                ->asForm()
                ->post($this->apiUrl, ['data' => $xml]);

            $body = trim($response->body());
            $success = $response->successful() && $body === '00';

            if (!$success) {
                Log::warning('VatanSMS yaniti: ' . $body);
            }

            return [
                'success'  => $success,
                'provider' => 'vatansms',
                'response' => ['raw' => $body],
            ];

        } catch (\Exception $e) {
            Log::error('VatanSMS hatasi: ' . $e->getMessage());
            return [
                'success'  => false,
                'provider' => 'vatansms',
                'response' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function sendBulk(array $phones, string $message): array
    {
        try {
            $phones    = array_map([$this, 'normalizePhone'], $phones);
            $numaralar = implode(',', $phones);

            $xml = "<sms>"
                . "<kno>{$this->kno}</kno>"
                . "<kulad>{$this->kulad}</kulad>"
                . "<sifre>{$this->sifre}</sifre>"
                . "<gonderen>{$this->gonderen}</gonderen>"
                . "<mesaj>{$message}</mesaj>"
                . "<numaralar>{$numaralar}</numaralar>"
                . "<tur>Turkce</tur>"
                . "</sms>";

            $response = Http::timeout(30)
                ->asForm()
                ->post($this->apiUrl, ['data' => $xml]);

            $body    = trim($response->body());
            $success = $response->successful() && $body === '00';

            return [
                'success'    => $success,
                'provider'   => 'vatansms',
                'response'   => ['raw' => $body],
                'sent_count' => $success ? count($phones) : 0,
            ];

        } catch (\Exception $e) {
            Log::error('VatanSMS toplu gonderim hatasi: ' . $e->getMessage());
            return [
                'success'    => false,
                'provider'   => 'vatansms',
                'response'   => ['error' => $e->getMessage()],
                'sent_count' => 0,
            ];
        }
    }

    public function getBalance(): array
    {
        return ['success' => true, 'balance' => 0, 'response' => []];
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '90')) {
            return '0' . substr($digits, 2); // 905xx → 05xx
        }
        if (str_starts_with($digits, '0')) {
            return $digits; // 05xx olarak bırak
        }
        if (str_starts_with($digits, '5')) {
            return '0' . $digits; // 5xx → 05xx
        }

        return $digits;
    }
}
