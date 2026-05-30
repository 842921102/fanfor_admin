import { onShow } from '@dcloudio/uni-app'
import { trackPageView, type PageViewEventName } from '@/lib/analytics'

/** 页面 onShow 时自动上报 page_view */
export function usePageAnalytics(pageViewEvent: PageViewEventName): void {
  onShow(() => {
    trackPageView(pageViewEvent)
  })
}
