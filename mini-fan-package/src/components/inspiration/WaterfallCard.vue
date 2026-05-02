<template>
  <view class="iw-card" @click="$emit('open')">
    <view class="iw-card__cover-wrap">
      <image v-if="coverSrc" class="iw-card__cover" :src="coverSrc" mode="widthFix" />
      <view v-else class="iw-card__cover iw-card__cover--empty" />
      <view v-if="coverSrc" class="iw-card__cover-mask" />
    </view>
    <view class="iw-card__body">
      <text v-if="item.title" class="iw-card__title">{{ item.title }}</text>
      <text v-if="item.description" class="iw-card__desc">{{ shortDesc }}</text>
      <view class="iw-card__meta">
        <view class="iw-card__author">
          <view class="iw-card__avatar"><text>{{ avatarText }}</text></view>
          <text class="iw-card__nick">{{ item.nickname }}</text>
        </view>
        <text class="iw-card__type">{{ item.sourceType === 'ai_generated' ? 'AI生成' : '用户实拍' }}</text>
      </view>
      <view class="iw-card__stats">
        <text @click.stop="$emit('favorite')">★ {{ item.favoriteCount }}</text>
        <text @click.stop="$emit('like')">♥ {{ item.likeCount }}</text>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { InspirationItem } from '@/types/inspiration'

const props = defineProps<{ item: InspirationItem }>()
defineEmits<{ open: []; favorite: []; like: [] }>()

const coverSrc = computed(() => {
  const c = (props.item.coverImage || '').trim()
  if (c) return c
  const imgs = Array.isArray(props.item.images) ? props.item.images : []
  const first = imgs.find((u) => typeof u === 'string' && u.trim())
  return first ? first.trim() : ''
})

const shortDesc = computed(() => {
  const t = props.item.description || ''
  return t.length > 44 ? `${t.slice(0, 44)}...` : t
})
const avatarText = computed(() => (props.item.nickname || '访').slice(0, 1))
</script>

<style lang="scss" scoped>
@import '@/uni.scss';
/* 双列卡片：白底、圆角、轻阴影，列内垂直间距与信息流一致 */
.iw-card {
  background: #fff;
  border: none;
  border-radius: 16rpx;
  overflow: hidden;
  margin-bottom: 12rpx;
  box-shadow: 0 2rpx 16rpx rgba(0, 0, 0, 0.06);
}
.iw-card__cover-wrap {
  position: relative;
  overflow: hidden;
  border-radius: 16rpx 16rpx 0 0;
}
.iw-card__cover {
  width: 100%;
  min-height: 220rpx;
  background: #f0f0f2;
  display: block;
}

.iw-card__cover--empty {
  min-height: 220rpx;
  background: linear-gradient(145deg, #ececf0 0%, #e2e2e8 100%);
}
.iw-card__cover-mask {
  position: absolute;
  inset: 0;
  pointer-events: none;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.12));
}
.iw-card__body {
  padding: 16rpx 14rpx 14rpx;
}
.iw-card__title {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
  font-size: 26rpx;
  font-weight: 600;
  color: $mp-text-primary;
  line-height: 1.35;
}
.iw-card__desc {
  display: block;
  margin-top: 8rpx;
  font-size: 22rpx;
  color: $mp-text-secondary;
  line-height: 1.45;
}
.iw-card__meta {
  margin-top: 12rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8rpx;
}
.iw-card__author {
  display: flex;
  align-items: center;
  gap: 8rpx;
  min-width: 0;
}
.iw-card__avatar {
  width: 36rpx;
  height: 36rpx;
  border-radius: 50%;
  background: #f0eef8;
  color: $mp-accent;
  font-size: 20rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.iw-card__nick {
  font-size: 22rpx;
  color: #8a8a8e;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.iw-card__type {
  font-size: 20rpx;
  color: #8a8a8e;
  flex-shrink: 0;
}
.iw-card__stats {
  margin-top: 8rpx;
  display: flex;
  gap: 20rpx;
  font-size: 22rpx;
  color: #8a8a8e;
}
</style>
