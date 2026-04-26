<?php

namespace App\Support;

/** 灵感帖子可见性 */
final class CirclePostVisibility
{
    public const Public = 'public';

    public const Friends = 'friends';

    public const Private = 'private';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [self::Public, self::Friends, self::Private];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Public => '公开可见',
            self::Friends => '仅互关好友可见',
            self::Private => '仅自己可见',
        ];
    }
}
