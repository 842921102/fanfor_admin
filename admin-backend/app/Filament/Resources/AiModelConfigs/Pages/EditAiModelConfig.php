<?php

namespace App\Filament\Resources\AiModelConfigs\Pages;

use App\Filament\Resources\AiModelConfigs\AiModelConfigResource;
use App\Models\AiModel;
use App\Models\AiProvider;
use App\Support\AdminActionLogger;
use App\Support\AiOpenAiCompatibleImage;
use App\Support\AiScene;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditAiModelConfig extends EditRecord
{
    protected static string $resource = AiModelConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('删除'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->resolveProviderAndModelIds($data);

        $model = AiModel::query()->find((int) ($data['model_id'] ?? 0));
        if ($model) {
            $sceneCode = (string) $this->record->scene_code;
            $expected = AiScene::modelTypeFor($sceneCode);
            if ((string) $model->model_type !== $expected) {
                $need = $expected === 'image' ? '图片' : '文本';
                throw ValidationException::withMessages([
                    'model_id' => "该场景需要 {$need} 类模型，当前所选模型类型为：{$model->model_type}。请在「模型」中选择与场景匹配的记录，或在供应商后台将该模型的类型改为 {$expected}。",
                ]);
            }

            $this->assertImageGatewayCompatible($data, $model);
        }

        if (empty($data['api_key'])) {
            unset($data['api_key']); // 留空不覆盖原 key
        }

        $data['updated_by'] = (int) auth()->id();

        return $data;
    }

    private function resolveProviderAndModelIds(array $data): array
    {
        $manualProviderName = trim((string) ($data['provider_name_manual'] ?? ''));
        $manualModelName = trim((string) ($data['model_name_manual'] ?? ''));
        $sceneCode = (string) $this->record->scene_code;
        $expectedModelType = AiScene::modelTypeFor($sceneCode);

        if ($manualProviderName !== '') {
            $provider = AiProvider::query()->firstOrCreate(
                ['provider_name' => $manualProviderName],
                [
                    'provider_code' => $this->makeUniqueProviderCode($manualProviderName),
                    'provider_type' => 'multi',
                    'base_url' => 'https://api.openai.com/v1',
                    'is_enabled' => true,
                ],
            );
            $data['provider_id'] = (int) $provider->getKey();
        }

        if (empty($data['provider_id'])) {
            throw ValidationException::withMessages([
                'provider_id' => '请选择供应商或手动填写供应商。',
            ]);
        }

        if ($manualModelName !== '') {
            $model = AiModel::query()->firstOrCreate(
                [
                    'provider_id' => (int) $data['provider_id'],
                    'model_name' => $manualModelName,
                    'model_type' => $expectedModelType,
                ],
                [
                    'model_code' => $this->makeUniqueModelCode((int) $data['provider_id'], $manualModelName),
                    'is_enabled' => true,
                    'is_default' => false,
                    'supports_temperature' => true,
                    'supports_timeout' => true,
                ],
            );
            $data['model_id'] = (int) $model->getKey();
        }

        if (empty($data['model_id'])) {
            throw ValidationException::withMessages([
                'model_id' => '请选择模型或手动填写模型。',
            ]);
        }

        unset($data['provider_name_manual'], $data['model_name_manual']);

        return $data;
    }

    private function makeUniqueProviderCode(string $fallbackName): string
    {
        $base = Str::of($fallbackName)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        if ($base === '') {
            $base = 'provider';
        }

        $code = $base;
        $suffix = 1;
        while (AiProvider::query()->where('provider_code', $code)->exists()) {
            $code = "{$base}_{$suffix}";
            $suffix++;
        }

        return $code;
    }

    private function assertImageGatewayCompatible(array $data, AiModel $model): void
    {
        if ((string) $model->model_type !== 'image') {
            return;
        }

        $provider = AiProvider::query()->find((int) ($data['provider_id'] ?? 0));
        if (! $provider) {
            return;
        }

        $baseUrl = (string) ($data['base_url_override'] ?? $provider->base_url ?? '');
        $apiPath = (string) ($model->api_path ?: '/images/generations');
        $misHint = AiOpenAiCompatibleImage::misconfiguredVolcVisualOpenAiImageHint($baseUrl, $apiPath);
        if ($misHint === null) {
            return;
        }

        throw ValidationException::withMessages([
            'provider_id' => $misHint,
            'base_url_override' => '当前地址与图片接口协议不匹配，请改用火山方舟 方舟地址。',
        ]);
    }

    private function makeUniqueModelCode(int $providerId, string $fallbackName): string
    {
        $base = Str::of($fallbackName)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9._-]+/', '_')
            ->trim('_')
            ->value();

        if ($base === '') {
            $base = 'model';
        }

        $code = $base;
        $suffix = 1;
        while (AiModel::query()->where('provider_id', $providerId)->where('model_code', $code)->exists()) {
            $code = "{$base}_{$suffix}";
            $suffix++;
        }

        return $code;
    }

    protected function afterSave(): void
    {
        AdminActionLogger::record('ai_model_config.updated', $this->getRecord(), [
            'scene_code' => $this->getRecord()->scene_code,
            'provider_id' => $this->getRecord()->provider_id,
            'model_id' => $this->getRecord()->model_id,
        ]);
    }
}
