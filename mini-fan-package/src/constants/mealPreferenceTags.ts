/** 与 Laravel UserDailyStatusMvp::flavorTagKeys / tabooTagKeys 一致 */

export const MEAL_FLAVOR_TAG_OPTIONS = [
  { value: 'light', label: '清淡' },
  { value: 'spicy_hot', label: '香辣' },
  { value: 'mild_spicy', label: '微辣' },
  { value: 'sweet_sour', label: '酸甜' },
  { value: 'home_style', label: '家常' },
  { value: 'strong', label: '重口' },
  { value: 'soup', label: '汤水' },
] as const

export const MEAL_TABOO_TAG_OPTIONS = [
  { value: 'coriander', label: '香菜' },
  { value: 'alliums', label: '葱姜蒜' },
  { value: 'seafood', label: '海鲜' },
  { value: 'organ', label: '内脏' },
  { value: 'peanut', label: '花生' },
  { value: 'none', label: '暂无' },
] as const

export type MealFlavorTagValue = (typeof MEAL_FLAVOR_TAG_OPTIONS)[number]['value']
export type MealTabooTagValue = (typeof MEAL_TABOO_TAG_OPTIONS)[number]['value']

const FLAVOR_SET = new Set<MealFlavorTagValue>(MEAL_FLAVOR_TAG_OPTIONS.map((o) => o.value))
const TABOO_SET = new Set<MealTabooTagValue>(MEAL_TABOO_TAG_OPTIONS.map((o) => o.value))

function isMealFlavorTagValue(value: string): value is MealFlavorTagValue {
  return FLAVOR_SET.has(value as MealFlavorTagValue)
}

function isMealTabooTagValue(value: string): value is MealTabooTagValue {
  return TABOO_SET.has(value as MealTabooTagValue)
}

export function normalizeFlavorTagsFromApi(raw: unknown): string[] {
  if (!Array.isArray(raw)) return []
  return raw.filter((x): x is MealFlavorTagValue => typeof x === 'string' && isMealFlavorTagValue(x))
}

export function normalizeTabooTagsFromApi(raw: unknown): string[] {
  if (!Array.isArray(raw)) return []
  const list = raw.filter((x): x is MealTabooTagValue => typeof x === 'string' && isMealTabooTagValue(x))
  if (list.includes('none')) return ['none']
  return list
}
