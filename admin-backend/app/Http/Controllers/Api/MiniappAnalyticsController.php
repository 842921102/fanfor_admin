<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMiniappAnalyticsEventsRequest;
use App\Services\MiniappAnalyticsEventRecorder;
use App\Support\LaravelAccessToken;
use Illuminate\Http\JsonResponse;

final class MiniappAnalyticsController extends Controller
{
    public function __construct(
        private readonly MiniappAnalyticsEventRecorder $recorder,
    ) {}

    public function store(StoreMiniappAnalyticsEventsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = LaravelAccessToken::verifyAndResolveUser($request->bearerToken());

        /** @var list<array{event_name: string, event_value?: string|null, meta?: array<string, mixed>|null}> $events */
        $events = $validated['events'];
        $sessionId = isset($validated['client_session_id']) ? (string) $validated['client_session_id'] : null;

        $accepted = $this->recorder->recordBatch($events, $user, $sessionId);

        return response()->json([
            'data' => [
                'accepted' => $accepted,
                'received' => count($events),
            ],
        ]);
    }
}
