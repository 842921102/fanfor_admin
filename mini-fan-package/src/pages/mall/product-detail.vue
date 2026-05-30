<template>
  <view class="mp-page mpd">
    <view v-if="!product" class="mp-card"><text class="mpd__muted">商品不存在或已下架</text></view>
    <template v-else>
      <view class="mp-card">
        <swiper v-if="product.images.length" class="mpd__swiper" circular indicator-dots>
          <swiper-item v-for="(src, i) in product.images" :key="i" @click="preview(i)">
            <image class="mpd__img" :src="src" mode="aspectFill" />
          </swiper-item>
        </swiper>
        <text class="mpd__title">{{ product.name }}</text>
        <text class="mpd__price">{{ formatPrice(product.price) }}</text>
        <text v-if="product.originalPrice" class="mpd__origin">原价 {{ formatPrice(product.originalPrice) }}</text>
        <text class="mpd__desc">{{ product.description || '暂无简介' }}</text>
      </view>
      <view class="mpd__bar">
        <button class="mp-btn-primary mpd__buy" :disabled="product.stock <= 0" @click="buyNow">
          {{ product.stock > 0 ? '立即购买' : '库存不足' }}
        </button>
      </view>
    </template>
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onLoad, onShow } from '@dcloudio/uni-app'
import { getMallProductDetail } from '@/api/mall'
import type { MallProduct } from '@/types/mall'

const id = ref('')
const product = ref<MallProduct | null>(null)

async function load() {
  if (!id.value) return
  product.value = await getMallProductDetail(id.value)
}

onLoad((q) => { id.value = typeof q?.id === 'string' ? decodeURIComponent(q.id) : '' })
onShow(() => void load())

function formatPrice(fen: number): string {
  return `¥${(fen / 100).toFixed(2)}`
}

function preview(i: number) {
  if (!product.value?.images.length) return
  uni.previewImage({ current: product.value.images[i], urls: product.value.images })
}

function buyNow() {
  if (!product.value) return
  uni.navigateTo({ url: `/pages/mall/order-create?productId=${encodeURIComponent(product.value.id)}` })
}
</script>

<style lang="scss" scoped>
@import '@/uni.scss';
.mpd{padding-bottom:calc(150rpx + env(safe-area-inset-bottom))}
.mpd__swiper{height:560rpx;border-radius:16rpx;overflow:hidden;background:#f5f5f7}
.mpd__img{width:100%;height:100%}
.mpd__title{display:block;margin-top:14rpx;font-size:34rpx;font-weight:600;line-height:1.35;color:$mp-text-primary}
.mpd__price{display:block;margin-top:8rpx;font-size: 30rpx;font-weight:700;line-height:1.4;color:#d15454}
.mpd__origin{display:block;margin-top:6rpx;font-size:22rpx;font-weight:500;line-height:1.3;color:$mp-text-muted;text-decoration:line-through}
.mpd__desc{display:block;margin-top:12rpx;font-size: 24rpx;font-weight:400;line-height:1.5;color:$mp-text-secondary;white-space:pre-wrap}
.mpd__muted{display:block;padding:16rpx 0;font-size:24rpx;font-weight:400;line-height:1.5;color:$mp-text-muted}
.mpd__bar{position:fixed;left:0;right:0;bottom:0;background:#fdfdfe;border-top:1rpx solid $mp-border;padding:12rpx 20rpx calc(12rpx + env(safe-area-inset-bottom))}
</style>
