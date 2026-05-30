import { request } from '@/api/http'
import { API_BASE_URL } from '@/constants'
import {
  AnalyticsEvents,
  type AnalyticsEventName,
  type PageViewEventName,
} from '@/lib/analyticsEvents'

export type { AnalyticsEventName, PageViewEventName }
export { AnalyticsEvents }

export interface AnalyticsTrackOptions {
  eventValue?: string | null
  meta?: Record<string, unknown>
}

interface PendingEvent {
  event_name: AnalyticsEventName
  event_value?: string | null
  meta?: Record<string, unknown>
}

const SESSION_STORAGE_KEY = 'miniapp_analytics_session_id'
const FAILED_QUEUE_KEY = 'miniapp_analytics_failed_queue'
const MAX_FAILED_QUEUE = 100
const FLUSH_DELAY_MS = 400
const BATCH_SIZE = 20

let pending: PendingEvent[] = []
let flushTimer: ReturnType<typeof setTimeout> | null = null
let retryScheduled = false

function analyticsEnabled(): boolean {
  return Boolean(API_BASE_URL.trim())
}

function getSessionId(): string {
  try {
    const existing = uni.getStorageSync(SESSION_STORAGE_KEY)
    if (typeof existing === 'string' && existing.trim()) {
      return existing.trim()
    }
  } catch {
    /* ignore */
  }
  const next = `${Date.now()}_${Math.random().toString(36).slice(2, 10)}`
  try {
    uni.setStorageSync(SESSION_STORAGE_KEY, next)
  } catch {
    /* ignore */
  }
  return next
}

function baseMeta(): Record<string, unknown> {
  try {
    const sys = uni.getSystemInfoSync()
    return {
      platform: sys.platform,
      model: sys.model,
      app_version: sys.appVersion,
      mp_version: sys.version,
    }
  } catch {
    return {}
  }
}

function readFailedQueue(): PendingEvent[] {
  try {
    const raw = uni.getStorageSync(FAILED_QUEUE_KEY)
    if (!Array.isArray(raw)) return []
    return raw.filter(
      (item): item is PendingEvent =>
        item != null &&
        typeof item === 'object' &&
        typeof (item as PendingEvent).event_name === 'string',
    )
  } catch {
    return []
  }
}

function writeFailedQueue(items: PendingEvent[]): void {
  try {
    uni.setStorageSync(FAILED_QUEUE_KEY, items.slice(-MAX_FAILED_QUEUE))
  } catch {
    /* ignore */
  }
}

function enqueueFailed(batch: PendingEvent[]): void {
  if (batch.length === 0) return
  writeFailedQueue([...readFailedQueue(), ...batch])
  scheduleRetryFailed()
}

function scheduleRetryFailed(): void {
  if (retryScheduled || !analyticsEnabled()) return
  retryScheduled = true
  setTimeout(() => {
    retryScheduled = false
    void retryFailedEvents()
  }, 3000)
}

async function retryFailedEvents(): Promise<void> {
  const failed = readFailedQueue()
  if (failed.length === 0) return
  writeFailedQueue([])
  pending.unshift(...failed)
  await flushAnalytics(true)
}

function scheduleFlush(): void {
  if (flushTimer) return
  flushTimer = setTimeout(() => {
    flushTimer = null
    void flushAnalytics(false)
  }, FLUSH_DELAY_MS)
}

async function postBatch(batch: PendingEvent[]): Promise<boolean> {
  await request<{ data?: { accepted?: number } }>({
    url: '/api/analytics/events',
    method: 'POST',
    data: {
      client_session_id: getSessionId(),
      events: batch.map((e) => ({
        event_name: e.event_name,
        event_value: e.event_value ?? null,
        meta: {
          ...baseMeta(),
          ...(e.meta ?? {}),
        },
      })),
    },
  })
  return true
}

async function flushAnalytics(fromRetry = false): Promise<void> {
  if (!analyticsEnabled()) return
  if (!fromRetry && pending.length === 0) {
    void retryFailedEvents()
    return
  }

  while (pending.length > 0) {
    const batch = pending.splice(0, BATCH_SIZE)
    try {
      await postBatch(batch)
    } catch {
      enqueueFailed(batch)
      break
    }
  }
}

export function trackAnalytics(eventName: AnalyticsEventName, options: AnalyticsTrackOptions = {}): void {
  if (!analyticsEnabled()) return
  pending.push({
    event_name: eventName,
    event_value: options.eventValue ?? null,
    meta: options.meta,
  })
  scheduleFlush()
}

export function trackPageView(eventName: PageViewEventName): void {
  trackAnalytics(eventName)
}

/** @deprecated 使用 trackPageView(AnalyticsEvents.HOME_PAGE_VIEW) */
export function trackHomePageView(): void {
  trackPageView(AnalyticsEvents.HOME_PAGE_VIEW)
}

/** @deprecated 使用 trackPageView(AnalyticsEvents.ME_PAGE_VIEW) */
export function trackMePageView(): void {
  trackPageView(AnalyticsEvents.ME_PAGE_VIEW)
}

const ME_NAV_PATH_EVENTS: Partial<Record<string, AnalyticsEventName>> = {
  '/pages/me/recommendation-preferences': AnalyticsEvents.ME_NAV_TASTE_PROFILE,
  '/pages/me/sponsorship': AnalyticsEvents.ME_NAV_SPONSORSHIP,
  '/pages/me/personal-info': AnalyticsEvents.ME_NAV_SETTINGS,
  '/pages/mall/orders': AnalyticsEvents.ME_NAV_ORDERS,
  '/pages/favorites/index': AnalyticsEvents.ME_NAV_FAVORITES,
  '/pages/recommendation-history/index': AnalyticsEvents.ME_NAV_RECOMMENDATION_HISTORY,
  '/pages/histories/index': AnalyticsEvents.ME_NAV_HISTORIES,
  '/pages/me/requirement-feedback': AnalyticsEvents.ME_NAV_REQUIREMENT_FEEDBACK,
}

const ME_TASTE_PREFS_NAV: Partial<Record<string, AnalyticsEventName>> = {
  '/pages/me/recommendation-profile-edit': AnalyticsEvents.ME_TASTE_PREFS_NAV_PROFILE_EDIT,
  '/pages/me/diet-preferences': AnalyticsEvents.ME_TASTE_PREFS_NAV_DIET_PREFS,
  '/pages/me/basic-profile': AnalyticsEvents.ME_TASTE_PREFS_NAV_BASIC_PROFILE,
  '/pages/me/recommend-settings': AnalyticsEvents.ME_TASTE_PREFS_NAV_RECOMMEND_SETTINGS,
  '/pages/recipe-favorites/index': AnalyticsEvents.ME_TASTE_PREFS_NAV_RECIPE_FAVORITES,
}

export function trackMeNav(path: string, meta?: Record<string, unknown>): void {
  const eventName = ME_NAV_PATH_EVENTS[path]
  if (eventName) {
    trackAnalytics(eventName, { meta })
  }
}

export function trackMeTastePrefsNav(path: string): void {
  const eventName = ME_TASTE_PREFS_NAV[path]
  if (eventName) {
    trackAnalytics(eventName)
  }
}

export function trackMeHistorySource(sourceType: string): void {
  trackAnalytics(AnalyticsEvents.ME_NAV_HISTORY_BY_SOURCE, {
    eventValue: sourceType,
    meta: { source_type: sourceType },
  })
}

void retryFailedEvents()
