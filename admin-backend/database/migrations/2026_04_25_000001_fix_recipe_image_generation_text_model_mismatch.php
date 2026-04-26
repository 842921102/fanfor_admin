<?php

use App\Support\AiScene;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 修复图片生成场景配置：
 * 1) 误绑文本模型会导致 /images/generations 调用失败；
 * 2) base_url_override 被配成不带 /v1 时，会命中站点首页而非 API。
 */
return new class extends Migration
{
    public function up(): void
    {
        $scene = AiScene::RecipeImageGeneration->value;
        $now = now();

        // 1) scene 绑定了非 image 模型时，改为同 provider 下的 image 模型（优先 gpt-image-1）
        $badRows = DB::table('ai_model_configs as c')
            ->join('ai_models as m', 'c.model_id', '=', 'm.id')
            ->where('c.scene_code', $scene)
            ->where('m.model_type', '!=', 'image')
            ->select('c.id', 'c.provider_id')
            ->get();

        foreach ($badRows as $row) {
            $imageModelId = DB::table('ai_models')
                ->where('provider_id', $row->provider_id)
                ->where('model_type', 'image')
                ->where('is_enabled', true)
                ->orderByRaw("CASE WHEN model_code = 'gpt-image-1' THEN 0 ELSE 1 END")
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->value('id');

            if ($imageModelId) {
                DB::table('ai_model_configs')
                    ->where('id', $row->id)
                    ->update([
                        'model_id' => $imageModelId,
                        'updated_at' => $now,
                    ]);
            }
        }

        // 2) OpenAI 兼容网关被配置成不带 /v1 时，归一化为 provider 的 base_url
        DB::table('ai_model_configs as c')
            ->join('ai_providers as p', 'c.provider_id', '=', 'p.id')
            ->where('c.scene_code', $scene)
            ->where('p.provider_code', 'openai')
            ->whereNotNull('c.base_url_override')
            ->where('c.base_url_override', 'NOT LIKE', '%/v1')
            ->update([
                'base_url_override' => null,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // 数据修复不回滚，避免再次指向错误配置。
    }
};
