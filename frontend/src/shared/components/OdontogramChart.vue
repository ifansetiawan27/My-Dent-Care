<script setup lang="ts">
import { ref, computed } from 'vue'

export interface ToothMark { condition: string; notes?: string }

const props = defineProps<{ modelValue: Record<string, ToothMark> }>()
const emit = defineEmits<{ 'update:modelValue': [value: Record<string, ToothMark>] }>()

const conditions = [
  { id: 'sound', label: 'Sehat', color: '#52c41a' },
  { id: 'caries', label: 'Karies', color: '#f5222d' },
  { id: 'filling', label: 'Tumpatan', color: '#1890ff' },
  { id: 'missing', label: 'Hilang', color: '#8c8c8c' },
  { id: 'crown', label: 'Mahkota', color: '#faad14' },
  { id: 'root_canal', label: 'RCT', color: '#722ed1' },
  { id: 'extraction', label: 'Ekstraksi', color: '#fa541c' },
] as const

const activeCondition = ref('caries')
const hoveredTooth = ref<string | null>(null)

const markedCount = computed(() => Object.values(props.modelValue).filter(m => m.condition && m.condition !== 'sound').length)

function getMark(num: string): ToothMark | undefined { return props.modelValue[num] }
function getCondition(num: string): string | null { return getMark(num)?.condition ?? null }
function getConditionColor(num: string): string | null {
  const c = getCondition(num)
  return conditions.find(x => x.id === c)?.color ?? null
}

function toggleTooth(num: string): void {
  const current = getMark(num)
  const updated = { ...props.modelValue }
  if (current?.condition === activeCondition.value) {
    delete updated[num]
  } else {
    updated[num] = { condition: activeCondition.value }
  }
  emit('update:modelValue', updated)
}

function clearAll(): void { emit('update:modelValue', {}) }

// ===== Tooth geometry =====
function toothWidth(num: number): number {
  const d = num % 10
  const map: Record<number, number> = { 1: 30, 2: 25, 3: 28, 4: 32, 5: 32, 6: 36, 7: 36, 8: 34 }
  return map[d] ?? 30
}

interface Tooth { num: number; x: number; y: number; w: number; upper: boolean; labelY: number }

const teeth = computed<Tooth[]>(() => {
  const upperSeq = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28]
  const lowerSeq = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38]
  const list: Tooth[] = []
  const baseYUpper = 150
  const baseYLower = 378
  const spacing = 46.6
  const x0 = 52

  for (let i = 0; i < 16; i++) {
    const x = x0 + i * spacing
    const d = Math.abs(i - 7.5)
    const arch = d * d * 0.14
    const yU = baseYUpper + arch
    const yL = baseYLower - arch
    // upper: crown pointing down (visually flip), number sits inside crown (below center)
    list.push({ num: upperSeq[i], x, y: yU, w: toothWidth(upperSeq[i]), upper: true, labelY: yU + 24 })
    list.push({ num: lowerSeq[i], x, y: yL, w: toothWidth(lowerSeq[i]), upper: false, labelY: yL - 24 })
  }
  return list
})

// Canonical tooth silhouette (crown up, roots down), centered at origin
function toothPath(w: number, h: number): string {
  const hw = w / 2
  return [
    `M ${-hw + 2}, ${h * 0.20}`,
    `C ${-hw + 2}, ${-h * 0.28} ${-hw * 0.34}, ${-h * 0.44} ${-hw * 0.20}, ${-h * 0.48}`,
    `C ${-hw * 0.08}, ${-h * 0.52} ${hw * 0.08}, ${-h * 0.52} ${hw * 0.20}, ${-h * 0.48}`,
    `C ${hw * 0.34}, ${-h * 0.44} ${hw - 2}, ${-h * 0.28} ${hw - 2}, ${h * 0.20}`,
    `C ${hw - 2}, ${h * 0.42} ${hw * 0.26}, ${h * 0.40} ${hw * 0.14}, ${h * 0.50}`,
    `C ${hw * 0.06}, ${h * 0.52} ${-hw * 0.06}, ${-h * 0.52} ${-hw * 0.14}, ${h * 0.50}`,
    `C ${-hw * 0.26}, ${h * 0.40} ${-hw + 2}, ${h * 0.42} ${-hw + 2}, ${h * 0.20}`,
    'Z',
  ].join(' ')
}

// Crown highlight (gloss) line
function highlightPath(w: number, h: number): string {
  return `M ${-w * 0.24}, ${-h * 0.02} C ${-w * 0.24}, ${-h * 0.26} ${-w * 0.12}, ${-h * 0.40} ${-w * 0.04}, ${-h * 0.44}`
}
</script>

<template>
  <div class="chart-wrap">
    <!-- Legend -->
    <div class="chart-legend">
      <span class="chart-legend-title">Kondisi:</span>
      <button
        v-for="c in conditions"
        :key="c.id"
        type="button"
        class="chart-legend-btn"
        :class="{ active: activeCondition === c.id }"
        :style="{
          borderColor: c.color,
          color: activeCondition === c.id ? '#fff' : c.color,
          background: activeCondition === c.id ? c.color : 'transparent',
        }"
        @click="activeCondition = c.id"
      >{{ c.label }}</button>
      <span class="chart-legend-count">{{ markedCount }} gigi ditandai</span>
      <button v-if="markedCount" class="chart-legend-clear" @click="clearAll">✕ Hapus Semua</button>
    </div>

    <!-- 3D Chart -->
    <div class="chart-svg-wrap">
      <svg viewBox="0 0 800 520" class="chart-svg" role="img" aria-label="Odontogram 3D interaktif">
        <defs>
          <linearGradient id="tooth3d" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#ffffff" />
            <stop offset="55%" stop-color="#eef3f6" />
            <stop offset="100%" stop-color="#c4d2db" />
          </linearGradient>
          <linearGradient id="gum3d" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#ffcec4" />
            <stop offset="100%" stop-color="#f0948a" />
          </linearGradient>
          <filter id="toothShadow" x="-30%" y="-30%" width="160%" height="160%">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#7c9aab" flood-opacity="0.35" />
          </filter>
        </defs>

        <!-- Gum (gingiva) -->
        <g>
          <rect x="30" y="104" width="740" height="52" rx="26" fill="url(#gum3d)" />
          <rect x="30" y="366" width="740" height="52" rx="26" fill="url(#gum3d)" />
        </g>

        <!-- quadrant labels -->
        <text x="390" y="80" text-anchor="middle" fill="#bfbfbf" font-size="12" font-weight="600">RAHANG ATAS</text>
        <text x="390" y="470" text-anchor="middle" fill="#bfbfbf" font-size="12" font-weight="600">RAHANG BAWAH</text>

        <!-- teeth -->
        <g v-for="t in teeth" :key="t.upper ? 'u' + t.num : 'l' + t.num">
          <g
            :transform="`translate(${t.x},${t.y})${t.upper ? ' scale(1,-1)' : ''}`"
            class="chart-tooth"
            :class="{ hovered: hoveredTooth === String(t.num) }"
            :style="hoveredTooth === String(t.num) ? '' : ''"
            @mouseenter="hoveredTooth = String(t.num)"
            @mouseleave="hoveredTooth = null"
            @click="toggleTooth(String(t.num))"
          >
            <!-- base 3D enamel -->
            <path :d="toothPath(t.w, 78)" fill="url(#tooth3d)" stroke="#b7c6cf" stroke-width="1" filter="url(#toothShadow)" />
            <!-- condition overlay -->
            <path
              v-if="getConditionColor(String(t.num))"
              :d="toothPath(t.w, 78)"
              :fill="getConditionColor(String(t.num))!"
              :opacity="0.85"
              stroke="rgba(0,0,0,0.15)"
              stroke-width="1"
            />
            <!-- gloss highlight -->
            <path :d="highlightPath(t.w, 78)" fill="none" stroke="rgba(255,255,255,0.85)" stroke-width="2.5" stroke-linecap="round" />
          </g>

          <!-- upright number label inside crown -->
          <text
            :x="t.x"
            :y="t.labelY"
            text-anchor="middle"
            dominant-baseline="central"
            :fill="getCondition(String(t.num)) && getCondition(String(t.num)) !== 'sound' ? '#fff' : '#495057'"
            font-size="11"
            font-weight="700"
            style="pointer-events:none"
          >{{ t.num }}</text>
        </g>
      </svg>
    </div>
  </div>
</template>

<style scoped>
.chart-wrap { margin: 0.5rem 0; }
.chart-legend { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.75rem; }
.chart-legend-title { font-size: 0.75rem; font-weight: 700; color: #8c8c8c; text-transform: uppercase; letter-spacing: 0.04em; }
.chart-legend-btn { padding: 0.28rem 0.8rem; border-radius: 999px; border: 1.5px solid; font-size: 0.75rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.15s; }
.chart-legend-btn:hover { opacity: 0.85; }
.chart-legend-count { margin-left: auto; font-size: 0.8125rem; color: #595959; font-weight: 600; }
.chart-legend-clear { background: none; border: 1px solid #d9d9d9; border-radius: 6px; padding: 0.25rem 0.625rem; font-size: 0.75rem; color: #f5222d; cursor: pointer; font-family: inherit; }
.chart-svg-wrap { background: linear-gradient(180deg, #ffffff, #f6f9fb); border: 1px solid #e6ebf0; border-radius: 14px; padding: 0.5rem; box-shadow: inset 0 1px 4px rgba(0,0,0,0.04); }
.chart-svg { width: 100%; height: auto; display: block; }
.chart-tooth { cursor: pointer; transition: filter 0.1s; }
.chart-tooth:hover { filter: drop-shadow(0 0 8px rgba(24,144,255,0.55)); }
</style>