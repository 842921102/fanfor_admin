<template>
  <view class="hc-rd" />
</template>

<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app'

/** 兼容旧入口 / 分包首路径；直接进入选菜页，不再展示中间落地页 */
onLoad(() => {
  uni.redirectTo({ url: '/pages/help-choose/pick' })
})
</script>

<style lang="scss" scoped>
.hc-rd {
  min-height: 100vh;
  background: #f5f5f7;
}
</style>
