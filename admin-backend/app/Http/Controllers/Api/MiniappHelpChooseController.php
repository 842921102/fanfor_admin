<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureDataRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * 小程序「帮忙选择」：用户自填菜名列表后本地随机选菜，结果可上报落库供运营查看。
 */
final class MiniappHelpChooseController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dishes' => ['required', 'array', 'min:2', 'max:40'],
            'dishes.*' => ['string', 'max:80'],
            'scene_id' => ['required', 'string', 'in:solo,friends,couple,family,colleagues'],
            'preferences' => ['nullable', 'array', 'max:8'],
            'preferences.*' => ['string', 'in:save_money,full_stomach,light,spicy'],
            'picked' => ['required', 'string', 'max:120'],
            'alternatives' => ['nullable', 'array', 'max:8'],
            'alternatives.*' => ['string', 'max:120'],
            'reason' => ['required', 'string', 'max:4000'],
        ]);

        $userId = (int) $request->user()->id;

        $dishes = array_values(array_filter(array_map(
            static fn (string $s): string => Str::limit(trim($s), 80, ''),
            $validated['dishes'],
        ), static fn (string $s): bool => $s !== ''));

        if (count($dishes) < 2) {
            return response()->json([
                'message' => '至少需要 2 个有效菜名。',
            ], 422);
        }

        $record = FeatureDataRecord::query()->create([
            'feature_type' => 'help_choose',
            'channel' => 'mini_program',
            'user_id' => $userId,
            'status' => 'success',
            'title' => $validated['picked'],
            'sub_type' => 'save',
            'input_payload' => [
                'dishes' => $dishes,
                'scene_id' => $validated['scene_id'],
                'preferences' => $validated['preferences'] ?? [],
            ],
            'result_payload' => [
                'picked' => $validated['picked'],
                'alternatives' => $validated['alternatives'] ?? [],
                'reason' => $validated['reason'],
            ],
            'result_summary' => Str::limit((string) $validated['reason'], 500),
            'requested_at' => now(),
            'source_ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
        ]);

        return response()->json(['data' => ['id' => $record->id]], 201);
    }
}
