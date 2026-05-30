<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $page
 * @property string $category
 * @property string $event_name
 * @property string|null $event_value
 * @property array<string, mixed>|null $meta
 * @property string|null $client_session_id
 * @property Carbon $created_at
 */
class MiniappAnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'page',
        'category',
        'event_name',
        'event_value',
        'meta',
        'client_session_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
