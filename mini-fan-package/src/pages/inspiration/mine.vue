<template>
  <view class="ins-mine">
    <view class="ins-mine__hdr">
      <view class="ins-mine__tabs">
        <view
          v-for="t in mainTabs"
          :key="t.key"
          class="ins-mine__tab"
          :class="{ 'ins-mine__tab--on': tab === t.key }"
          @click="switchTab(t.key)"
        >{{ t.label }}</view>
      </view>
    </view>

    <scroll-view
      class="ins-mine__scroll"
      scroll-y
      :style="scrollStyle"
    >
      <view v-if="needLogin" class="ins-mine__gate">
        <text class="ins-mine__gate-t">登录后查看「我的灵感」</text>
        <text class="ins-mine__gate-s">灵感、评论、收藏与赞过记录均与账号同步</text>
        <button type="button" class="mp-btn-primary ins-mine__gate-btn" @click="onTapLogin">去登录</button>
      </view>

      <view v-else-if="loading" class="ins-mine__loading">
        <text class="ins-mine__loading-t">加载中…</text>
      </view>

      <template v-else-if="tab === 'comments'">
        <view v-if="!listComments.length" class="ins-mine__empty">
          <text class="ins-mine__empty-t">暂无评论动态</text>
          <text class="ins-mine__empty-s">你发出的评论和他人对你帖子的评论会出现在这里</text>
        </view>
        <view v-else class="ins-mine__acts">
          <view
            v-for="row in listComments"
            :key="row.id"
            class="ins-mine__act"
            @click="openPost(row.postId)"
          >
            <image
              v-if="row.postCoverImage"
              class="ins-mine__act-thumb"
              :src="row.postCoverImage"
              mode="aspectFill"
            />
            <view v-else class="ins-mine__act-thumb ins-mine__act-thumb--ph">
              <text class="ins-mine__act-ph">图</text>
            </view>
            <view class="ins-mine__act-body">
              <view class="ins-mine__act-tags">
                <text class="ins-mine__act-tag" :class="row.direction === 'sent' ? 'ins-mine__act-tag--sent' : 'ins-mine__act-tag--in'">
                  {{ row.direction === 'sent' ? '我评的' : '收到评论' }}
                </text>
              </view>
              <text class="ins-mine__act-title">{{ row.postTitle || '无标题' }}</text>
              <text class="ins-mine__act-excerpt">{{ row.commentExcerpt }}</text>
              <text class="ins-mine__act-meta">{{ row.counterpartNickname }} · {{ formatListTime(row.createdAt) }}</text>
            </view>
          </view>
        </view>
      </template>

      <template v-else>
        <view v-if="!gridList.length" class="ins-mine__empty">
          <text class="ins-mine__empty-t">{{ emptyTitle }}</text>
          <button v-if="tab === 'posts'" type="button" class="mp-btn-primary ins-mine__empty-btn" @click="goPublish">去发布</button>
        </view>
        <view v-else class="ins-mine__cols">
          <view class="ins-mine__col">
            <WaterfallCard
              v-for="item in leftGrid"
              :key="item.id"
              :item="item"
              @open="openPost(item.id)"
              @favorite="onToggleFavorite(item.id)"
              @like="onToggleLike(item.id)"
            />
          </view>
          <view class="ins-mine__col">
            <WaterfallCard
              v-for="item in rightGrid"
              :key="item.id"
              :item="item"
              @open="openPost(item.id)"
              @favorite="onToggleFavorite(item.id)"
              @like="onToggleLike(item.id)"
            />
          </view>
        </view>
      </template>
    </scroll-view>
  </view>
</template>

<script setup lang="ts">
import { computed, getCurrentInstance, nextTick, ref } from 'vue'
import { onPullDownRefresh, onReady, onShow } from '@dcloudio/uni-app'
import { HttpError } from '@/api/http'
import {
  favoriteInspiration,
  getMyCollectedInspirations,
  getMyInspirationCommentActivity,
  getMyInspirations,
  getMyLikedInspirations,
  likeInspiration,
} from '@/api/inspiration'
import WaterfallCard from '@/components/inspiration/WaterfallCard.vue'
import { getToken, hydrateFromStorage } from '@/composables/useAuth'
import { goLoginGate } from '@/lib/loginNav'
import type { InspirationCommentActivity, InspirationItem } from '@/types/inspiration'
import { formatListTime } from '@/utils/dateFormat'

function errMessage(e: unknown, fallback: string): string {
  if (e instanceof Error && e.message) return e.message
  if (typeof e === 'string' && e.trim()) return e.trim()
  return fallback
}

type MineTab = 'posts' | 'comments' | 'collects' | 'likes'

const mainTabs: Array<{ key: MineTab; label: string }> = [
  { key: 'posts', label: '灵感' },
  { key: 'comments', label: '评论' },
  { key: 'collects', label: '收藏' },
  { key: 'likes', label: '赞过' },
]

const tab = ref<MineTab>('posts')
const needLogin = ref(false)
const loading = ref(true)
const listPosts = ref<InspirationItem[]>([])
const listCollects = ref<InspirationItem[]>([])
const listLikes = ref<InspirationItem[]>([])
const listComments = ref<InspirationCommentActivity[]>([])

const scrollH = ref(520)
const scrollStyle = computed(() => ({
  height: `${scrollH.value}px`,
  boxSizing: 'border-box' as const,
}))

const gridList = computed<InspirationItem[]>(() => {
  if (tab.value === 'posts') return listPosts.value
  if (tab.value === 'collects') return listCollects.value
  if (tab.value === 'likes') return listLikes.value
  return []
})

const leftGrid = computed(() => gridList.value.filter((_, i) => i % 2 === 0))
const rightGrid = computed(() => gridList.value.filter((_, i) => i % 2 === 1))

const emptyTitle = computed(() => {
  if (tab.value === 'posts') return '还没有发布灵感'
  if (tab.value === 'collects') return '暂无收藏内容'
  if (tab.value === 'likes') return '暂无赞过的内容'
  return '暂无'
})

/** 避免并发请求后「旧请求」覆盖新 Tab 的数据 */
let loadSeq = 0

function measureScroll() {
  const sys = uni.getSystemInfoSync()
  const wh = Number(sys.windowHeight) || 667
  const inst = getCurrentInstance()?.proxy
  const q = inst ? uni.createSelectorQuery().in(inst) : uni.createSelectorQuery()
  q.select('.ins-mine__hdr')
    .boundingClientRect((rect) => {
      if (rect && typeof rect.bottom === 'number') {
        scrollH.value = Math.max(200, wh - rect.bottom - 8)
      } else {
        scrollH.value = Math.max(200, wh - 200)
      }
    })
    .exec()
}

function switchTab(k: MineTab) {
  tab.value = k
  void loadCurrent()
}

function onTapLogin() {
  goLoginGate('/pages/inspiration/mine')
}

async function loadCurrent() {
  hydrateFromStorage()
  if (!getToken().trim()) {
    needLogin.value = true
    loading.value = false
    listPosts.value = []
    listCollects.value = []
    listLikes.value = []
    listComments.value = []
    nextTick(() => measureScroll())
    return
  }

  needLogin.value = false
  const seq = ++loadSeq
  const whichTab = tab.value
  loading.value = true
  try {
    if (whichTab === 'posts') {
      const data = await getMyInspirations()
      if (seq !== loadSeq) return
      listPosts.value = data
    } else if (whichTab === 'comments') {
      const data = await getMyInspirationCommentActivity()
      if (seq !== loadSeq) return
      listComments.value = data
    } else if (whichTab === 'collects') {
      const data = await getMyCollectedInspirations()
      if (seq !== loadSeq) return
      listCollects.value = data
    } else {
      const data = await getMyLikedInspirations()
      if (seq !== loadSeq) return
      listLikes.value = data
    }
  } catch (e: unknown) {
    if (seq !== loadSeq) return
    if (e instanceof HttpError && e.statusCode === 401) {
      needLogin.value = true
      uni.showToast({ title: '请先登录', icon: 'none' })
      goLoginGate('/pages/inspiration/mine')
      return
    }
    uni.showToast({ title: errMessage(e, '加载失败'), icon: 'none' })
  } finally {
    if (seq === loadSeq) {
      loading.value = false
      nextTick(() => measureScroll())
    }
  }
}

function openPost(id: string) {
  uni.navigateTo({ url: `/pages/inspiration/detail?id=${encodeURIComponent(id)}` })
}

function goPublish() {
  uni.navigateTo({ url: '/pages/inspiration/publish' })
}

function patchInList(arr: InspirationItem[], id: string, next: InspirationItem): InspirationItem[] {
  return arr.map((x) => (x.id === id ? next : x))
}

async function onToggleFavorite(id: string) {
  if (needLogin.value || !getToken().trim()) {
    onTapLogin()
    return
  }
  try {
    const updated = await favoriteInspiration(id)
    if (!updated) return
    listPosts.value = patchInList(listPosts.value, id, updated)
    listCollects.value = patchInList(listCollects.value, id, updated)
    listLikes.value = patchInList(listLikes.value, id, updated)
    if (tab.value === 'collects' && !updated.isFavorited) {
      listCollects.value = listCollects.value.filter((x) => x.id !== id)
    }
  } catch (e: unknown) {
    if (e instanceof HttpError && e.statusCode === 401) {
      uni.showToast({ title: '请先登录', icon: 'none' })
      goLoginGate('/pages/inspiration/mine')
      return
    }
    uni.showToast({ title: errMessage(e, '操作失败'), icon: 'none' })
  }
}

async function onToggleLike(id: string) {
  if (needLogin.value || !getToken().trim()) {
    onTapLogin()
    return
  }
  try {
    const updated = await likeInspiration(id)
    if (!updated) return
    listPosts.value = patchInList(listPosts.value, id, updated)
    listCollects.value = patchInList(listCollects.value, id, updated)
    listLikes.value = patchInList(listLikes.value, id, updated)
    if (tab.value === 'likes' && !updated.isLiked) {
      listLikes.value = listLikes.value.filter((x) => x.id !== id)
    }
  } catch (e: unknown) {
    if (e instanceof HttpError && e.statusCode === 401) {
      uni.showToast({ title: '请先登录', icon: 'none' })
      goLoginGate('/pages/inspiration/mine')
      return
    }
    uni.showToast({ title: errMessage(e, '操作失败'), icon: 'none' })
  }
}

onReady(() => {
  nextTick(() => measureScroll())
})

onShow(() => void loadCurrent())

onPullDownRefresh(() => {
  void (async () => {
    await loadCurrent()
    uni.stopPullDownRefresh()
  })()
})
</script>

<style lang="scss" scoped>
@import '@/uni.scss';

.ins-mine {
  min-height: 100vh;
  padding: 12rpx 12rpx 0;
  box-sizing: border-box;
  background: #f5f5f6;
}

.ins-mine__hdr {
  padding-bottom: 8rpx;
}

.ins-mine__tabs {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  padding: 8rpx 4rpx 4rpx;
  background: #fff;
  border-radius: 16rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.06);
}

.ins-mine__tab {
  flex: 1;
  text-align: center;
  padding: 16rpx 8rpx 18rpx;
  font-size: 28rpx;
  font-weight: 500;
  color: $mp-text-secondary;
  position: relative;
}

.ins-mine__tab--on {
  color: $mp-text-primary;
  font-weight: 700;
}

.ins-mine__tab--on::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: 8rpx;
  transform: translateX(-50%);
  width: 40rpx;
  height: 4rpx;
  border-radius: 999rpx;
  background: $mp-accent;
}

.ins-mine__scroll {
  margin-top: 8rpx;
  background: #f5f5f6;
}

.ins-mine__loading {
  padding: 80rpx 0;
  text-align: center;
}

.ins-mine__loading-t {
  font-size: 26rpx;
  color: $mp-text-muted;
}

.ins-mine__empty {
  padding: 80rpx 32rpx;
  text-align: center;
}

.ins-mine__empty-t {
  display: block;
  font-size: 28rpx;
  color: $mp-text-muted;
}

.ins-mine__empty-s {
  display: block;
  margin-top: 12rpx;
  font-size: 24rpx;
  line-height: 1.5;
  color: $mp-text-muted;
  opacity: 0.9;
}

.ins-mine__empty-btn {
  margin-top: 32rpx;
  max-width: 360rpx;
}

.ins-mine__gate {
  padding: 72rpx 40rpx 48rpx;
  text-align: center;
}

.ins-mine__gate-t {
  display: block;
  font-size: 30rpx;
  font-weight: 700;
  color: $mp-text-primary;
}

.ins-mine__gate-s {
  display: block;
  margin-top: 12rpx;
  font-size: 24rpx;
  line-height: 1.5;
  color: $mp-text-muted;
}

.ins-mine__gate-btn {
  margin-top: 36rpx;
  max-width: 400rpx;
}

.ins-mine__cols {
  display: flex;
  flex-direction: row;
  gap: 12rpx;
  padding-bottom: calc(32rpx + env(safe-area-inset-bottom));
}

.ins-mine__col {
  flex: 1;
  min-width: 0;
}

.ins-mine__acts {
  padding-bottom: calc(32rpx + env(safe-area-inset-bottom));
}

.ins-mine__act {
  display: flex;
  flex-direction: row;
  gap: 20rpx;
  padding: 24rpx 20rpx;
  margin-bottom: 12rpx;
  background: #fff;
  border-radius: 16rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);
}

.ins-mine__act-thumb {
  width: 128rpx;
  height: 128rpx;
  border-radius: 12rpx;
  flex-shrink: 0;
  background: #eee;
}

.ins-mine__act-thumb--ph {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ebebed;
}

.ins-mine__act-ph {
  font-size: 24rpx;
  color: $mp-text-muted;
}

.ins-mine__act-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 8rpx;
}

.ins-mine__act-tags {
  display: flex;
  flex-direction: row;
}

.ins-mine__act-tag {
  font-size: 20rpx;
  font-weight: 700;
  padding: 4rpx 12rpx;
  border-radius: 8rpx;
}

.ins-mine__act-tag--sent {
  color: #5c6bc0;
  background: rgba(92, 107, 192, 0.12);
}

.ins-mine__act-tag--in {
  color: #7b57e4;
  background: $mp-accent-soft;
}

.ins-mine__act-title {
  font-size: 28rpx;
  font-weight: 700;
  color: $mp-text-primary;
  line-height: 1.35;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.ins-mine__act-excerpt {
  font-size: 24rpx;
  color: $mp-text-secondary;
  line-height: 1.45;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.ins-mine__act-meta {
  font-size: 22rpx;
  color: $mp-text-muted;
}
</style>
