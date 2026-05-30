<?php

namespace App\Services;

use App\Models\MiniappAnalyticsEvent;
use App\Support\MiniappAnalyticsEventCatalog;
use Illuminate\Support\Carbon;

/**
 * 小程序埋点统计。
 */
final class MiniappAnalyticsService
{
    /**
     * @return array{
     *   total_events: int,
     *   home_page_views: int,
     *   me_page_views: int,
     *   me_sub_page_views: int,
     *   home_uv: int,
     *   me_uv: int,
     *   logged_in_events: int,
     *   guest_events: int,
     *   conversion_events: int,
     * }
     */
    public function rangeOverview(Carbon $now, int $days = 1): array
    {
        [$start, $end] = $this->rangeBounds($now, $days);

        $meSubPages = array_values(array_filter(
            MiniappAnalyticsEventCatalog::pages(),
            fn (string $p): bool => $p !== MiniappAnalyticsEventCatalog::PAGE_HOME
                && $p !== MiniappAnalyticsEventCatalog::PAGE_ME,
        ));

        $meSubPv = MiniappAnalyticsEvent::query()
            ->whereIn('page', $meSubPages)
            ->where('category', MiniappAnalyticsEventCatalog::CATEGORY_PAGE_VIEW)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->count();

        return [
            'total_events' => $this->countBetween($start, $end),
            'home_page_views' => $this->countEventBetween('home.page_view', $start, $end),
            'me_page_views' => $this->countEventBetween('me.page_view', $start, $end),
            'me_sub_page_views' => $meSubPv,
            'home_uv' => $this->distinctSessionsBetween(MiniappAnalyticsEventCatalog::PAGE_HOME, $start, $end),
            'me_uv' => $this->distinctSessionsBetween(MiniappAnalyticsEventCatalog::PAGE_ME, $start, $end),
            'logged_in_events' => MiniappAnalyticsEvent::query()
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('user_id')
                ->count(),
            'guest_events' => MiniappAnalyticsEvent::query()
                ->whereBetween('created_at', [$start, $end])
                ->whereNull('user_id')
                ->count(),
            'conversion_events' => MiniappAnalyticsEvent::query()
                ->whereBetween('created_at', [$start, $end])
                ->where('category', MiniappAnalyticsEventCatalog::CATEGORY_CONVERSION)
                ->count(),
        ];
    }

    /**
     * @return array{
     *   home_generate_rate_pct: float|null,
     *   home_success_rate_pct: float|null,
     *   home_favorite_rate_pct: float|null,
     *   me_login_rate_pct: float|null,
     *   me_login_success_rate_pct: float|null,
     *   me_sponsor_pay_rate_pct: float|null,
     *   me_feedback_submit_rate_pct: float|null,
     * }
     */
    public function rangeRates(Carbon $now, int $days = 1): array
    {
        [$start, $end] = $this->rangeBounds($now, $days);

        $homePv = $this->countEventBetween('home.page_view', $start, $end);
        $generateStart = $this->countEventBetween('home.wizard_generate_start', $start, $end);
        $generateSuccess = $this->countEventBetween('home.wizard_generate_success', $start, $end);
        $favoriteToggle = $this->countEventBetween('home.recipe_favorite_toggle', $start, $end);

        $mePv = $this->countEventBetween('me.page_view', $start, $end);
        $loginClick = $this->countEventBetween('me.wechat_login', $start, $end);
        $loginSuccess = $this->countEventBetween('me.wechat_login_success', $start, $end);

        $sponsorPv = $this->countEventBetween('me_sponsorship.page_view', $start, $end);
        $sponsorPayStart = $this->countEventBetween('me_sponsorship.pay_start', $start, $end);
        $sponsorPaySuccess = $this->countEventBetween('me_sponsorship.pay_success', $start, $end);

        $feedbackPv = $this->countEventBetween('me_feedback.page_view', $start, $end);
        $feedbackSubmit = $this->countEventBetween('me_feedback.submit', $start, $end);
        $feedbackSuccess = $this->countEventBetween('me_feedback.submit_success', $start, $end);

        return [
            'home_generate_rate_pct' => $this->ratePct($generateStart, $homePv),
            'home_success_rate_pct' => $this->ratePct($generateSuccess, $generateStart),
            'home_favorite_rate_pct' => $this->ratePct($favoriteToggle, $generateSuccess),
            'me_login_rate_pct' => $this->ratePct($loginClick, $mePv),
            'me_login_success_rate_pct' => $this->ratePct($loginSuccess, $loginClick),
            'me_sponsor_pay_rate_pct' => $this->ratePct($sponsorPaySuccess, $sponsorPayStart),
            'me_feedback_submit_rate_pct' => $this->ratePct($feedbackSuccess, $feedbackSubmit),
        ];
    }

    /**
     * @return list<array{
     *   date: string,
     *   home_page_views: int,
     *   me_page_views: int,
     *   total_events: int,
     *   home_generate_success: int,
     *   me_login_success: int,
     *   conversion_events: int,
     * }>
     */
    public function dailyTrend(Carbon $now, int $days = 7): array
    {
        $out = [];
        $span = max(1, min(90, $days));
        for ($i = $span - 1; $i >= 0; $i--) {
            $day = $now->copy()->startOfDay()->subDays($i);
            $start = $day->copy();
            $end = $day->copy()->addDay();

            $out[] = [
                'date' => $day->toDateString(),
                'home_page_views' => $this->countEventBetween('home.page_view', $start, $end),
                'me_page_views' => $this->countEventBetween('me.page_view', $start, $end),
                'total_events' => $this->countBetween($start, $end),
                'home_generate_success' => $this->countEventBetween('home.wizard_generate_success', $start, $end),
                'me_login_success' => $this->countEventBetween('me.wechat_login_success', $start, $end),
                'conversion_events' => MiniappAnalyticsEvent::query()
                    ->where('category', MiniappAnalyticsEventCatalog::CATEGORY_CONVERSION)
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<', $end)
                    ->count(),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{event_name: string, label: string, count: int}>
     */
    public function topEvents(?string $page, Carbon $now, int $days = 7, int $limit = 12): array
    {
        [$start] = $this->rangeBounds($now, $days);
        $counts = [];

        $query = MiniappAnalyticsEvent::query()
            ->where('created_at', '>=', $start)
            ->where('event_name', 'not like', '%.page_view');
        if ($page !== null && $page !== '') {
            $query->where('page', $page);
        }

        $query->orderBy('id')
            ->chunkById(3000, function ($chunk) use (&$counts): void {
                foreach ($chunk as $row) {
                    $name = (string) $row->event_name;
                    $counts[$name] = ($counts[$name] ?? 0) + 1;
                }
            });

        arsort($counts);
        $out = [];
        $i = 0;
        foreach ($counts as $name => $count) {
            if ($i >= $limit) {
                break;
            }
            $out[] = [
                'event_name' => $name,
                'label' => MiniappAnalyticsEventCatalog::label($name),
                'count' => $count,
            ];
            $i++;
        }

        return $out;
    }

    /**
     * @return list<array{page: string, page_label: string, count: int}>
     */
    public function eventsByPage(Carbon $now, int $days = 7): array
    {
        [$start] = $this->rangeBounds($now, $days);
        $counts = [];

        MiniappAnalyticsEvent::query()
            ->where('created_at', '>=', $start)
            ->orderBy('id')
            ->chunkById(3000, function ($chunk) use (&$counts): void {
                foreach ($chunk as $row) {
                    $page = (string) $row->page;
                    $counts[$page] = ($counts[$page] ?? 0) + 1;
                }
            });

        $out = [];
        foreach (MiniappAnalyticsEventCatalog::pages() as $page) {
            $out[] = [
                'page' => $page,
                'page_label' => MiniappAnalyticsEventCatalog::pageLabel($page),
                'count' => $counts[$page] ?? 0,
            ];
        }
        usort($out, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $out;
    }

    /**
     * @return list<array{category: string, category_label: string, count: int}>
     */
    public function eventsByCategory(Carbon $now, int $days = 7): array
    {
        [$start] = $this->rangeBounds($now, $days);
        $counts = [];

        MiniappAnalyticsEvent::query()
            ->where('created_at', '>=', $start)
            ->orderBy('id')
            ->chunkById(3000, function ($chunk) use (&$counts): void {
                foreach ($chunk as $row) {
                    $cat = (string) $row->category;
                    $counts[$cat] = ($counts[$cat] ?? 0) + 1;
                }
            });

        $out = [];
        foreach ([
            MiniappAnalyticsEventCatalog::CATEGORY_PAGE_VIEW,
            MiniappAnalyticsEventCatalog::CATEGORY_CLICK,
            MiniappAnalyticsEventCatalog::CATEGORY_ACTION,
            MiniappAnalyticsEventCatalog::CATEGORY_CONVERSION,
        ] as $cat) {
            $out[] = [
                'category' => $cat,
                'category_label' => MiniappAnalyticsEventCatalog::categoryLabel($cat),
                'count' => $counts[$cat] ?? 0,
            ];
        }

        return $out;
    }

    /**
     * @return array{
     *   home_page_view: int,
     *   generate_start: int,
     *   generate_success: int,
     *   favorite_toggle: int,
     *   image_generate: int,
     * }
     */
    public function homeFunnel(Carbon $now, int $days = 7): array
    {
        [$start] = $this->rangeBounds($now, $days);

        return [
            'home_page_view' => $this->countEventSince('home.page_view', $start),
            'generate_start' => $this->countEventSince('home.wizard_generate_start', $start),
            'generate_success' => $this->countEventSince('home.wizard_generate_success', $start),
            'favorite_toggle' => $this->countEventSince('home.recipe_favorite_toggle', $start),
            'image_generate' => $this->countEventSince('home.recipe_image_generate', $start),
        ];
    }

    /**
     * @return array{
     *   me_page_view: int,
     *   login_click: int,
     *   login_success: int,
     *   nav_clicks: int,
     *   profile_saves: int,
     * }
     */
    public function meFunnel(Carbon $now, int $days = 7): array
    {
        [$start] = $this->rangeBounds($now, $days);

        $navClicks = MiniappAnalyticsEvent::query()
            ->where('page', MiniappAnalyticsEventCatalog::PAGE_ME)
            ->where('category', MiniappAnalyticsEventCatalog::CATEGORY_CLICK)
            ->where('created_at', '>=', $start)
            ->count();

        $profileSaves = MiniappAnalyticsEvent::query()
            ->where('created_at', '>=', $start)
            ->whereIn('event_name', [
                'me_profile_edit.save_success',
                'me_diet_prefs.save_success',
                'me_basic_profile.save_success',
                'me_recommend_settings.save_success',
                'me_personal_info.nickname_save',
            ])
            ->count();

        return [
            'me_page_view' => $this->countEventSince('me.page_view', $start),
            'login_click' => $this->countEventSince('me.wechat_login', $start),
            'login_success' => $this->countEventSince('me.wechat_login_success', $start),
            'nav_clicks' => $navClicks,
            'profile_saves' => $profileSaves,
        ];
    }

    /**
     * @return list<array{
     *   id: int,
     *   page: string,
     *   page_label: string,
     *   category: string,
     *   category_label: string,
     *   event_name: string,
     *   label: string,
     *   event_value: string|null,
     *   user_id: int|null,
     *   client_session_id: string|null,
     *   created_at: string,
     * }>
     */
    public function recentEvents(Carbon $now, int $limit = 50): array
    {
        return MiniappAnalyticsEvent::query()
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (MiniappAnalyticsEvent $row): array => [
                'id' => (int) $row->id,
                'page' => $row->page,
                'page_label' => MiniappAnalyticsEventCatalog::pageLabel($row->page),
                'category' => $row->category,
                'category_label' => MiniappAnalyticsEventCatalog::categoryLabel($row->category),
                'event_name' => $row->event_name,
                'label' => MiniappAnalyticsEventCatalog::label($row->event_name),
                'event_value' => $row->event_value,
                'user_id' => $row->user_id !== null ? (int) $row->user_id : null,
                'client_session_id' => $row->client_session_id,
                'created_at' => $row->created_at?->format('Y-m-d H:i:s') ?? '',
            ])
            ->all();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rangeBounds(Carbon $now, int $days): array
    {
        $span = max(1, min(90, $days));
        if ($span === 1) {
            return $this->dayRange($now);
        }

        $start = $now->copy()->startOfDay()->subDays($span - 1);
        $end = $now->copy()->addDay()->startOfDay();

        return [$start, $end];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dayRange(Carbon $now): array
    {
        $start = $now->copy()->startOfDay();
        $end = $now->copy()->addDay()->startOfDay();

        return [$start, $end];
    }

    private function countBetween(Carbon $start, Carbon $end): int
    {
        return MiniappAnalyticsEvent::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->count();
    }

    private function countEventBetween(string $eventName, Carbon $start, Carbon $end): int
    {
        return MiniappAnalyticsEvent::query()
            ->where('event_name', $eventName)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->count();
    }

    private function countEventSince(string $eventName, Carbon $since): int
    {
        return MiniappAnalyticsEvent::query()
            ->where('event_name', $eventName)
            ->where('created_at', '>=', $since)
            ->count();
    }

    private function distinctSessionsBetween(string $page, Carbon $start, Carbon $end): int
    {
        return (int) MiniappAnalyticsEvent::query()
            ->where('page', $page)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('client_session_id')
            ->distinct('client_session_id')
            ->count('client_session_id');
    }

    private function ratePct(int $num, int $den): ?float
    {
        if ($den <= 0) {
            return null;
        }

        return round($num / $den * 100, 1);
    }
}
