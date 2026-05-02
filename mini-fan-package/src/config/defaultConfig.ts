import type { PlazaEntryConfig } from '@/types/plazaConfig'
import { PLAZA_ENTRIES_DEFAULT } from '@/config/plazaEntriesDefault'

/**
 * 本地默认配置（单一事实来源）
 *
 * - `AppConfig` / `APP_CONFIG_DEFAULT`：完整默认值
 * - `APP_CONFIG_STRING_KEYS` / `APP_CONFIG_BOOLEAN_KEYS`：与远端合并时参与覆盖的字段清单（api/config.ts）
 * - `pickAppConfigPartial` / `parseBooleanLike`：扁平解析与布尔归一（configRemoteMap、plazaEntriesMerge 复用）
 * - `cloneAppConfigDefault`：首屏与失败兜底用的深拷贝快照（避免与常量对象共享 plaza_entries 引用）
 *
 * 远端拉取、合并、缓存：`src/api/config.ts`。JSON → 部分 AppConfig：`src/api/configRemoteMap.ts`。
 */
export interface AppConfig {
  home_title: string
  home_subtitle: string
  /** 首页运营：Banner */
  home_banner_title: string
  home_banner_subtitle: string
  show_home_banner: boolean
  /** 首页运营：推荐区 */
  home_recommend_title: string
  home_recommend_subtitle: string
  show_home_recommend: boolean
  /** 首页运营：热门玩法 */
  home_hot_title: string
  home_hot_subtitle: string
  show_home_hot: boolean

  plaza_title: string
  plaza_subtitle: string
  /** 功能广场入口（远端 plaza_entries 与默认按 key 合并） */
  plaza_entries: PlazaEntryConfig[]
  profile_title: string
  profile_subtitle: string
  profile_guest_title: string
  profile_guest_subtitle: string

  /** 收藏页 */
  favorites_title: string
  favorites_subtitle: string
  favorites_empty_title: string
  favorites_empty_subtitle: string
  favorites_empty_button_text: string
  /** 历史页 */
  histories_title: string
  histories_subtitle: string
  histories_empty_title: string
  histories_empty_subtitle: string
  histories_empty_button_text: string
  /** 最近区块（有数据时顶部展示前 3 条，下列表从第 4 条起） */
  show_recent_favorites: boolean
  show_recent_histories: boolean

  /** 登录 / 访客引导 */
  login_prompt_title: string
  login_prompt_subtitle: string
  login_button_text: string
  /** Toast */
  toast_favorite_success: string
  toast_favorite_cancel: string
  toast_history_deleted: string
  toast_favorite_deleted: string
  toast_save_success: string
  toast_save_failed: string
  toast_load_failed: string
  /** 通用空状态（未登录等） */
  common_empty_title: string
  common_empty_subtitle: string
  common_empty_button_text: string

  /** 帮忙选择（首页 Tab / 二级页；可由远端运营配置覆盖，便于接入「AI 文案」投放） */
  help_choose_landing_title: string
  help_choose_landing_subtitle: string
  help_choose_landing_cta: string
  help_choose_dishes_placeholder: string
  help_choose_min_dishes_toast: string
  help_choose_primary_pick_btn: string
  help_choose_result_today_label: string
  help_choose_result_reason_label: string
  help_choose_result_alternatives_label: string
  help_choose_btn_reroll: string
  help_choose_btn_reset: string
  help_choose_btn_save: string
  help_choose_btn_share: string
  help_choose_share_title_prefix: string
}

export const APP_CONFIG_STRING_KEYS: (keyof AppConfig)[] = [
  'home_title',
  'home_subtitle',
  'home_banner_title',
  'home_banner_subtitle',
  'home_recommend_title',
  'home_recommend_subtitle',
  'home_hot_title',
  'home_hot_subtitle',
  'plaza_title',
  'plaza_subtitle',
  'profile_title',
  'profile_subtitle',
  'profile_guest_title',
  'profile_guest_subtitle',
  'favorites_title',
  'favorites_subtitle',
  'favorites_empty_title',
  'favorites_empty_subtitle',
  'favorites_empty_button_text',
  'histories_title',
  'histories_subtitle',
  'histories_empty_title',
  'histories_empty_subtitle',
  'histories_empty_button_text',
  'login_prompt_title',
  'login_prompt_subtitle',
  'login_button_text',
  'toast_favorite_success',
  'toast_favorite_cancel',
  'toast_history_deleted',
  'toast_favorite_deleted',
  'toast_save_success',
  'toast_save_failed',
  'toast_load_failed',
  'common_empty_title',
  'common_empty_subtitle',
  'common_empty_button_text',
  'help_choose_landing_title',
  'help_choose_landing_subtitle',
  'help_choose_landing_cta',
  'help_choose_dishes_placeholder',
  'help_choose_min_dishes_toast',
  'help_choose_primary_pick_btn',
  'help_choose_result_today_label',
  'help_choose_result_reason_label',
  'help_choose_result_alternatives_label',
  'help_choose_btn_reroll',
  'help_choose_btn_reset',
  'help_choose_btn_save',
  'help_choose_btn_share',
  'help_choose_share_title_prefix',
]

export const APP_CONFIG_BOOLEAN_KEYS: (keyof AppConfig)[] = [
  'show_home_banner',
  'show_home_recommend',
  'show_home_hot',
  'show_recent_favorites',
  'show_recent_histories',
]

export const APP_CONFIG_DEFAULT: AppConfig = {
  home_title: '饭否',
  home_subtitle: '此刻想吃，让 AI 帮你出主意',

  home_banner_title: '此刻灵感',
  home_banner_subtitle: '用 AI 搭配一顿好饭',
  show_home_banner: true,

  home_recommend_title: '为你推荐',
  home_recommend_subtitle: '填写口味，获取个性化菜谱',
  show_home_recommend: true,

  home_hot_title: '热门玩法',
  home_hot_subtitle: '收藏、历史与功能广场一键直达',
  show_home_hot: true,

  plaza_title: '功能广场',
  plaza_subtitle: '第一阶段已开放入口',
  plaza_entries: PLAZA_ENTRIES_DEFAULT.map((e) => ({ ...e })),
  profile_title: '账号信息',
  profile_subtitle: '',
  profile_guest_title: '欢迎来到饭否',
  profile_guest_subtitle: '登录后可在小程序同步收藏与历史记录',

  favorites_title: '收藏',
  favorites_subtitle: '收藏已同步至饭否服务器，可与管理后台联查',
  favorites_empty_title: '暂无收藏',
  favorites_empty_subtitle: '在此刻想吃、家常好菜等结果页加入收藏后会显示在这里',
  favorites_empty_button_text: '去此刻想吃',

  histories_title: '历史',
  histories_subtitle: '与 Web 同步的生成记录',
  histories_empty_title: '暂无历史',
  histories_empty_subtitle: '在 Web 端生成菜谱后会写入历史',
  histories_empty_button_text: '去此刻想吃',

  show_recent_favorites: true,
  show_recent_histories: true,

  login_prompt_title: '登录饭否账号',
  login_prompt_subtitle: '使用与 Web 相同的邮箱与密码，即可同步收藏与历史',
  login_button_text: '登录',

  toast_favorite_success: '已加入收藏',
  toast_favorite_cancel: '已取消收藏',
  toast_history_deleted: '历史已删除',
  toast_favorite_deleted: '收藏已删除',
  toast_save_success: '保存成功',
  toast_save_failed: '保存失败',
  toast_load_failed: '加载失败',

  common_empty_title: '暂无内容',
  common_empty_subtitle: '登录后可同步云端收藏与历史',
  common_empty_button_text: '去登录',

  help_choose_landing_title: '帮忙选择',
  help_choose_landing_subtitle: '几个都想吃？怎么选',
  help_choose_landing_cta: '来帮你拍板',
  help_choose_dishes_placeholder: '例如：火锅、烤肉、酸菜鱼、麻辣烫、日料',
  help_choose_min_dishes_toast: '至少输入 2 个菜，饭否才好帮你选。',
  help_choose_primary_pick_btn: '帮我选一个',
  help_choose_result_today_label: '今日推荐',
  help_choose_result_reason_label: '推荐理由',
  help_choose_result_alternatives_label: '备选推荐',
  help_choose_btn_reroll: '再选一次',
  help_choose_btn_reset: '清空重填',
  help_choose_btn_save: '保存结果',
  help_choose_btn_share: '分享给朋友',
  help_choose_share_title_prefix: '今日推荐：',
}

/** 与 `APP_CONFIG_DEFAULT` 等价的可变副本（含 plaza_entries 逐项拷贝） */
export function cloneAppConfigDefault(): AppConfig {
  return {
    ...APP_CONFIG_DEFAULT,
    plaza_entries: APP_CONFIG_DEFAULT.plaza_entries.map((e) => ({ ...e })),
  }
}

/** 解析 JSON / 表单里常见的布尔写法 */
export function parseBooleanLike(v: unknown): boolean | undefined {
  if (typeof v === 'boolean') return v
  if (typeof v === 'number' && (v === 0 || v === 1)) return v === 1
  if (typeof v === 'string') {
    const s = v.trim().toLowerCase()
    if (s === 'true' || s === '1' || s === 'yes' || s === 'on') return true
    if (s === 'false' || s === '0' || s === 'no' || s === 'off') return false
  }
  return undefined
}

/** 从任意对象中挑出已知的 AppConfig 字段（字符串 + 布尔开关） */
export function pickAppConfigPartial(
  raw: Record<string, unknown>,
): Partial<AppConfig> {
  const out: Partial<AppConfig> = {}
  for (const k of APP_CONFIG_STRING_KEYS) {
    const v = raw[k]
    if (typeof v === 'string') out[k] = v
  }
  for (const k of APP_CONFIG_BOOLEAN_KEYS) {
    const b = parseBooleanLike(raw[k])
    if (b !== undefined) out[k] = b
  }
  return out
}
