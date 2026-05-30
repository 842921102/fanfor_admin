<?php

namespace App\Support;

/**
 * 小程序埋点事件白名单与展示文案。
 *
 * @phpstan-type EventDef array{page: string, category: string, label: string}
 */
final class MiniappAnalyticsEventCatalog
{
    public const PAGE_HOME = 'home';

    public const PAGE_ME = 'me';

    public const PAGE_ME_SPONSORSHIP = 'me_sponsorship';

    public const PAGE_ME_FEEDBACK = 'me_feedback';

    public const PAGE_ME_PERSONAL_INFO = 'me_personal_info';

    public const PAGE_ME_TASTE_PREFS = 'me_taste_prefs';

    public const PAGE_ME_PROFILE_EDIT = 'me_profile_edit';

    public const PAGE_ME_DIET_PREFS = 'me_diet_prefs';

    public const PAGE_ME_BASIC_PROFILE = 'me_basic_profile';

    public const PAGE_ME_RECOMMEND_SETTINGS = 'me_recommend_settings';

    public const CATEGORY_PAGE_VIEW = 'page_view';

    public const CATEGORY_CLICK = 'click';

    public const CATEGORY_ACTION = 'action';

    public const CATEGORY_CONVERSION = 'conversion';

    /**
     * @var array<string, EventDef>
     */
    public const EVENTS = [
        // —— 首页（自由搭配）——
        'home.page_view' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'home.ingredient_add_manual' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '手动添加食材'],
        'home.ingredient_add_quick' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '快速选择食材'],
        'home.ingredient_remove' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '删除食材'],
        'home.ingredient_recognize_photo' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '拍照识别食材'],
        'home.ingredient_recognized_confirm' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '确认加入识别食材'],
        'home.cuisine_toggle' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '切换菜系'],
        'home.preset_apply' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '应用预设'],
        'home.inspiration_random' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '随机灵感'],
        'home.wizard_generate_start' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '开始生成菜谱'],
        'home.wizard_generate_success' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_CONVERSION, 'label' => '生成菜谱成功'],
        'home.wizard_generate_fail' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '生成菜谱失败'],
        'home.recipe_favorite_toggle' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_CONVERSION, 'label' => '收藏/取消收藏'],
        'home.recipe_image_generate' => ['page' => self::PAGE_HOME, 'category' => self::CATEGORY_ACTION, 'label' => '生成菜品图片'],

        // —— 个人中心主页 ——
        'me.page_view' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'me.wechat_login' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_ACTION, 'label' => '点击微信登录'],
        'me.wechat_login_success' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CONVERSION, 'label' => '微信登录成功'],
        'me.nav_taste_profile' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '进入口味画像'],
        'me.nav_sponsorship' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '进入赞助页'],
        'me.nav_settings' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '进入设置'],
        'me.nav_orders' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '进入订单'],
        'me.nav_favorites' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '进入收藏'],
        'me.nav_recommendation_history' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '进入最近推荐'],
        'me.nav_histories' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '进入我的历史'],
        'me.nav_history_by_source' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '按玩法进入历史'],
        'me.nav_requirement_feedback' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '进入需求反馈'],
        'me.service_help_center' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '打开帮助中心'],
        'me.service_about_us' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '打开关于我们'],
        'me.service_user_agreement' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '打开用户协议'],
        'me.service_privacy_policy' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '打开隐私政策'],
        'me.service_wechat_contact' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_CLICK, 'label' => '打开微信客服'],
        'me.help_qa_toggle' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_ACTION, 'label' => '展开/收起帮助问答'],
        'me.avatar_change_menu' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_ACTION, 'label' => '打开头像菜单'],
        'me.avatar_wechat_choose' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_ACTION, 'label' => '选择微信头像'],
        'me.avatar_local_pick' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_ACTION, 'label' => '本地选择头像'],
        'me.nickname_edit_open' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_ACTION, 'label' => '打开昵称编辑'],
        'me.nickname_save' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_ACTION, 'label' => '保存昵称'],
        'me.logout' => ['page' => self::PAGE_ME, 'category' => self::CATEGORY_ACTION, 'label' => '退出登录'],

        // —— 赞助页 ——
        'me_sponsorship.page_view' => ['page' => self::PAGE_ME_SPONSORSHIP, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'me_sponsorship.pay_start' => ['page' => self::PAGE_ME_SPONSORSHIP, 'category' => self::CATEGORY_ACTION, 'label' => '发起赞助支付'],
        'me_sponsorship.pay_success' => ['page' => self::PAGE_ME_SPONSORSHIP, 'category' => self::CATEGORY_CONVERSION, 'label' => '赞助支付成功'],
        'me_sponsorship.pay_fail' => ['page' => self::PAGE_ME_SPONSORSHIP, 'category' => self::CATEGORY_ACTION, 'label' => '赞助支付失败/取消'],
        'me_sponsorship.cancel_identity' => ['page' => self::PAGE_ME_SPONSORSHIP, 'category' => self::CATEGORY_ACTION, 'label' => '取消赞助身份'],

        // —— 需求反馈 ——
        'me_feedback.page_view' => ['page' => self::PAGE_ME_FEEDBACK, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'me_feedback.submit' => ['page' => self::PAGE_ME_FEEDBACK, 'category' => self::CATEGORY_ACTION, 'label' => '提交需求反馈'],
        'me_feedback.submit_success' => ['page' => self::PAGE_ME_FEEDBACK, 'category' => self::CATEGORY_CONVERSION, 'label' => '反馈提交成功'],
        'me_feedback.submit_fail' => ['page' => self::PAGE_ME_FEEDBACK, 'category' => self::CATEGORY_ACTION, 'label' => '反馈提交失败'],

        // —— 个人资料设置 ——
        'me_personal_info.page_view' => ['page' => self::PAGE_ME_PERSONAL_INFO, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'me_personal_info.avatar_change' => ['page' => self::PAGE_ME_PERSONAL_INFO, 'category' => self::CATEGORY_ACTION, 'label' => '更换头像'],
        'me_personal_info.nickname_save' => ['page' => self::PAGE_ME_PERSONAL_INFO, 'category' => self::CATEGORY_ACTION, 'label' => '保存昵称'],
        'me_personal_info.gender_change' => ['page' => self::PAGE_ME_PERSONAL_INFO, 'category' => self::CATEGORY_ACTION, 'label' => '修改性别'],
        'me_personal_info.birthday_change' => ['page' => self::PAGE_ME_PERSONAL_INFO, 'category' => self::CATEGORY_ACTION, 'label' => '修改生日'],
        'me_personal_info.region_change' => ['page' => self::PAGE_ME_PERSONAL_INFO, 'category' => self::CATEGORY_ACTION, 'label' => '修改地区'],
        'me_personal_info.phone_change' => ['page' => self::PAGE_ME_PERSONAL_INFO, 'category' => self::CATEGORY_ACTION, 'label' => '更换手机号'],

        // —— 口味画像入口 ——
        'me_taste_prefs.page_view' => ['page' => self::PAGE_ME_TASTE_PREFS, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'me_taste_prefs.nav_profile_edit' => ['page' => self::PAGE_ME_TASTE_PREFS, 'category' => self::CATEGORY_CLICK, 'label' => '进入编辑推荐画像'],
        'me_taste_prefs.nav_onboarding' => ['page' => self::PAGE_ME_TASTE_PREFS, 'category' => self::CATEGORY_CLICK, 'label' => '重新完成问卷'],
        'me_taste_prefs.nav_diet_prefs' => ['page' => self::PAGE_ME_TASTE_PREFS, 'category' => self::CATEGORY_CLICK, 'label' => '进入饮食偏好'],
        'me_taste_prefs.nav_basic_profile' => ['page' => self::PAGE_ME_TASTE_PREFS, 'category' => self::CATEGORY_CLICK, 'label' => '进入基础资料'],
        'me_taste_prefs.nav_recommend_settings' => ['page' => self::PAGE_ME_TASTE_PREFS, 'category' => self::CATEGORY_CLICK, 'label' => '进入推荐设置'],
        'me_taste_prefs.nav_recipe_favorites' => ['page' => self::PAGE_ME_TASTE_PREFS, 'category' => self::CATEGORY_CLICK, 'label' => '进入标准菜谱收藏'],

        // —— 推荐画像编辑 ——
        'me_profile_edit.page_view' => ['page' => self::PAGE_ME_PROFILE_EDIT, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'me_profile_edit.save' => ['page' => self::PAGE_ME_PROFILE_EDIT, 'category' => self::CATEGORY_ACTION, 'label' => '保存推荐画像'],
        'me_profile_edit.save_success' => ['page' => self::PAGE_ME_PROFILE_EDIT, 'category' => self::CATEGORY_CONVERSION, 'label' => '保存推荐画像成功'],
        'me_profile_edit.save_fail' => ['page' => self::PAGE_ME_PROFILE_EDIT, 'category' => self::CATEGORY_ACTION, 'label' => '保存推荐画像失败'],

        // —— 饮食偏好 ——
        'me_diet_prefs.page_view' => ['page' => self::PAGE_ME_DIET_PREFS, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'me_diet_prefs.save' => ['page' => self::PAGE_ME_DIET_PREFS, 'category' => self::CATEGORY_ACTION, 'label' => '保存饮食偏好'],
        'me_diet_prefs.save_success' => ['page' => self::PAGE_ME_DIET_PREFS, 'category' => self::CATEGORY_CONVERSION, 'label' => '保存饮食偏好成功'],
        'me_diet_prefs.save_fail' => ['page' => self::PAGE_ME_DIET_PREFS, 'category' => self::CATEGORY_ACTION, 'label' => '保存饮食偏好失败'],

        // —— 基础资料 ——
        'me_basic_profile.page_view' => ['page' => self::PAGE_ME_BASIC_PROFILE, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'me_basic_profile.save' => ['page' => self::PAGE_ME_BASIC_PROFILE, 'category' => self::CATEGORY_ACTION, 'label' => '保存基础资料'],
        'me_basic_profile.save_success' => ['page' => self::PAGE_ME_BASIC_PROFILE, 'category' => self::CATEGORY_CONVERSION, 'label' => '保存基础资料成功'],
        'me_basic_profile.save_fail' => ['page' => self::PAGE_ME_BASIC_PROFILE, 'category' => self::CATEGORY_ACTION, 'label' => '保存基础资料失败'],

        // —— 推荐设置 ——
        'me_recommend_settings.page_view' => ['page' => self::PAGE_ME_RECOMMEND_SETTINGS, 'category' => self::CATEGORY_PAGE_VIEW, 'label' => '页面浏览'],
        'me_recommend_settings.save' => ['page' => self::PAGE_ME_RECOMMEND_SETTINGS, 'category' => self::CATEGORY_ACTION, 'label' => '保存推荐设置'],
        'me_recommend_settings.save_success' => ['page' => self::PAGE_ME_RECOMMEND_SETTINGS, 'category' => self::CATEGORY_CONVERSION, 'label' => '保存推荐设置成功'],
        'me_recommend_settings.save_fail' => ['page' => self::PAGE_ME_RECOMMEND_SETTINGS, 'category' => self::CATEGORY_ACTION, 'label' => '保存推荐设置失败'],
    ];

    /**
     * @return list<string>
     */
    public static function pages(): array
    {
        return [
            self::PAGE_HOME,
            self::PAGE_ME,
            self::PAGE_ME_SPONSORSHIP,
            self::PAGE_ME_FEEDBACK,
            self::PAGE_ME_PERSONAL_INFO,
            self::PAGE_ME_TASTE_PREFS,
            self::PAGE_ME_PROFILE_EDIT,
            self::PAGE_ME_DIET_PREFS,
            self::PAGE_ME_BASIC_PROFILE,
            self::PAGE_ME_RECOMMEND_SETTINGS,
        ];
    }

    /**
     * @return list<string>
     */
    public static function eventNames(): array
    {
        return array_keys(self::EVENTS);
    }

    public static function isAllowed(string $eventName): bool
    {
        return isset(self::EVENTS[$eventName]);
    }

    /**
     * @return EventDef|null
     */
    public static function definition(string $eventName): ?array
    {
        return self::EVENTS[$eventName] ?? null;
    }

    public static function label(string $eventName): string
    {
        return self::EVENTS[$eventName]['label'] ?? $eventName;
    }

    public static function pageLabel(string $page): string
    {
        return match ($page) {
            self::PAGE_HOME => '首页（自由搭配）',
            self::PAGE_ME => '个人中心',
            self::PAGE_ME_SPONSORSHIP => '赞助饭否',
            self::PAGE_ME_FEEDBACK => '需求反馈',
            self::PAGE_ME_PERSONAL_INFO => '个人资料',
            self::PAGE_ME_TASTE_PREFS => '口味画像入口',
            self::PAGE_ME_PROFILE_EDIT => '推荐画像编辑',
            self::PAGE_ME_DIET_PREFS => '饮食偏好',
            self::PAGE_ME_BASIC_PROFILE => '基础资料',
            self::PAGE_ME_RECOMMEND_SETTINGS => '推荐设置',
            default => $page,
        };
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_PAGE_VIEW => '页面浏览',
            self::CATEGORY_CLICK => '点击导航',
            self::CATEGORY_ACTION => '用户操作',
            self::CATEGORY_CONVERSION => '转化',
            default => $category,
        };
    }

    /**
     * @return list<array{event_name: string, page: string, page_label: string, category: string, category_label: string, label: string}>
     */
    public static function catalogRows(): array
    {
        $rows = [];
        foreach (self::EVENTS as $name => $def) {
            $rows[] = [
                'event_name' => $name,
                'page' => $def['page'],
                'page_label' => self::pageLabel($def['page']),
                'category' => $def['category'],
                'category_label' => self::categoryLabel($def['category']),
                'label' => $def['label'],
            ];
        }

        return $rows;
    }
}
