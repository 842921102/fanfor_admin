<?php

namespace App\Services;

use App\Models\CircleComment;
use App\Models\CirclePost;
use App\Models\CirclePostCollection;
use App\Models\CirclePostLike;
use App\Models\Product;
use App\Models\User;
use App\Support\CircleCommentStatus;
use App\Support\CirclePostStatus;
use App\Support\CirclePostVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CircleService
{
    private function hasVisibilityColumn(): bool
    {
        return Schema::hasColumn('circle_posts', 'visibility');
    }

    private function hasProductsTable(): bool
    {
        return Schema::hasTable('products');
    }

    private function hasUserFollowsTable(): bool
    {
        return Schema::hasTable('user_follows');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{items: array<int, array<string, mixed>>, has_more: bool}
     */
    public function paginateForPublic(array $validated, ?User $viewer): array
    {
        $tab = (string) ($validated['tab'] ?? 'latest');
        $page = max(1, (int) ($validated['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($validated['per_page'] ?? 15)));
        $keyword = isset($validated['keyword']) ? trim((string) $validated['keyword']) : '';

        $query = CirclePost::query()
            ->publishedVisible()
            ->with('user');

        $this->applyVisibilityForViewer($query, $viewer);

        if ($this->hasProductsTable()) {
            $query->with('relatedProduct');
        }

        if ($keyword !== '') {
            $kw = '%'.$keyword.'%';
            $query->where(function (Builder $q) use ($kw): void {
                $q->where('title', 'like', $kw)
                    ->orWhere('content', 'like', $kw)
                    ->orWhere('topic', 'like', $kw)
                    ->orWhereHas('user', fn (Builder $uq) => $uq->where('name', 'like', $kw));
            });
        }

        if ($tab === 'recommend') {
            $query->orderByDesc('is_pinned')
                ->orderByDesc('is_recommended')
                ->orderByDesc('published_at');
        } elseif ($tab === 'ai_generated') {
            $query->where('source_type', 'ai_generated')->orderByDesc('published_at');
        } elseif ($tab === 'user_uploaded') {
            $query->where('source_type', 'user_uploaded')->orderByDesc('published_at');
        } elseif ($tab === 'following') {
            if (! $viewer || ! $this->hasUserFollowsTable()) {
                return ['items' => [], 'has_more' => false];
            }
            $query->whereIn('user_id', function ($sub) use ($viewer): void {
                $sub->select('followee_id')
                    ->from('user_follows')
                    ->where('follower_id', $viewer->id);
            })->orderByDesc('published_at');
        } else {
            $query->orderByDesc('published_at');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $viewerId = $viewer?->id;

        $items = $paginator->getCollection()
            ->map(fn (CirclePost $p) => $this->postToApiArray($p, $viewerId))
            ->all();

        return [
            'items' => $items,
            'has_more' => $paginator->hasMorePages(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function postToApiArray(CirclePost $post, ?int $viewerUserId): array
    {
        $post->loadMissing('user');
        if ($this->hasProductsTable()) {
            $post->loadMissing('relatedProduct');
        }
        $u = $post->user;
        $relatedProduct = $post->relatedProduct;
        $relatedImages = $relatedProduct instanceof Product && is_array($relatedProduct->images) ? $relatedProduct->images : [];
        $canExposeRelatedProduct = $relatedProduct instanceof Product && $relatedProduct->status === 'online';

        $images = is_array($post->images) ? array_values(array_filter($post->images, fn ($v) => is_string($v) && $v !== '')) : [];

        $isLiked = false;
        $isCollected = false;
        if ($viewerUserId) {
            $isLiked = CirclePostLike::query()
                ->where('post_id', $post->id)
                ->where('user_id', $viewerUserId)
                ->exists();
            $isCollected = CirclePostCollection::query()
                ->where('post_id', $post->id)
                ->where('user_id', $viewerUserId)
                ->exists();
        }

        return [
            'id' => (string) $post->id,
            'userId' => (string) $post->user_id,
            'nickname' => (string) ($u?->name ?? ''),
            'avatar' => (string) ($u?->avatar_url ?? ''),
            'title' => (string) ($post->title ?? ''),
            'description' => (string) ($post->description ?? ''),
            'content' => (string) $post->content,
            'images' => $images,
            'coverImage' => (string) ($post->cover_image ?? ($images[0] ?? '')),
            'sourceType' => (string) ($post->source_type ?? 'user_uploaded'),
            'publishSource' => (string) ($post->publish_source ?? 'manual_upload'),
            'visibility' => (string) ($post->visibility ?? CirclePostVisibility::Public),
            'topic' => (string) $post->topic,
            'favoriteCount' => (int) $post->favorite_count,
            'likeCount' => (int) $post->like_count,
            'commentCount' => (int) $post->comment_count,
            'isLiked' => $isLiked,
            'isCollected' => $isCollected,
            'isFavorited' => $isCollected,
            'relatedProductId' => $post->related_product_id ? (string) $post->related_product_id : null,
            'relatedProduct' => $canExposeRelatedProduct ? [
                'id' => (string) $relatedProduct->id,
                'name' => (string) $relatedProduct->name,
                'priceText' => '¥'.number_format(((int) $relatedProduct->price) / 100, 2, '.', ''),
                'image' => (string) ($relatedProduct->cover_image ?: (($relatedImages[0] ?? '') ?: '')),
            ] : null,
            'createdAt' => $post->published_at?->toIso8601String() ?? $post->created_at->toIso8601String(),
        ];
    }

    public function findVisibleForPublic(int $id, ?User $viewer): ?CirclePost
    {
        $post = CirclePost::query()->publishedVisible()->whereKey($id)->first();
        if ($post && $this->canViewerSeePost($post, $viewer)) {
            return $post;
        }
        // 自己的下架帖仍可读
        if ($viewer) {
            return CirclePost::query()
                ->whereKey($id)
                ->where('user_id', $viewer->id)
                ->where('status', '!=', CirclePostStatus::Deleted)
                ->whereNull('deleted_at')
                ->first();
        }

        return null;
    }

    /**
     * @param  array{title?: string, description?: string, content?: string, topic?: string, images: array<int, string>, sourceType?: string, publishSource?: string, visibility?: string, relatedProductId?: int|string|null}  $data
     */
    public function createByUser(User $user, array $data): CirclePost
    {
        $payload = [
            'user_id' => $user->id,
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'content' => $data['content'] ?? ($data['description'] ?? ''),
            'topic' => $data['topic'] ?? '',
            'images' => $data['images'],
            'cover_image' => $data['images'][0] ?? null,
            'source_type' => $data['sourceType'] ?? 'user_uploaded',
            'publish_source' => $data['publishSource'] ?? 'manual_upload',
            'related_product_id' => $data['relatedProductId'] ?? null,
            'status' => CirclePostStatus::Normal,
            'like_count' => 0,
            'favorite_count' => 0,
            'comment_count' => 0,
            'is_recommended' => false,
            'is_pinned' => false,
            'published_at' => now(),
        ];

        if ($this->hasVisibilityColumn()) {
            $payload['visibility'] = $data['visibility'] ?? CirclePostVisibility::Public;
        }

        return CirclePost::query()->create($payload);
    }

    public function toggleLike(User $user, CirclePost $post): CirclePost
    {
        return DB::transaction(function () use ($user, $post): CirclePost {
            $exists = CirclePostLike::query()
                ->where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($exists) {
                CirclePostLike::query()
                    ->where('post_id', $post->id)
                    ->where('user_id', $user->id)
                    ->delete();
                $post->decrement('like_count');
            } else {
                CirclePostLike::query()->create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
                $post->increment('like_count');
            }
            $post->refresh();

            return $post;
        });
    }

    public function toggleCollect(User $user, CirclePost $post): CirclePost
    {
        return DB::transaction(function () use ($user, $post): CirclePost {
            $exists = CirclePostCollection::query()
                ->where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($exists) {
                CirclePostCollection::query()
                    ->where('post_id', $post->id)
                    ->where('user_id', $user->id)
                    ->delete();
                $post->decrement('favorite_count');
            } else {
                CirclePostCollection::query()->create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
                $post->increment('favorite_count');
            }
            $post->refresh();

            return $post;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function commentsForApi(CirclePost $post): array
    {
        return CircleComment::query()
            ->where('post_id', $post->id)
            ->where('status', CircleCommentStatus::Normal)
            ->orderBy('id')
            ->with('user')
            ->get()
            ->map(fn (CircleComment $c) => $this->commentToApiArray($c))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function commentToApiArray(CircleComment $c): array
    {
        $c->loadMissing('user');
        $u = $c->user;

        return [
            'id' => (string) $c->id,
            'postId' => (string) $c->post_id,
            'userId' => (string) $c->user_id,
            'nickname' => (string) ($u?->name ?? ''),
            'avatar' => (string) ($u?->avatar_url ?? ''),
            'content' => (string) $c->content,
            'createdAt' => $c->created_at->toIso8601String(),
        ];
    }

    public function addComment(User $user, CirclePost $post, string $content): CircleComment
    {
        return DB::transaction(function () use ($user, $post, $content): CircleComment {
            $comment = CircleComment::query()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'content' => $content,
                'status' => CircleCommentStatus::Normal,
            ]);
            $post->increment('comment_count');

            return $comment;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function myPosts(User $user): array
    {
        return CirclePost::query()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (CirclePost $p) => $this->postToApiArray($p, (int) $user->id))
            ->all();
    }

    /**
     * 当前用户在灵感流里「收藏」过的帖子（仍可浏览的），按收藏时间倒序。
     *
     * @return array<int, array<string, mixed>>
     */
    public function myCollectedPosts(User $user): array
    {
        $postIds = CirclePostCollection::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(120)
            ->pluck('post_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $this->mapPostsByIdsForViewer($postIds, $user);
    }

    /**
     * 当前用户点过赞的灵感帖，按点赞时间倒序。
     *
     * @return array<int, array<string, mixed>>
     */
    public function myLikedPosts(User $user): array
    {
        $postIds = CirclePostLike::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(120)
            ->pluck('post_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $this->mapPostsByIdsForViewer($postIds, $user);
    }

    /**
     * 评论动态：我发出的评论 + 他人对我帖子的评论，按时间合并倒序。
     *
     * @return array<int, array<string, mixed>>
     */
    public function myCommentActivity(User $user): array
    {
        $uid = (int) $user->id;

        $sent = CircleComment::query()
            ->where('user_id', $uid)
            ->where('status', CircleCommentStatus::Normal)
            ->with(['post.user'])
            ->orderByDesc('created_at')
            ->limit(80)
            ->get();

        $received = CircleComment::query()
            ->where('user_id', '!=', $uid)
            ->where('status', CircleCommentStatus::Normal)
            ->whereHas('post', function (Builder $q) use ($uid): void {
                $q->where('user_id', $uid)->whereNull('deleted_at');
            })
            ->with(['post.user', 'user'])
            ->orderByDesc('created_at')
            ->limit(80)
            ->get();

        $rows = [];
        foreach ($sent as $c) {
            $row = $this->commentActivityRowFromSent($c);
            if ($row !== null) {
                $rows[] = $row;
            }
        }
        foreach ($received as $c) {
            $row = $this->commentActivityRowFromReceived($c);
            if ($row !== null) {
                $rows[] = $row;
            }
        }

        usort($rows, static fn (array $a, array $b): int => strcmp((string) $b['createdAt'], (string) $a['createdAt']));

        return array_slice($rows, 0, 120);
    }

    /**
     * @param  array<int, int>  $postIds
     * @return array<int, array<string, mixed>>
     */
    private function mapPostsByIdsForViewer(array $postIds, User $viewer): array
    {
        if ($postIds === []) {
            return [];
        }

        $uniqueIds = array_values(array_unique(array_filter($postIds, fn ($id) => $id > 0)));
        if ($uniqueIds === []) {
            return [];
        }

        $posts = CirclePost::query()
            ->whereIn('id', $uniqueIds)
            ->get()
            ->keyBy('id');

        $out = [];
        $seen = [];
        foreach ($postIds as $pid) {
            $id = (int) $pid;
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }
            $p = $posts->get($id);
            if (! $p instanceof CirclePost) {
                continue;
            }
            $visible = $this->findVisibleForPublic($id, $viewer);
            if ($visible === null) {
                continue;
            }
            $seen[$id] = true;
            $out[] = $this->postToApiArray($visible, (int) $viewer->id);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function commentActivityRowFromSent(CircleComment $c): ?array
    {
        $post = $c->post;
        if (! $post instanceof CirclePost) {
            return null;
        }

        $author = $post->user;

        return [
            'id' => (string) $c->id,
            'direction' => 'sent',
            'postId' => (string) $post->id,
            'postTitle' => (string) ($post->title ?? ''),
            'postCoverImage' => (string) ($post->firstImageUrl() ?? ''),
            'commentExcerpt' => $this->excerptText((string) $c->content, 160),
            'counterpartNickname' => (string) ($author?->name ?? ''),
            'createdAt' => $c->created_at->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function commentActivityRowFromReceived(CircleComment $c): ?array
    {
        $post = $c->post;
        if (! $post instanceof CirclePost) {
            return null;
        }

        $commenter = $c->user;

        return [
            'id' => (string) $c->id,
            'direction' => 'received',
            'postId' => (string) $post->id,
            'postTitle' => (string) ($post->title ?? ''),
            'postCoverImage' => (string) ($post->firstImageUrl() ?? ''),
            'commentExcerpt' => $this->excerptText((string) $c->content, 160),
            'counterpartNickname' => (string) ($commenter?->name ?? ''),
            'createdAt' => $c->created_at->toIso8601String(),
        ];
    }

    private function excerptText(string $text, int $maxChars): string
    {
        $t = trim($text);
        if ($t === '') {
            return '';
        }
        if (function_exists('mb_substr')) {
            $s = mb_substr($t, 0, $maxChars);

            return mb_strlen($t) > $maxChars ? $s.'…' : $s;
        }

        return strlen($t) > $maxChars ? substr($t, 0, $maxChars).'…' : $t;
    }

    private function canViewerSeePost(CirclePost $post, ?User $viewer): bool
    {
        if (! $this->hasVisibilityColumn()) {
            return true;
        }

        $visibility = (string) ($post->visibility ?? CirclePostVisibility::Public);
        if ($visibility === CirclePostVisibility::Public) {
            return true;
        }

        if (! $viewer) {
            return false;
        }

        if ((int) $viewer->id === (int) $post->user_id) {
            return true;
        }

        if ($visibility === CirclePostVisibility::Private) {
            return false;
        }

        if ($visibility === CirclePostVisibility::Friends) {
            return $this->isMutualFollow((int) $viewer->id, (int) $post->user_id);
        }

        return true;
    }

    private function applyVisibilityForViewer(Builder $query, ?User $viewer): void
    {
        if (! $this->hasVisibilityColumn()) {
            return;
        }

        if (! $viewer) {
            $query->where('visibility', CirclePostVisibility::Public);

            return;
        }

        $query->where(function (Builder $scope) use ($viewer): void {
            $scope->where('visibility', CirclePostVisibility::Public)
                ->orWhere('user_id', $viewer->id);

            if ($this->hasUserFollowsTable()) {
                $scope->orWhere(function (Builder $friends) use ($viewer): void {
                    $friends->where('visibility', CirclePostVisibility::Friends)
                        ->where('user_id', '!=', $viewer->id)
                        ->whereExists(function ($sub) use ($viewer): void {
                            $sub->selectRaw('1')
                                ->from('user_follows as uf_out')
                                ->where('uf_out.follower_id', $viewer->id)
                                ->whereColumn('uf_out.followee_id', 'circle_posts.user_id');
                        })
                        ->whereExists(function ($sub) use ($viewer): void {
                            $sub->selectRaw('1')
                                ->from('user_follows as uf_in')
                                ->where('uf_in.followee_id', $viewer->id)
                                ->whereColumn('uf_in.follower_id', 'circle_posts.user_id');
                        });
                });
            }
        });
    }

    private function isMutualFollow(int $viewerId, int $authorId): bool
    {
        if ($viewerId <= 0 || $authorId <= 0 || $viewerId === $authorId || ! $this->hasUserFollowsTable()) {
            return false;
        }

        $viewerFollowedAuthor = DB::table('user_follows')
            ->where('follower_id', $viewerId)
            ->where('followee_id', $authorId)
            ->exists();
        if (! $viewerFollowedAuthor) {
            return false;
        }

        return DB::table('user_follows')
            ->where('follower_id', $authorId)
            ->where('followee_id', $viewerId)
            ->exists();
    }
}
