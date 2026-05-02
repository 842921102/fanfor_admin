import { request } from '@/api/http'
import type { HelpChoosePrefId, HelpChooseSceneId } from '@/lib/helpChoosePick'

export interface SaveHelpChoosePayload {
  dishes: string[]
  scene_id: HelpChooseSceneId
  preferences: HelpChoosePrefId[]
  picked: string
  alternatives: string[]
  reason: string
}

export async function saveHelpChooseRecord(payload: SaveHelpChoosePayload): Promise<{ id: number }> {
  const raw = await request<{ data?: { id?: number } }>({
    url: '/api/me/help-choose',
    method: 'POST',
    data: payload as unknown as Record<string, unknown>,
  })
  const id = raw?.data?.id
  if (typeof id !== 'number') {
    throw new Error('保存失败：未返回记录编号')
  }
  return { id }
}
