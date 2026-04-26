<?php

namespace App\Support;

/**
 * OpenAI 兼容的图片生成（POST …/images/generations）在不同云厂商下的根路径差异。
 *
 * - OpenAI：根路径需带 /v1
 * - 火山方舟（ark.*.volces.com）：根路径需带 /api/v3
 *
 * visual.volcengineapi.com 为火山「视觉」原生网关（Action + Version + 签名），
 * 与 OpenAI 兼容的 Bearer + /images/generations 不是同一套协议。
 */
final class AiOpenAiCompatibleImage
{
    public static function normalizeRootBaseUrl(string $baseUrl, string $providerCode = ''): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');
        if ($baseUrl === '') {
            return '';
        }

        $lowerBase = strtolower($baseUrl);
        $host = strtolower((string) (parse_url($baseUrl.'/', PHP_URL_HOST) ?? ''));
        $code = strtolower(trim($providerCode));

        if ($code === 'openai' || str_contains($host, 'api.openai.com')) {
            if (! str_ends_with($lowerBase, '/v1')) {
                return $baseUrl.'/v1';
            }

            return $baseUrl;
        }

        // Only Ark OpenAI-compatible hosts need '/api/v3'.
        // visual.volcengineapi.com is a different API family.
        $isArkHost = str_contains($host, 'ark.') && str_contains($host, 'volces.com');
        if ($isArkHost && ! str_contains($lowerBase, '/api/v3')) {
            return $baseUrl.'/api/v3';
        }

        return $baseUrl;
    }

    /**
     * 视觉原生域名误配成 OpenAI 风格图片路径时返回说明文案；否则返回 null。
     */
    public static function misconfiguredVolcVisualOpenAiImageHint(string $baseUrl, string $apiPath): ?string
    {
        $host = strtolower((string) (parse_url(rtrim(trim($baseUrl), '/').'/', PHP_URL_HOST) ?? ''));
        if ($host === '') {
            return null;
        }

        $isVisualHost = $host === 'visual.volcengineapi.com'
            || str_ends_with($host, '.visual.volcengineapi.com');
        if (! $isVisualHost) {
            return null;
        }

        $p = strtolower(ltrim(trim($apiPath), '/'));
        if ($p === '') {
            return null;
        }

        if (! str_contains($p, 'images/generations')) {
            return null;
        }

        $isTosHost = str_contains($host, '.tos-') && str_contains($host, '.volces.com');
        if ($isTosHost) {
            return '当前「供应商地址」是火山对象存储域名（TOS，通常用于图片结果文件访问），不是模型推理网关。'
                .'请改为火山方舟网关：base_url=https://ark.cn-beijing.volces.com（区域按控制台为准）、'
                .'api_path=images/generations、密钥=方舟 API Key、model_code=图片接入点 ID（如 ep-xxxx）。';
        }

        return '当前「供应商地址」是 visual.volcengineapi.com（火山视觉原生网关），与 OpenAI 兼容的「图片路径」不能混用。'
            .'请到火山方舟控制台创建图片推理接入点，然后把本配置改为：'
            .'base_url=https://ark.cn-beijing.volces.com（区域按控制台为准）、'
            .'api_path=images/generations、密钥=方舟 API Key、model_code=接入点 ID。';
    }

    public static function isAzureOpenAiHost(string $baseUrl, string $providerCode = ''): bool
    {
        $host = strtolower((string) (parse_url(rtrim(trim($baseUrl), '/').'/', PHP_URL_HOST) ?? ''));
        $code = strtolower(trim($providerCode));

        return str_ends_with($host, '.openai.azure.com')
            || $host === 'openai.azure.com'
            || str_contains($code, 'azure');
    }

    public static function buildAzureOpenAiRequestUrl(string $baseUrl, string $apiPath, string $deployment): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');
        $apiPath = ltrim(trim($apiPath), '/');
        $deployment = trim($deployment);

        if ($baseUrl === '' || $apiPath === '') {
            return $baseUrl.($apiPath !== '' ? '/'.$apiPath : '');
        }

        $url = $baseUrl;
        if (! str_contains(strtolower($url), '/openai/deployments/')) {
            if (str_ends_with(strtolower($url), '/openai')) {
                $url .= '/deployments/'.rawurlencode($deployment);
            } else {
                $url .= '/openai/deployments/'.rawurlencode($deployment);
            }
        }

        $url .= '/'.$apiPath;
        $joiner = str_contains($url, '?') ? '&' : '?';
        if (! str_contains(strtolower($url), 'api-version=')) {
            $url .= $joiner.'api-version=2024-02-01';
        }

        return $url;
    }

    public static function normalizeImageSizeForModel(string $requestedSize, string $modelCode): string
    {
        $size = strtolower(trim($requestedSize));
        $model = strtolower(trim($modelCode));

        if ($size === '') {
            $size = '1024x1024';
        }

        if (! str_contains($model, 'seedream')) {
            return $size;
        }

        if (! preg_match('/^(\d{2,5})x(\d{2,5})$/', $size, $m)) {
            return '1920x1920';
        }

        $w = (int) $m[1];
        $h = (int) $m[2];
        if ($w <= 0 || $h <= 0) {
            return '1920x1920';
        }

        // Doubao Seedream requires area >= 3,686,400 pixels.
        if (($w * $h) < 3686400) {
            return '1920x1920';
        }

        return $size;
    }
}
