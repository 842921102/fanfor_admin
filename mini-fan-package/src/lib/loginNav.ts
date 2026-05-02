import { STORAGE_POST_LOGIN_REDIRECT } from '@/constants'

const TAB_BAR_PATHS = new Set([
  '/pages/today-eat/index',
  '/pages/index/index',
  '/pages/plaza/index',
  '/pages/inspiration/index',
  '/pages/me/index',
])

/**
 * 登录成功后的回跳（与独立登录页 `pages/login/index` 行为一致）。
 * `redirectPath` 须为已解码的路径，例如 `/pages/favorites/index` 或带 query 的完整 path。
 */
export function navigateAfterLogin(redirectPath: string) {
  const target = redirectPath.trim()
  if (target && target.startsWith('/pages/')) {
    if (TAB_BAR_PATHS.has(target)) {
      uni.switchTab({ url: target })
      return
    }
    uni.redirectTo({ url: target })
    return
  }
  uni.navigateBack({
    fail: () => {
      uni.switchTab({ url: '/pages/me/index' })
    },
  })
}

/** 写入待回跳路径并进入「我的」登录入口（避免 `navigateTo` 登录页再被立刻 `switchTab` 导致白屏）。 */
export function goLoginGate(redirectPath?: string) {
  const path = (redirectPath ?? '').trim()
  try {
    if (path) {
      uni.setStorageSync(STORAGE_POST_LOGIN_REDIRECT, path)
    } else {
      uni.removeStorageSync(STORAGE_POST_LOGIN_REDIRECT)
    }
  } catch {
    /* ignore */
  }
  uni.switchTab({ url: '/pages/me/index' })
}

/** 读取并清除待回跳路径（「我的」内联登录成功后调用）。 */
export function takePostLoginRedirect(): string {
  try {
    const v = uni.getStorageSync(STORAGE_POST_LOGIN_REDIRECT)
    uni.removeStorageSync(STORAGE_POST_LOGIN_REDIRECT)
    return typeof v === 'string' ? v.trim() : ''
  } catch {
    return ''
  }
}
