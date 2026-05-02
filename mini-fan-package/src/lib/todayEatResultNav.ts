import type { TodayEatResult } from '@/types/ai'

/** 与首页 `navigateTo` 结果页约定一致，避免魔法字符串分散 */
export const TODAY_EAT_RESULT_STORAGE_KEY = 'today_eat_result_nav_payload_v1'

export type TodayEatResultNavPayload = {
  result: TodayEatResult
  historyNote: string
  isFavorited: boolean
}
