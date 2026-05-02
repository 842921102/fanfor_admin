<template>
  <view class="mp-page idt">
    <view v-if="!item" class="idt__gone">
      <text class="idt__muted">内容不存在或已下架</text>
    </view>
    <template v-else>
      <!-- 顶图全宽；正文区单独 16px 量级内边距（对齐小红书笔记） -->
      <swiper v-if="item.images.length" class="idt__swiper" circular indicator-dots indicator-color="rgba(0,0,0,0.15)" indicator-active-color="#ff2442">
        <swiper-item v-for="(src, i) in item.images" :key="i" @click="preview(i)">
          <image class="idt__img" :src="src" mode="aspectFill" />
        </swiper-item>
      </swiper>
      <view class="idt__sheet">
        <text v-if="item.title" class="idt__title">{{ item.title }}</text>
        <text v-if="item.description" class="idt__desc">{{ item.description }}</text>
        <text class="idt__meta">{{ item.nickname }} · {{ item.sourceType === 'ai_generated' ? 'AI生成' : '用户实拍' }} · {{ formatListTime(item.createdAt) }}</text>
        <view class="idt__actions">
          <button type="button" class="mp-btn-secondary" hover-class="none" @click="onFavorite">
            {{ item.isFavorited ? '已收藏' : '收藏' }} {{ item.favoriteCount }}
          </button>
          <button type="button" class="mp-btn-ghost" hover-class="none" @click="onLike">点赞 {{ item.likeCount }}</button>
        </view>
      </view>

      <view class="idt__sheet idt__sheet--comments">
        <text class="idt__comments-h">评论 {{ comments.length }}</text>
        <CommentItem v-for="c in comments" :key="c.id" :comment="c" />
        <text v-if="comments.length === 0" class="idt__muted">还没有评论，来抢沙发吧</text>
      </view>
    </template>

    <view class="idt__bar">
      <input v-model="draft" class="idt__input" placeholder="写评论..." confirm-type="send" @confirm="sendComment" />
      <button type="button" class="mp-btn-primary idt__send" hover-class="none" @click="sendComment">发送</button>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onLoad, onShow } from '@dcloudio/uni-app'
import { HttpError } from '@/api/http'
import { createInspirationComment, favoriteInspiration, getInspirationComments, getInspirationDetail, likeInspiration } from '@/api/inspiration'
import CommentItem from '@/components/inspiration/CommentItem.vue'
import { goLoginGate } from '@/lib/loginNav'
import type { InspirationComment, InspirationItem } from '@/types/inspiration'
import { formatListTime } from '@/utils/dateFormat'

const id = ref('')
const item = ref<InspirationItem | null>(null)
const comments = ref<InspirationComment[]>([])
const draft = ref('')

/** 丢弃「详情/评论」拉取完成晚于点赞、收藏」的旧响应，避免覆盖已更新的 isLiked / isFavorited */
let detailLoadSeq = 0

async function load() {
  if (!id.value) return
  const seq = ++detailLoadSeq
  try {
    const [d, cs] = await Promise.all([getInspirationDetail(id.value), getInspirationComments(id.value)])
    if (seq !== detailLoadSeq) return
    item.value = d
    comments.value = cs
  } catch (e: unknown) {
    if (seq !== detailLoadSeq) return
    const err = e as Error
    uni.showToast({ title: err.message || '加载失败', icon: 'none' })
  }
}
onLoad((q) => { id.value = typeof q?.id === 'string' ? decodeURIComponent(q.id) : '' })
onShow(() => void load())

function preview(i: number) {
  if (!item.value?.images.length) return
  uni.previewImage({ current: item.value.images[i], urls: item.value.images })
}
function currentDetailPathForLogin(): string {
  const raw = id.value.trim()
  if (!raw) return '/pages/inspiration/index'
  return `/pages/inspiration/detail?id=${encodeURIComponent(raw)}`
}

async function onFavorite() {
  if (!id.value) return
  try {
    const d = await favoriteInspiration(id.value)
    if (d) {
      item.value = d
      detailLoadSeq++
    }
  } catch (e: unknown) {
    if (e instanceof HttpError && e.statusCode === 401) {
      uni.showToast({ title: '请先登录', icon: 'none' })
      goLoginGate(currentDetailPathForLogin())
      return
    }
    const err = e as Error
    uni.showToast({ title: err.message || '操作失败', icon: 'none' })
  }
}

async function onLike() {
  if (!id.value) return
  try {
    const d = await likeInspiration(id.value)
    if (d) {
      item.value = d
      detailLoadSeq++
    }
  } catch (e: unknown) {
    if (e instanceof HttpError && e.statusCode === 401) {
      uni.showToast({ title: '请先登录', icon: 'none' })
      goLoginGate(currentDetailPathForLogin())
      return
    }
    const err = e as Error
    uni.showToast({ title: err.message || '操作失败', icon: 'none' })
  }
}

async function sendComment() {
  const content = draft.value.trim()
  if (!content || !id.value) return
  try {
    const c = await createInspirationComment(id.value, content)
    if (!c) return
    comments.value = comments.value.concat(c)
    draft.value = ''
    detailLoadSeq++
  } catch (e: unknown) {
    if (e instanceof HttpError && e.statusCode === 401) {
      uni.showToast({ title: '请先登录', icon: 'none' })
      goLoginGate(currentDetailPathForLogin())
      return
    }
    const err = e as Error
    uni.showToast({ title: err.message || '发送失败', icon: 'none' })
  }
}
</script>

<style lang="scss" scoped>
@import '@/uni.scss';

.idt {
  padding: 0;
  padding-bottom: calc(160rpx + env(safe-area-inset-bottom));
  background: #f5f5f6;
}

.idt__gone {
  margin: 24rpx 32rpx;
  padding: 40rpx 32rpx;
  background: #fff;
  border-radius: 16rpx;
  text-align: center;
}

.idt__swiper {
  height: 560rpx;
  width: 100%;
  background: #ebebed;
}

.idt__img {
  width: 100%;
  height: 100%;
  display: block;
}

/* 正文 / 评论：水平约 16px（32rpx），与顶图全宽形成层次 */
.idt__sheet {
  background: #fff;
  padding: 24rpx 32rpx 28rpx;
}

.idt__sheet--comments {
  margin-top: 12rpx;
  padding-top: 20rpx;
  padding-bottom: 32rpx;
}

.idt__comments-h {
  display: block;
  margin-bottom: 8rpx;
  font-size: 28rpx;
  font-weight: 700;
  color: $mp-text-primary;
}

.idt__title {
  display: block;
  margin-top: 0;
  font-size: 34rpx;
  font-weight: 800;
  color: $mp-text-primary;
  line-height: 1.35;
}

.idt__desc {
  display: block;
  margin-top: 12rpx;
  font-size: 28rpx;
  line-height: 1.55;
  color: $mp-text-primary;
  white-space: pre-wrap;
}

.idt__meta {
  display: block;
  margin-top: 12rpx;
  font-size: 22rpx;
  color: $mp-text-muted;
}

.idt__actions {
  margin-top: 20rpx;
  display: flex;
  gap: 12rpx;
}

.idt__actions button {
  flex: 1;
}

.idt__muted {
  display: block;
  padding: 16rpx 0;
  font-size: 24rpx;
  color: $mp-text-muted;
}

.idt__bar {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 940;
  background: #fff;
  border-top: 1rpx solid #eee;
  padding: 12rpx 24rpx calc(12rpx + env(safe-area-inset-bottom));
  display: flex;
  gap: 12rpx;
  align-items: center;
  box-shadow: 0 -4rpx 24rpx rgba(0, 0, 0, 0.04);
}

.idt__input {
  flex: 1;
  padding: 16rpx 20rpx;
  border-radius: 999rpx;
  border: none;
  background: #f5f5f6;
  font-size: 26rpx;
}

.idt__send {
  width: 150rpx;
  flex-shrink: 0;
}
</style>
