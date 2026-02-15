<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    private ?string $appId;
    private ?string $apiKey;

    public function __construct()
    {
        $this->appId = env("ONESIGNAL_APP_ID");
        $this->apiKey = env("ONESIGNAL_REST_API_KEY");
    }

    public function isConfigured(): bool
    {
        return !empty($this->appId) && !empty($this->apiKey);
    }

    public function sendToAll(string $title, string $body, array $data = []): array
    {
        if (!$this->isConfigured()) {
            return ["success" => false, "reason" => "OneSignal not configured"];
        }

        try {
            $payload = [
                "app_id" => $this->appId,
                "included_segments" => ["All"],
                "headings" => ["en" => $title],
                "contents" => ["en" => $body],
            ];

            if (!empty($data["url"])) {
                $payload["url"] = $data["url"];
            }

            $response = Http::withHeaders([
                "Authorization" => "Basic " . $this->apiKey,
                "Content-Type" => "application/json",
            ])->post("https://onesignal.com/api/v1/notifications", $payload);

            $result = $response->json();

            return [
                "success" => $response->successful(),
                "recipients" => $result["recipients"] ?? 0,
                "id" => $result["id"] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("OneSignal error: " . $e->getMessage());
            return ["success" => false, "reason" => $e->getMessage()];
        }
    }

    public function getStats(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                "Authorization" => "Basic " . $this->apiKey,
            ])->get("https://onesignal.com/api/v1/apps/{$this->appId}");

            $data = $response->json();
            return [
                "players" => $data["players"] ?? 0,
                "messageable_players" => $data["messageable_players"] ?? 0,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}
