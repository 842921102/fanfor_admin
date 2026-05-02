<template>
  <view class="mp-page hc-pick">
    <scroll-view v-if="phase === 'form'" scroll-y class="hc-pick__scroll" :show-scrollbar="false">
      <view class="hc-pick__section mp-card">
        <text class="hc-pick__h">候选菜品</text>
        <text class="hc-pick__hint">支持中文逗号、英文逗号、顿号或换行分隔；输入后会自动拆成标签。</text>
        <textarea
          v-model="dishText"
          class="hc-pick__textarea"
          :placeholder="config.help_choose_dishes_placeholder"
          placeholder-class="hc-pick__textarea-ph"
          maxlength="800"
          :show-confirm-bar="false"
          :auto-height="true"
        />
        <view v-if="dishTags.length" class="hc-pick__tags">
          <view v-for="(tag, i) in dishTags" :key="`${tag}-${i}`" class="hc-pick__tag">
            <text class="hc-pick__tag-txt">{{ tag }}</text>
            <text class="hc-pick__tag-x" @click.stop="removeTag(i)">×</text>
          </view>
        </view>
      </view>

      <view class="hc-pick__section mp-card">
        <text class="hc-pick__h">场景选择</text>
        <view class="hc-pick__scene-grid">
          <view
            v-for="s in HELP_CHOOSE_SCENES"
            :key="s.id"
            class="hc-pick__scene"
            :class="{ 'hc-pick__scene--on': sceneId === s.id }"
            @click="sceneId = s.id"
          >
            <text class="hc-pick__scene-txt">{{ s.label }}</text>
          </view>
        </view>
      </view>

      <view class="hc-pick__section mp-card">
        <text class="hc-pick__h">偏好（可选）</text>
        <view class="hc-pick__pref-row">
          <view
            v-for="p in HELP_CHOOSE_PREFS"
            :key="p.id"
            class="hc-pick__pref"
            :class="{ 'hc-pick__pref--on': prefs.includes(p.id) }"
            @click="togglePref(p.id)"
          >
            <text>{{ p.label }}</text>
          </view>
        </view>
      </view>

      <button class="mp-btn-primary hc-pick__main-btn" @click="onPick">
        <text>{{ config.help_choose_primary_pick_btn }}</text>
      </button>
      <view class="hc-pick__foot-spacer" />
    </scroll-view>

    <scroll-view v-else scroll-y class="hc-pick__scroll" :show-scrollbar="false">
      <view class="hc-pick__result mp-card mp-card--accent-soft">
        <text class="hc-pick__result-k">{{ config.help_choose_result_today_label }}</text>
        <text class="hc-pick__result-dish">{{ result?.picked }}</text>
        <view class="hc-pick__rule" />
        <text class="hc-pick__result-label">{{ config.help_choose_result_reason_label }}</text>
        <text class="hc-pick__result-reason">{{ result?.reason }}</text>
        <view v-if="result?.alternatives?.length" class="hc-pick__alt-block">
          <text class="hc-pick__result-label">{{ config.help_choose_result_alternatives_label }}</text>
          <view class="hc-pick__alt-chips">
            <text v-for="(a, i) in result.alternatives" :key="i" class="hc-pick__alt-chip">{{ a }}</text>
          </view>
        </view>
      </view>

      <view class="hc-pick__actions">
        <button class="mp-btn-ghost hc-pick__act" @click="onReroll">{{ config.help_choose_btn_reroll }}</button>
        <button class="mp-btn-ghost hc-pick__act" @click="onReset">{{ config.help_choose_btn_reset }}</button>
        <button class="mp-btn-primary hc-pick__act hc-pick__act--primary" :loading="saveLoading" @click="onSave">
          {{ config.help_choose_btn_save }}
        </button>
        <button class="mp-btn-secondary hc-pick__act hc-pick__share" open-type="share">
          {{ config.help_choose_btn_share }}
        </button>
      </view>
      <view class="hc-pick__foot-spacer" />
    </scroll-view>
  </view>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { onShareAppMessage, onShow } from '@dcloudio/uni-app'
import { useAppConfig } from '@/composables/useAppConfig'
import { useAppMessages } from '@/composables/useAppMessages'
import { goLoginGate } from '@/lib/loginNav'
import { useAuth } from '@/composables/useAuth'
import { saveHelpChooseRecord } from '@/api/helpChoose'
import { HttpError } from '@/api/http'
import {
  HELP_CHOOSE_PREFS,
  HELP_CHOOSE_SCENES,
  buildHelpChooseReason,
  parseDishNames,
  pickRandomDishAndAlternatives,
  type HelpChoosePrefId,
  type HelpChooseSceneId,
} from '@/lib/helpChoosePick'

const { config } = useAppConfig()
const msg = useAppMessages()
const { isLoggedIn, syncAuthFromSupabase } = useAuth()

const phase = ref<'form' | 'result'>('form')
const dishText = ref('')
const dishTags = ref<string[]>([])
const sceneId = ref<HelpChooseSceneId>('solo')
const prefs = ref<HelpChoosePrefId[]>([])

const result = ref<{
  picked: string
  alternatives: string[]
  reason: string
} | null>(null)

const saveLoading = ref(false)

let parseTimer: ReturnType<typeof setTimeout> | null = null

function scheduleParse() {
  if (parseTimer) clearTimeout(parseTimer)
  parseTimer = setTimeout(() => {
    dishTags.value = parseDishNames(dishText.value)
  }, 180)
}

watch(dishText, () => {
  scheduleParse()
})

function removeTag(i: number) {
  dishTags.value.splice(i, 1)
  dishText.value = dishTags.value.join('、')
}

function togglePref(id: HelpChoosePrefId) {
  const ix = prefs.value.indexOf(id)
  if (ix >= 0) {
    prefs.value = prefs.value.filter((x) => x !== id)
  } else {
    prefs.value = [...prefs.value, id]
  }
}

function runPickFromTags(): boolean {
  const tags = [...dishTags.value]
  if (tags.length < 2) {
    uni.showToast({ title: config.value.help_choose_min_dishes_toast, icon: 'none' })
    return false
  }
  const { picked, alternatives } = pickRandomDishAndAlternatives(tags)
  const reason = buildHelpChooseReason(sceneId.value, prefs.value, picked)
  result.value = { picked, alternatives, reason }
  phase.value = 'result'
  return true
}

function onPick() {
  dishTags.value = parseDishNames(dishText.value)
  runPickFromTags()
}

function onReroll() {
  if (!result.value) return
  runPickFromTags()
}

function onReset() {
  phase.value = 'form'
  dishText.value = ''
  dishTags.value = []
  sceneId.value = 'solo'
  prefs.value = []
  result.value = null
}

async function onSave() {
  if (!result.value) return
  if (!isLoggedIn.value) {
    uni.showModal({
      title: '需要登录',
      content: '保存后可在管理后台与你的账号关联统计，是否前往登录？',
      confirmText: '去登录',
      cancelText: '取消',
      success(res) {
        if (res.confirm) {
          goLoginGate('/pages/help-choose/pick')
        }
      },
    })
    return
  }
  saveLoading.value = true
  try {
    await saveHelpChooseRecord({
      dishes: dishTags.value,
      scene_id: sceneId.value,
      preferences: prefs.value,
      picked: result.value.picked,
      alternatives: result.value.alternatives,
      reason: result.value.reason,
    })
    msg.toastSaveSuccess()
  } catch (e) {
    msg.toastSaveFailed(e instanceof HttpError ? e.message : undefined)
  } finally {
    saveLoading.value = false
  }
}

onShow(() => {
  void syncAuthFromSupabase()
})

onShareAppMessage(() => {
  const p = result.value?.picked
  const prefix = config.value.help_choose_share_title_prefix
  const title = p ? `${prefix}${p}` : `${config.value.help_choose_landing_title} · 饭否`
  return {
    title,
    path: '/pages/help-choose/pick',
  }
})
</script>

<style lang="scss" scoped>
$hc-primary: #7b57e4;
$hc-soft: #ebe4ff;

.hc-pick {
  min-height: 100vh;
  background: #f5f5f7;
  box-sizing: border-box;
}

.hc-pick__scroll {
  height: 100vh;
  padding: 24rpx 24rpx 0;
  box-sizing: border-box;
}

.hc-pick__section {
  margin-bottom: 24rpx;
  padding: 28rpx 24rpx;
  border-radius: 24rpx;
}

.hc-pick__h {
  display: block;
  font-size: 30rpx;
  font-weight: 800;
  color: #1a1c21;
  margin-bottom: 12rpx;
}

.hc-pick__hint {
  display: block;
  font-size: 22rpx;
  color: #8e95a3;
  line-height: 1.45;
  margin-bottom: 16rpx;
}

.hc-pick__textarea {
  width: 100%;
  min-height: 160rpx;
  padding: 20rpx;
  box-sizing: border-box;
  font-size: 28rpx;
  color: #2d3436;
  line-height: 1.5;
  border-radius: 16rpx;
  background: #fbfbfc;
  border: 1rpx solid rgba(123, 87, 228, 0.12);
}

.hc-pick__textarea-ph {
  color: #b2b9c2;
}

.hc-pick__tags {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  gap: 12rpx;
  margin-top: 20rpx;
}

.hc-pick__tag {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8rpx;
  padding: 10rpx 16rpx 10rpx 18rpx;
  border-radius: 999rpx;
  background: $hc-soft;
  border: 1rpx solid rgba(123, 87, 228, 0.2);
}

.hc-pick__tag-txt {
  font-size: 24rpx;
  font-weight: 600;
  color: #4a4f55;
  max-width: 280rpx;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.hc-pick__tag-x {
  font-size: 28rpx;
  color: #8e95a3;
  padding: 0 4rpx;
}

.hc-pick__scene-grid {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  gap: 16rpx;
}

.hc-pick__scene {
  padding: 18rpx 22rpx;
  border-radius: 16rpx;
  background: #f6f7fb;
  border: 2rpx solid transparent;
}

.hc-pick__scene--on {
  background: rgba(123, 87, 228, 0.1);
  border-color: $hc-primary;
}

.hc-pick__scene-txt {
  font-size: 26rpx;
  font-weight: 600;
  color: #3d4446;
}

.hc-pick__scene--on .hc-pick__scene-txt {
  color: $hc-primary;
}

.hc-pick__pref-row {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  gap: 14rpx;
}

.hc-pick__pref {
  padding: 14rpx 22rpx;
  border-radius: 999rpx;
  font-size: 24rpx;
  font-weight: 600;
  color: #5f6673;
  background: #f0f1f5;
  border: 2rpx solid transparent;
}

.hc-pick__pref--on {
  color: $hc-primary;
  background: $hc-soft;
  border-color: rgba(123, 87, 228, 0.35);
}

.hc-pick__main-btn {
  width: 100%;
  height: 96rpx;
  border-radius: 48rpx;
  margin-bottom: 32rpx;
  background: linear-gradient(120deg, #966fec 0%, $hc-primary 55%, #6842cf 100%);
}

.hc-pick__foot-spacer {
  height: 48rpx;
}

.hc-pick__result {
  padding: 36rpx 28rpx;
  border-radius: 28rpx;
  margin-bottom: 24rpx;
  border: 1rpx solid rgba(123, 87, 228, 0.15);
}

.hc-pick__result-k {
  display: block;
  font-size: 24rpx;
  font-weight: 700;
  color: $hc-primary;
  letter-spacing: 0.06em;
}

.hc-pick__result-dish {
  display: block;
  margin-top: 12rpx;
  font-size: 40rpx;
  font-weight: 800;
  color: #1a1c21;
  line-height: 1.25;
}

.hc-pick__rule {
  height: 1rpx;
  background: rgba(123, 87, 228, 0.15);
  margin: 28rpx 0;
}

.hc-pick__result-label {
  display: block;
  font-size: 24rpx;
  font-weight: 700;
  color: #636e72;
  margin-bottom: 10rpx;
}

.hc-pick__result-reason {
  font-size: 28rpx;
  line-height: 1.65;
  color: #2d3436;
}

.hc-pick__alt-block {
  margin-top: 28rpx;
}

.hc-pick__alt-chips {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  gap: 12rpx;
  margin-top: 8rpx;
}

.hc-pick__alt-chip {
  padding: 10rpx 18rpx;
  border-radius: 12rpx;
  font-size: 24rpx;
  font-weight: 600;
  color: #4a4f55;
  background: rgba(255, 255, 255, 0.85);
  border: 1rpx solid rgba(123, 87, 228, 0.12);
}

.hc-pick__actions {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
}

.hc-pick__act {
  width: 100%;
  height: 88rpx;
  border-radius: 44rpx;
}

.hc-pick__act--primary {
  background: linear-gradient(120deg, #966fec 0%, $hc-primary 55%, #6842cf 100%);
}

.hc-pick__share {
  margin: 0;
  padding: 0;
  line-height: 88rpx;
  font-size: 28rpx;
  font-weight: 600;
}

.hc-pick__share::after {
  border: none;
}
</style>
