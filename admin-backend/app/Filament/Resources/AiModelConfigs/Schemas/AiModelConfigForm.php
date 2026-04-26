<?php

namespace App\Filament\Resources\AiModelConfigs\Schemas;

use App\Models\AiModel;
use App\Models\AiProvider;
use App\Support\AiScene;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AiModelConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('scene_code')
                ->label('场景')
                ->options(AiScene::options())
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('model_id', null))
                ->native(false),
            Select::make('provider_id')
                ->label('供应商')
                ->options(fn () => AiProvider::query()->orderBy('provider_name')->pluck('provider_name', 'id'))
                ->searchable()
                ->createOptionForm([
                    TextInput::make('provider_name')
                        ->label('供应商名称')
                        ->required()
                        ->maxLength(128),
                    TextInput::make('provider_code')
                        ->label('供应商编码（可选）')
                        ->maxLength(64),
                    TextInput::make('base_url')
                        ->label('基础地址（可选）')
                        ->maxLength(512),
                ])
                ->createOptionUsing(function (array $data): int {
                    $providerCode = self::makeUniqueProviderCode($data['provider_code'] ?? null, $data['provider_name']);

                    $provider = AiProvider::query()->create([
                        'provider_name' => $data['provider_name'],
                        'provider_code' => $providerCode,
                        'provider_type' => 'multi',
                        'base_url' => $data['base_url'] ?: 'https://api.openai.com/v1',
                        'is_enabled' => true,
                    ]);

                    return (int) $provider->getKey();
                })
                                ->requiredWithout('provider_name_manual')
                ->native(false),
            TextInput::make('provider_name_manual')
                ->label('手动填写供应商')
                ->maxLength(128)
                ->requiredWithout('provider_id'),
            Select::make('model_id')
                ->label('模型')
                ->options(function (Get $get): array {
                    $scene = (string) ($get('scene_code') ?? '');
                    $query = AiModel::query()->orderBy('model_name');
                    if ($scene !== '') {
                        $query->where('model_type', AiScene::modelTypeFor($scene));
                    }

                    return $query->pluck('model_name', 'id')->all();
                })
                ->disabled(fn (Get $get): bool => blank($get('scene_code')))
                ->searchable()
                ->createOptionForm([
                    Select::make('provider_id')
                        ->label('所属供应商')
                        ->options(fn () => AiProvider::query()->orderBy('provider_name')->pluck('provider_name', 'id'))
                        ->default(fn (Get $get): ?int => $get('provider_id'))
                        ->searchable()
                        ->native(false)
                        ->required(),
                    TextInput::make('model_name')
                        ->label('模型名称')
                        ->required()
                        ->maxLength(160),
                    TextInput::make('model_code')
                        ->label('模型编码（可选）')
                        ->maxLength(96),
                ])
                ->createOptionUsing(function (array $data, $livewire): int {
                    $sceneCode = (string) (($livewire->data ?? [])['scene_code'] ?? '');
                    if ($sceneCode === '') {
                        throw ValidationException::withMessages([
                            'scene_code' => '请先在表单中选择场景，再通过此处创建模型。',
                        ]);
                    }

                    $modelType = AiScene::modelTypeFor($sceneCode);
                    $modelCode = self::makeUniqueModelCode((int) $data['provider_id'], $data['model_code'] ?? null, $data['model_name']);

                    $model = AiModel::query()->create([
                        'provider_id' => (int) $data['provider_id'],
                        'model_name' => $data['model_name'],
                        'model_code' => $modelCode,
                        'model_type' => $modelType,
                        'is_enabled' => true,
                        'is_default' => false,
                        'supports_temperature' => true,
                        'supports_timeout' => true,
                    ]);

                    return (int) $model->getKey();
                })
                                ->requiredWithout('model_name_manual')
                ->native(false),
            TextInput::make('model_name_manual')
                ->label('手动填写模型')
                ->maxLength(160)
                ->requiredWithout('model_id'),
            TextInput::make('api_key')
                ->label('接口密钥')
                ->password()
                ->revealable()
                ->maxLength(2048),
            TextInput::make('base_url_override')
                ->label('基础地址覆盖')
                ->maxLength(512),
            TextInput::make('temperature')
                ->label('温度参数')
                ->numeric()
                ->minValue(0)
                ->maxValue(2)
                ->step(0.01),
            TextInput::make('timeout_ms')
                ->label('超时时间（毫秒）')
                ->numeric()
                ->minValue(1000)
                ->maxValue(120000)
                ->step(1000),
            Textarea::make('fallback_model_codes')
                ->label('降级模型链')
                ->rows(3)
                ->placeholder("gpt-5-mini\ngpt-4o-mini"),
            Toggle::make('is_enabled')
                ->label('启用')
                ->default(true),
            Toggle::make('is_default')
                ->label('默认')
                ->default(true),
            Textarea::make('remark')
                ->label('备注')
                ->rows(3)
                ->maxLength(2000),
        ]);
    }

    private static function makeUniqueProviderCode(?string $candidate, string $fallbackName): string
    {
        $base = Str::of($candidate ?: $fallbackName)
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

    private static function makeUniqueModelCode(int $providerId, ?string $candidate, string $fallbackName): string
    {
        $base = Str::of($candidate ?: $fallbackName)
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
}
