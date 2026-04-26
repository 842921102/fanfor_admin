<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 历史上部分图片能力模型被写入 model_type=text，导致后台场景校验失败。
 * 凡 api_path 指向图片类接口的，统一标记为 image。
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('ai_models')
            ->where('model_type', 'text')
            ->whereNotNull('api_path')
            ->where(function ($query): void {
                $query
                    ->where('api_path', 'like', '%/images/%')
                    ->orWhere('api_path', 'like', '%images/generations%');
            })
            ->update([
                'model_type' => 'image',
                'updated_at' => $now,
            ]);

        DB::table('ai_models')
            ->where('model_type', 'text')
            ->where(function ($query): void {
                $query
                    ->where('model_name', 'like', '%图片生成%')
                    ->orWhere('model_name', 'like', '%文生图%');
            })
            ->update([
                'model_type' => 'image',
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // 无法可靠区分「本应 text 却误带 /images/」与「本应 image」，不回滚。
    }
};
