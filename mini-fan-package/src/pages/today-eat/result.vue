<template>
  <view class="mp-page ter">
    <view v-if="!result" class="ter__empty">
      <text class="ter__empty-txt">未找到推荐内容</text>
      <button class="mp-btn-primary ter__empty-btn" @click="onEmptyBack">返回</button>
    </view>
    <scroll-view v-else scroll-y class="te__scroll te__scroll--padded">
      <view class="mp-card te__result">
        <view class="te__result-hero">
          <text class="te__result-hero-k">推荐完成</text>
          <text class="te__result-hero-title">你的此刻灵感已就绪</text>
          <text class="te__result-hero-sub">以下是依据你本次偏好生成的方案，可作为晚餐参考</text>
        </view>

        <TodayEatResultBody
          class="te__result-body"
          :cover-image="result.cover_image ?? null"
          :recommended-dish="recommendedDishDisplay"
          :main-tagline="mainTaglineDisplay"
          :tags="displayTags"
          :reason-text="reasonTextDisplay"
          :destiny-text="destinyTextDisplay"
          :alternatives="displayAlternatives"
          :alternatives-interactive="false"
          :cuisine="result.cuisine ?? null"
          :ingredients-text="ingredientsText"
          :recipe-content="result.content"
        />

        <view v-if="historyNote" class="te__history-banner">
          <text class="te__history-note">{{ historyNote }}</text>
        </view>

        <view class="te__fav-row">
          <button class="mp-btn-ghost te__fav-btn" :disabled="favoriteLoading" @click="onToggleFavorite">
            <text>{{ isFavorited ? '取消收藏' : '加入收藏' }}</text>
          </button>
        </view>

        <view class="te__recent">
          <text class="te__recent-title">最近吃么么记录</text>
          <view v-if="recentLoading" class="te__recent-empty">加载中…</view>
          <view v-else-if="recentRecords.length === 0" class="te__recent-empty">暂无记录</view>
          <view v-else class="te__recent-list">
            <view v-for="row in recentRecords" :key="row.id" class="te__recent-item">
              <view class="te__recent-main" @click="onReplayRecord(row.id)">
                <text class="te__recent-name">{{ row.result_title || '未命名结果' }}</text>
                <text class="te__recent-meta">{{ row.result_cuisine || '—' }} · {{ row.created_at || '' }}</text>
              </view>
            </view>
          </view>
        </view>
      </view>
      <button class="mp-btn-primary te__again te__again--hero" @click="onAgain">
        <text class="te__again-txt">再生成一次</text>
        <text class="te__again-go">→</text>
      </button>
    </scroll-view>
  </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import {
  isFavoriteRecipe,
  toggleFavoriteRecipe,
  BIZ_NEED_LARAVEL_AUTH,
  BIZ_NOT_CONFIGURED,
} from '@/api/biz'
import { fetchEatMemeRecords, type EatMemeRecordItem } from '@/api/eatMeme'
import TodayEatResultBody from '@/components/TodayEatResultBody.vue'
import { favoriteContentDigest } from '@/lib/favoriteDigest'
import { goLoginGate } from '@/lib/loginNav'
import { TODAY_EAT_RESULT_STORAGE_KEY } from '@/lib/todayEatResultNav'
import type { TodayEatResult } from '@/types/ai'
import { useAppMessages } from '@/composables/useAppMessages'
import { useAuth } from '@/composables/useAuth'

const msg = useAppMessages()
const { isLoggedIn } = useAuth()

const result = ref<TodayEatResult | null>(null)
const historyNote = ref('')
const isFavorited = ref(false)
const favoriteLoading = ref(false)
const recentRecords = ref<EatMemeRecordItem[]>([])
const recentLoading = ref(false)

function hydrateFromStorage() {
  try {
    const raw = uni.getStorageSync(TODAY_EAT_RESULT_STORAGE_KEY)
    if (typeof raw !== 'string' || !raw.trim()) {
      return
    }
    const p = JSON.parse(raw) as {
      result?: TodayEatResult
      historyNote?: string
      isFavorited?: boolean
    }
    try {
      uni.removeStorageSync(TODAY_EAT_RESULT_STORAGE_KEY)
    } catch {
      /* ignore */
    }
    if (p?.result && typeof p.result.content === 'string' && p.result.title) {
      result.value = p.result
      historyNote.value = typeof p.historyNote === 'string' ? p.historyNote : ''
      isFavorited.value = Boolean(p.isFavorited)
    }
  } catch {
    /* ignore */
  }
}

onShow(() => {
  hydrateFromStorage()
  void loadRecentRecords()
})

function onEmptyBack() {
  uni.navigateBack()
}

const ingredientsText = computed(() => {
  const ing = result.value?.ingredients
  if (!ing?.length) return '—'
  return ing.join('、')
})

const displayTags = computed(() => {
  const t = result.value?.tags
  if (!Array.isArray(t)) return []
  return t.filter((x) => typeof x === 'string' && x.trim()).slice(0, 4)
})

const displayAlternatives = computed(() => {
  const a = result.value?.alternatives
  if (!Array.isArray(a)) return []
  return a.filter((x) => typeof x === 'string' && x.trim())
})

const recommendedDishDisplay = computed(() => {
  const r = result.value?.recommended_dish
  if (typeof r === 'string' && r.trim()) return r.trim()
  return (result.value?.title || '').trim()
})

const mainTaglineDisplay = computed(() => {
  const tag = displayTags.value[0]
  if (tag) return `${tag}——今晚的主角就是它。`
  return '和你今天选的口味与状态相称，下面是可以直接照做的参考。'
})

const reasonTextDisplay = computed(() => {
  const s = result.value?.reason_text
  const t = typeof s === 'string' ? s.trim() : ''
  if (t) return t
  return '本次结合你的偏好生成；具体步骤与用料见下方「制作参考」。'
})

const destinyTextDisplay = computed(() => {
  const s = result.value?.destiny_text
  return typeof s === 'string' ? s.trim() : ''
})

async function loadRecentRecords() {
  recentLoading.value = true
  try {
    recentRecords.value = await fetchEatMemeRecords(1, 3)
  } catch {
    recentRecords.value = []
  } finally {
    recentLoading.value = false
  }
}

async function onToggleFavorite() {
  if (!result.value?.title || !result.value?.content) return
  if (!isLoggedIn.value) {
    goLoginGate('/pages/today-eat/result')
    return
  }
  if (favoriteLoading.value) return

  favoriteLoading.value = true
  try {
    const sid = favoriteContentDigest(result.value.title, result.value.content)
    const { favorited } = await toggleFavoriteRecipe({
      source_type: 'today_eat',
      source_id: sid,
      title: result.value.title,
      cuisine: result.value.cuisine ?? null,
      ingredients: result.value.ingredients ?? [],
      recipe_content: result.value.content,
      image_url: null,
    })
    isFavorited.value = favorited
    if (favorited) msg.toastFavoriteSuccess()
    else msg.toastFavoriteCancel()
  } catch (e: unknown) {
    const err = e as Error & { code?: string }
    if (err.code === BIZ_NEED_LARAVEL_AUTH || err.message === BIZ_NEED_LARAVEL_AUTH) {
      msg.toastSaveFailed('请先微信一键登录')
    } else if (err.code === BIZ_NOT_CONFIGURED || err.message === BIZ_NOT_CONFIGURED) {
      msg.toastSaveFailed('收藏功能暂不可用')
    } else {
      msg.toastSaveFailed(err.message || '收藏失败')
    }
  } finally {
    favoriteLoading.value = false
  }
}

function onReplayRecord(id: number) {
  const row = recentRecords.value.find((x) => x.id === id)
  if (!row || !row.result_content || !row.result_title) return
  result.value = {
    title: row.result_title,
    cuisine: row.result_cuisine ?? undefined,
    ingredients: row.result_ingredients ?? [],
    content: row.result_content,
    history_saved: false,
    tags: undefined,
    reason_text: undefined,
    destiny_text: undefined,
    alternatives: undefined,
    cover_image: undefined,
    recommended_dish: undefined,
  }
  historyNote.value = ''
  void syncFavoriteState()
}

async function syncFavoriteState() {
  if (!result.value?.title || !result.value?.content) return
  if (!isLoggedIn.value) {
    isFavorited.value = false
    return
  }
  try {
    const sid = favoriteContentDigest(result.value.title, result.value.content)
    isFavorited.value = await isFavoriteRecipe({
      source_type: 'today_eat',
      source_id: sid,
    })
  } catch {
    isFavorited.value = false
  }
}

function onAgain() {
  uni.navigateBack()
}
</script>

<style lang="scss" scoped>
@import '@/uni.scss';

$te-bg: #f5f6fa;

.ter {
  min-height: 100vh;
  background: $te-bg;
  padding-bottom: calc(32rpx + env(safe-area-inset-bottom));
}

.ter__empty {
  padding: 120rpx 48rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 32rpx;
}

.ter__empty-txt {
  font-size: 28rpx;
  color: $mp-text-secondary;
}

.ter__empty-btn {
  min-width: 280rpx;
}

.te__scroll {
  max-height: 100vh;
  box-sizing: border-box;
}

.te__scroll--padded {
  max-height: 100vh;
  box-sizing: border-box;
  padding: 16rpx 24rpx 0;
  padding-bottom: calc(32rpx + env(safe-area-inset-bottom));
}

.te__result {
  padding: 0;
  overflow: hidden;
}

.te__result-hero {
  padding: 36rpx 28rpx 32rpx;
  text-align: center;
  background: linear-gradient(180deg, rgba(243, 236, 255, 0.45) 0%, #fff 100%);
  border-bottom: 1rpx solid $mp-border;
}

.te__result-hero-k {
  font-size: 22rpx;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: $mp-accent;
}

.te__result-hero-title {
  display: block;
  margin-top: 12rpx;
  font-size: 32rpx;
  font-weight: 800;
  color: $mp-text-primary;
}

.te__result-hero-sub {
  display: block;
  margin-top: 12rpx;
  font-size: 26rpx;
  line-height: 1.5;
  color: $mp-text-secondary;
  padding: 0 16rpx;
}

.te__result-body {
  padding: 28rpx 28rpx 32rpx;
}

.te__history-banner {
  padding: 20rpx 28rpx 28rpx;
  background: #fff;
}

.te__history-note {
  font-size: 24rpx;
  line-height: 1.5;
  color: $mp-text-secondary;
  padding: 16rpx 20rpx;
  border-radius: 12rpx;
  background: #f9fafb;
  border: 1rpx dashed #e5e7eb;
}

.te__fav-row {
  padding: 0 28rpx 24rpx;
  background: #fff;
}

.te__fav-btn {
  width: 100%;
}

.te__recent {
  margin: 4rpx 28rpx 22rpx;
  padding-top: 16rpx;
  border-top: 1rpx dashed $mp-border;
}

.te__recent-title {
  font-size: 26rpx;
  font-weight: 700;
  color: $mp-text-primary;
}

.te__recent-empty {
  margin-top: 10rpx;
  font-size: 24rpx;
  color: $mp-text-muted;
}

.te__recent-list {
  margin-top: 10rpx;
  display: flex;
  flex-direction: column;
  gap: 10rpx;
}

.te__recent-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12rpx;
  padding: 14rpx 16rpx;
  border-radius: 14rpx;
  background: #f6f7fb;
}

.te__recent-main {
  display: flex;
  flex-direction: column;
  gap: 4rpx;
  min-width: 0;
  flex: 1;
}

.te__recent-name {
  font-size: 26rpx;
  color: $mp-text-primary;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.te__recent-meta {
  font-size: 22rpx;
  color: $mp-text-muted;
}

.te__again {
  margin-top: 24rpx;
  margin-bottom: 48rpx;
}

.te__again--hero {
  display: flex !important;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 12rpx;
  padding-top: 30rpx !important;
  padding-bottom: 30rpx !important;
}

.te__again-txt {
  font-weight: 800;
}

.te__again-go {
  font-size: 32rpx;
  font-weight: 800;
}
</style>
