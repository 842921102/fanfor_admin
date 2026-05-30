<?php

namespace App\Services;

use App\Models\MiniappAnalyticsEvent;
use App\Models\User;
use App\Support\MiniappAnalyticsEventCatalog;
use Illuminate\Support\Carbon;

final class MiniappAnalyticsEventRecorder
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function record(
        string $eventName,
        ?User $user = null,
        ?string $eventValue = null,
        array $meta = [],
        ?string $clientSessionId = null,
        ?Carbon $createdAt = null,
    ): void {
        $def = MiniappAnalyticsEventCatalog::definition($eventName);
        if ($def === null) {
            return;
        }

        MiniappAnalyticsEvent::query()->create([
            'user_id' => $user !== null ? (int) $user->id : null,
            'page' => $def['page'],
            'category' => $def['category'],
            'event_name' => $eventName,
            'event_value' => $eventValue !== null && $eventValue !== '' ? mb_substr($eventValue, 0, 255) : null,
            'meta' => $meta === [] ? null : $meta,
            'client_session_id' => $clientSessionId !== null && $clientSessionId !== ''
                ? mb_substr($clientSessionId, 0, 64)
                : null,
            'created_at' => $createdAt ?? Carbon::now(),
        ]);
    }

    /**
     * @param  list<array{
     *   event_name: string,
     *   event_value?: string|null,
     *   meta?: array<string, mixed>|null,
     * }>  $events
     */
    public function recordBatch(
        array $events,
        ?User $user = null,
        ?string $clientSessionId = null,
    ): int {
        $now = Carbon::now();
        $rows = [];
        $accepted = 0;

        foreach ($events as $event) {
            $eventName = (string) ($event['event_name'] ?? '');
            $def = MiniappAnalyticsEventCatalog::definition($eventName);
            if ($def === null) {
                continue;
            }

            $eventValue = isset($event['event_value']) ? (string) $event['event_value'] : null;
            $meta = isset($event['meta']) && is_array($event['meta']) ? $event['meta'] : [];

            $rows[] = [
                'user_id' => $user !== null ? (int) $user->id : null,
                'page' => $def['page'],
                'category' => $def['category'],
                'event_name' => $eventName,
                'event_value' => $eventValue !== null && $eventValue !== '' ? mb_substr($eventValue, 0, 255) : null,
                'meta' => $meta === [] ? null : json_encode($meta, JSON_UNESCAPED_UNICODE),
                'client_session_id' => $clientSessionId !== null && $clientSessionId !== ''
                    ? mb_substr($clientSessionId, 0, 64)
                    : null,
                'created_at' => $now,
            ];
            $accepted++;
        }

        if ($rows !== []) {
            MiniappAnalyticsEvent::query()->insert($rows);
        }

        return $accepted;
    }
}
