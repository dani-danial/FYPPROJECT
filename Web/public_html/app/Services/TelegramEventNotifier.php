<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramEventNotifier
{
    public function notifyCreated(Event $event): void
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $destination = $this->resolveDestination($event);
        $chatId = $destination['chat_id'] ?? null;

        if (!$token || !$chatId) {
            Log::warning('Telegram event notification skipped: missing bot token or chat id.');
            return;
        }

        try {
            Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $this->message($event, $destination['name'] ?? 'Melaka Community'),
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Telegram event notification failed: ' . $e->getMessage());
        }
    }

    private function resolveDestination(Event $event): array
    {
        if ($event->latitude && $event->longitude) {
            return $this->nearestArea((float) $event->latitude, (float) $event->longitude);
        }

        $text = strtolower(trim(($event->location ?? '') . ' ' . ($event->state ?? '')));

        foreach (config('telegram.areas', []) as $area) {
            $name = strtolower($area['name'] ?? '');
            if (!$name || empty($area['chat_id'])) continue;

            if ($this->contains($text, $name)) {
                return $area;
            }

            if ($this->contains($name, 'bandaraya') && ($this->contains($text, 'bandaraya') || $this->contains($text, 'melaka city'))) {
                return $area;
            }

            if ($this->contains($name, 'ayer keroh') && $this->contains($text, 'ayer')) {
                return $area;
            }
        }

        return [
            'name' => 'Melaka General',
            'chat_id' => config('telegram.fallback_chat_id'),
        ];
    }

    private function nearestArea(float $latitude, float $longitude): array
    {
        $nearestArea = null;
        $nearestDistance = null;

        foreach (config('telegram.areas', []) as $area) {
            if (empty($area['chat_id']) || !isset($area['lat'], $area['lng'])) continue;

            $distance = $this->distanceKm($latitude, $longitude, (float) $area['lat'], (float) $area['lng']);
            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestArea = $area;
            }
        }

        return $nearestArea ?: [
            'name' => 'Melaka General',
            'chat_id' => config('telegram.fallback_chat_id'),
        ];
    }

    private function message(Event $event, string $areaName): string
    {
        $fee = ((float) $event->entry_fee) > 0 ? 'RM ' . number_format((float) $event->entry_fee, 2) : 'Free';
        $description = $event->description ? "\n\n📝 <b>Description:</b>\n" . htmlspecialchars($event->description) : '';
        $map = ($event->latitude && $event->longitude)
            ? "\n\n📍 <a href='https://www.google.com/maps?q={$event->latitude},{$event->longitude}'>View location on Google Maps</a>"
            : '';

        return "🏃 <b>NEW RUNNING EVENT CREATED</b>\n\n"
            . "📢 <b>Community:</b> " . htmlspecialchars($areaName) . "\n"
            . "🏁 <b>Event:</b> " . htmlspecialchars($event->title) . "\n"
            . "📅 <b>Date:</b> " . htmlspecialchars((string) $event->date) . "\n"
            . "⏰ <b>Time:</b> " . htmlspecialchars((string) $event->time) . "\n"
            . "📍 <b>Location:</b> " . htmlspecialchars((string) $event->location) . "\n"
            . "🗺️ <b>State:</b> " . htmlspecialchars((string) $event->state) . "\n"
            . "📏 <b>Distance:</b> " . htmlspecialchars((string) $event->distance_km) . " km\n"
            . "🎽 <b>Run Type:</b> " . htmlspecialchars((string) $event->run_type) . "\n"
            . "⭐ <b>Suggested Tier:</b> " . htmlspecialchars((string) $event->runner_tier) . "\n"
            . "💰 <b>Entry Fee:</b> " . $fee . "\n"
            . "👤 <b>Organizer:</b> " . htmlspecialchars((string) $event->organizer)
            . $description
            . $map;
    }

    private function distanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadiusKm = 6371;
        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);

        $a = sin($latDelta / 2) * sin($latDelta / 2)
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat))
            * sin($lngDelta / 2) * sin($lngDelta / 2);

        return $earthRadiusKm * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }
}
