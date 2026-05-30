/** 与后端 MiniappAnalyticsEventCatalog 保持同步的事件名常量 */
export const AnalyticsEvents = {
  HOME_PAGE_VIEW: 'home.page_view',
  HOME_INGREDIENT_ADD_MANUAL: 'home.ingredient_add_manual',
  HOME_INGREDIENT_ADD_QUICK: 'home.ingredient_add_quick',
  HOME_INGREDIENT_REMOVE: 'home.ingredient_remove',
  HOME_INGREDIENT_RECOGNIZE_PHOTO: 'home.ingredient_recognize_photo',
  HOME_INGREDIENT_RECOGNIZED_CONFIRM: 'home.ingredient_recognized_confirm',
  HOME_CUISINE_TOGGLE: 'home.cuisine_toggle',
  HOME_PRESET_APPLY: 'home.preset_apply',
  HOME_INSPIRATION_RANDOM: 'home.inspiration_random',
  HOME_WIZARD_GENERATE_START: 'home.wizard_generate_start',
  HOME_WIZARD_GENERATE_SUCCESS: 'home.wizard_generate_success',
  HOME_WIZARD_GENERATE_FAIL: 'home.wizard_generate_fail',
  HOME_RECIPE_FAVORITE_TOGGLE: 'home.recipe_favorite_toggle',
  HOME_RECIPE_IMAGE_GENERATE: 'home.recipe_image_generate',

  ME_PAGE_VIEW: 'me.page_view',
  ME_WECHAT_LOGIN: 'me.wechat_login',
  ME_WECHAT_LOGIN_SUCCESS: 'me.wechat_login_success',
  ME_NAV_TASTE_PROFILE: 'me.nav_taste_profile',
  ME_NAV_SPONSORSHIP: 'me.nav_sponsorship',
  ME_NAV_SETTINGS: 'me.nav_settings',
  ME_NAV_ORDERS: 'me.nav_orders',
  ME_NAV_FAVORITES: 'me.nav_favorites',
  ME_NAV_RECOMMENDATION_HISTORY: 'me.nav_recommendation_history',
  ME_NAV_HISTORIES: 'me.nav_histories',
  ME_NAV_HISTORY_BY_SOURCE: 'me.nav_history_by_source',
  ME_NAV_REQUIREMENT_FEEDBACK: 'me.nav_requirement_feedback',
  ME_SERVICE_HELP_CENTER: 'me.service_help_center',
  ME_SERVICE_ABOUT_US: 'me.service_about_us',
  ME_SERVICE_USER_AGREEMENT: 'me.service_user_agreement',
  ME_SERVICE_PRIVACY_POLICY: 'me.service_privacy_policy',
  ME_SERVICE_WECHAT_CONTACT: 'me.service_wechat_contact',
  ME_HELP_QA_TOGGLE: 'me.help_qa_toggle',
  ME_AVATAR_CHANGE_MENU: 'me.avatar_change_menu',
  ME_AVATAR_WECHAT_CHOOSE: 'me.avatar_wechat_choose',
  ME_AVATAR_LOCAL_PICK: 'me.avatar_local_pick',
  ME_NICKNAME_EDIT_OPEN: 'me.nickname_edit_open',
  ME_NICKNAME_SAVE: 'me.nickname_save',
  ME_LOGOUT: 'me.logout',

  ME_SPONSORSHIP_PAGE_VIEW: 'me_sponsorship.page_view',
  ME_SPONSORSHIP_PAY_START: 'me_sponsorship.pay_start',
  ME_SPONSORSHIP_PAY_SUCCESS: 'me_sponsorship.pay_success',
  ME_SPONSORSHIP_PAY_FAIL: 'me_sponsorship.pay_fail',
  ME_SPONSORSHIP_CANCEL_IDENTITY: 'me_sponsorship.cancel_identity',

  ME_FEEDBACK_PAGE_VIEW: 'me_feedback.page_view',
  ME_FEEDBACK_SUBMIT: 'me_feedback.submit',
  ME_FEEDBACK_SUBMIT_SUCCESS: 'me_feedback.submit_success',
  ME_FEEDBACK_SUBMIT_FAIL: 'me_feedback.submit_fail',

  ME_PERSONAL_INFO_PAGE_VIEW: 'me_personal_info.page_view',
  ME_PERSONAL_INFO_AVATAR_CHANGE: 'me_personal_info.avatar_change',
  ME_PERSONAL_INFO_NICKNAME_SAVE: 'me_personal_info.nickname_save',
  ME_PERSONAL_INFO_GENDER_CHANGE: 'me_personal_info.gender_change',
  ME_PERSONAL_INFO_BIRTHDAY_CHANGE: 'me_personal_info.birthday_change',
  ME_PERSONAL_INFO_REGION_CHANGE: 'me_personal_info.region_change',
  ME_PERSONAL_INFO_PHONE_CHANGE: 'me_personal_info.phone_change',

  ME_TASTE_PREFS_PAGE_VIEW: 'me_taste_prefs.page_view',
  ME_TASTE_PREFS_NAV_PROFILE_EDIT: 'me_taste_prefs.nav_profile_edit',
  ME_TASTE_PREFS_NAV_ONBOARDING: 'me_taste_prefs.nav_onboarding',
  ME_TASTE_PREFS_NAV_DIET_PREFS: 'me_taste_prefs.nav_diet_prefs',
  ME_TASTE_PREFS_NAV_BASIC_PROFILE: 'me_taste_prefs.nav_basic_profile',
  ME_TASTE_PREFS_NAV_RECOMMEND_SETTINGS: 'me_taste_prefs.nav_recommend_settings',
  ME_TASTE_PREFS_NAV_RECIPE_FAVORITES: 'me_taste_prefs.nav_recipe_favorites',

  ME_PROFILE_EDIT_PAGE_VIEW: 'me_profile_edit.page_view',
  ME_PROFILE_EDIT_SAVE: 'me_profile_edit.save',
  ME_PROFILE_EDIT_SAVE_SUCCESS: 'me_profile_edit.save_success',
  ME_PROFILE_EDIT_SAVE_FAIL: 'me_profile_edit.save_fail',

  ME_DIET_PREFS_PAGE_VIEW: 'me_diet_prefs.page_view',
  ME_DIET_PREFS_SAVE: 'me_diet_prefs.save',
  ME_DIET_PREFS_SAVE_SUCCESS: 'me_diet_prefs.save_success',
  ME_DIET_PREFS_SAVE_FAIL: 'me_diet_prefs.save_fail',

  ME_BASIC_PROFILE_PAGE_VIEW: 'me_basic_profile.page_view',
  ME_BASIC_PROFILE_SAVE: 'me_basic_profile.save',
  ME_BASIC_PROFILE_SAVE_SUCCESS: 'me_basic_profile.save_success',
  ME_BASIC_PROFILE_SAVE_FAIL: 'me_basic_profile.save_fail',

  ME_RECOMMEND_SETTINGS_PAGE_VIEW: 'me_recommend_settings.page_view',
  ME_RECOMMEND_SETTINGS_SAVE: 'me_recommend_settings.save',
  ME_RECOMMEND_SETTINGS_SAVE_SUCCESS: 'me_recommend_settings.save_success',
  ME_RECOMMEND_SETTINGS_SAVE_FAIL: 'me_recommend_settings.save_fail',
} as const

export type AnalyticsEventName = (typeof AnalyticsEvents)[keyof typeof AnalyticsEvents]

export type PageViewEventName = {
  [K in keyof typeof AnalyticsEvents]: (typeof AnalyticsEvents)[K] extends `${string}.page_view`
    ? (typeof AnalyticsEvents)[K]
    : never
}[keyof typeof AnalyticsEvents]
