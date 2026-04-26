<?php

namespace App\Support;

enum AiScene: string
{
    case RecipeTextGeneration = 'recipe_text_generation';
    case RecipeImageGeneration = 'recipe_image_generation';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::RecipeTextGeneration->value => '菜谱生成模型',
            self::RecipeImageGeneration->value => '图片生成模型',
        ];
    }

    public static function modelTypeFor(string $sceneCode): string
    {
        return match ($sceneCode) {
            self::RecipeImageGeneration->value => 'image',
            default => 'text',
        };
    }

    public static function assertModelTypeMatchesScene(string $sceneCode, string $actualModelType): void
    {
        $expected = self::modelTypeFor($sceneCode);
        if ($actualModelType !== $expected) {
            $need = $expected === 'image' ? '图片（image）' : '文本（text）';
            abort(422, "该场景需要 {$need} 类模型，当前所选模型类型为：{$actualModelType}。");
        }
    }
}

