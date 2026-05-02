/**
 * 「帮忙选择」：菜名解析、本地随机选菜、按场景/偏好拼推荐理由（可配合远端运营文案由页面注入）。
 */

export type HelpChooseSceneId = 'solo' | 'friends' | 'couple' | 'family' | 'colleagues'

export type HelpChoosePrefId = 'save_money' | 'full_stomach' | 'light' | 'spicy'

export const HELP_CHOOSE_SCENES: { id: HelpChooseSceneId; label: string }[] = [
  { id: 'solo', label: '一个人吃' },
  { id: 'friends', label: '朋友聚餐' },
  { id: 'couple', label: '情侣约会' },
  { id: 'family', label: '家庭吃饭' },
  { id: 'colleagues', label: '同事聚餐' },
]

export const HELP_CHOOSE_PREFS: { id: HelpChoosePrefId; label: string }[] = [
  { id: 'save_money', label: '想省钱' },
  { id: 'full_stomach', label: '想吃饱' },
  { id: 'light', label: '清淡一点' },
  { id: 'spicy', label: '想吃辣' },
]

const SCENE_REASON: Record<HelpChooseSceneId, string> = {
  friends: '朋友聚餐更适合热闹、好分食的选项，这道菜不容易让场面冷下来。',
  solo: '一个人吃时，方便落地、满足感更重要，这道相对好把握，也少些纠结。',
  couple: '情侣约会更看重氛围与节奏，这道吃起来轻松，也适合边吃边聊。',
  family: '家庭吃饭求稳，这道口味相对中庸，老少皆宜、接受度更高。',
  colleagues: '同事聚餐要兼顾口味与场面，这道不过分挑人，小聚也体面。',
}

const PREF_REASON: Record<HelpChoosePrefId, string> = {
  save_money: '若想省一点预算，这道在性价比上更容易「值回票价」。',
  full_stomach: '想吃饱时，这道饱腹感更实在，不容易吃完还饿。',
  light: '想清淡一点时，这道负担更小，吃完更舒服。',
  spicy: '想吃辣时，这道更过瘾，口味记忆点也更鲜明。',
}

/** 中文逗号、英文逗号、顿号、空白与换行分隔 */
export function parseDishNames(raw: string): string[] {
  const s = raw.trim()
  if (!s) return []
  const parts = s.split(/[\s,，、\n\r]+/u)
  const out: string[] = []
  const seen = new Set<string>()
  for (const p of parts) {
    const t = p.trim().replace(/^[\s"'「」]+|[\s"'」]+$/g, '')
    if (!t) continue
    if (t.length > 40) continue
    const key = t.toLowerCase()
    if (seen.has(key)) continue
    seen.add(key)
    out.push(t)
    if (out.length >= 40) break
  }
  return out
}

function shuffleInPlace<T>(arr: T[]): void {
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
}

export function pickRandomDishAndAlternatives(dishes: string[]): {
  picked: string
  alternatives: string[]
} {
  const list = [...dishes]
  shuffleInPlace(list)
  const picked = list[0]!
  const rest = list.slice(1)
  const alternatives = rest.slice(0, Math.min(2, rest.length))
  return { picked, alternatives }
}

/**
 * 第一句来自场景；第二句来自用户勾选的偏好（按勾选顺序取第一条），无则给温和兜底。
 */
export function buildHelpChooseReason(
  sceneId: HelpChooseSceneId,
  prefs: HelpChoosePrefId[],
  _picked: string,
): string {
  const line1 = SCENE_REASON[sceneId] ?? SCENE_REASON.solo
  let line2 = ''
  for (const p of prefs) {
    const t = PREF_REASON[p]
    if (t) {
      line2 = t
      break
    }
  }
  if (!line2) {
    line2 = '今天不妨听饭否一次，换换口味也不错。'
  }
  return `${line1} ${line2}`
}
