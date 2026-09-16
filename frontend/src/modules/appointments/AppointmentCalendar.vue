<script setup lang="ts">
import { ref, computed } from 'vue'

/**
 * Month calendar view for appointments.
 *
 * Purely presentational: renders the appointments of one month and emits
 * `select` when an event is clicked. Month navigation is self-contained.
 *
 * NOTE: `todayStr` intentionally mirrors the parent's UTC-based comparison
 * (new Date().toISOString().slice(0, 10)) so day highlighting stays identical.
 */
const props = defineProps<{ appointments: any[] }>()
const emit = defineEmits<{ (e: 'select', appointment: any): void }>()

const calendarMonth = ref(new Date().getMonth())
const calendarYear = ref(new Date().getFullYear())

const calendarDays = computed(() => {
  const year = calendarYear.value
  const month = calendarMonth.value
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const startDay = firstDay.getDay()
  const daysInMonth = lastDay.getDate()

  const days: { date: string; day: number; appointments: any[] }[] = []
  // padding
  for (let i = 0; i < startDay; i++) {
    days.push({ date: '', day: 0, appointments: [] })
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
    const appts = props.appointments.filter(a => a.scheduled_at?.startsWith(dateStr))
    days.push({ date: dateStr, day: d, appointments: appts })
  }
  return days
})

const monthName = computed(() => {
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  return `${months[calendarMonth.value]} ${calendarYear.value}`
})

const todayStr = computed(() => new Date().toISOString().slice(0, 10))

function prevMonth() {
  if (calendarMonth.value === 0) {
    calendarMonth.value = 11
    calendarYear.value--
  } else {
    calendarMonth.value--
  }
}
function nextMonth() {
  if (calendarMonth.value === 11) {
    calendarMonth.value = 0
    calendarYear.value++
  } else {
    calendarMonth.value++
  }
}

function formatTime(val: string): string {
  if (!val) return ''
  return new Date(val).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

function patientName(row: any): string {
  return row.patient?.full_name || row.patient_name || row.patient_code || '—'
}
</script>

<template>
  <div class="appt-calendar">
    <div class="cal-header">
      <button class="cal-nav" @click="prevMonth">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      </button>
      <h2 class="cal-month">{{ monthName }}</h2>
      <button class="cal-nav" @click="nextMonth">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
      </button>
    </div>
    <div class="cal-weekdays">
      <span v-for="d in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="d" class="cal-weekday">{{ d }}</span>
    </div>
    <div class="cal-grid">
      <div
        v-for="(day, idx) in calendarDays"
        :key="idx"
        :class="['cal-day', { 'cal-day-empty': !day.day, 'cal-today': day.date === todayStr }]"
      >
        <span v-if="day.day" class="cal-day-num">{{ day.day }}</span>
        <div v-if="day.appointments.length" class="cal-events">
          <div
            v-for="appt in day.appointments.slice(0, 3)"
            :key="appt.id"
            :class="['cal-event', 'cal-event-' + (appt.status || 'scheduled')]"
            @click="emit('select', appt)"
          >
            <span class="cal-event-dot"></span>
            <span class="cal-event-text">{{ formatTime(appt.scheduled_at) }} – {{ patientName(appt) }}</span>
          </div>
          <div v-if="day.appointments.length > 3" class="cal-more">+{{ day.appointments.length - 3 }} lainnya</div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.appt-calendar { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem; }
.cal-header { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem; }
.cal-nav { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; color: #334155; cursor: pointer; }
.cal-nav:hover { background: #f1f5f9; }
.cal-month { font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0; }
.cal-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); margin-bottom: 0.5rem; }
.cal-weekday { text-align: center; font-size: 0.6875rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; padding: 0.375rem 0; }
.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
.cal-day { min-height: 80px; padding: 0.375rem; border: 1px solid #f1f5f9; border-radius: 6px; background: #fff; }
.cal-day-empty { background: #fafafa; }
.cal-today { border-color: #0ea5e9; background: #f0f9ff; }
.cal-day-num { font-size: 0.75rem; font-weight: 700; color: #334155; }
.cal-today .cal-day-num { color: #0ea5e9; }
.cal-events { display: flex; flex-direction: column; gap: 2px; margin-top: 2px; }
.cal-event { display: flex; align-items: center; gap: 0.25rem; padding: 2px 4px; border-radius: 4px; font-size: 0.625rem; cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cal-event-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.cal-event-text { overflow: hidden; text-overflow: ellipsis; }
.cal-event-scheduled { background: #dbeafe; }
.cal-event-scheduled .cal-event-dot { background: #2563eb; }
.cal-event-confirmed { background: #cffafe; }
.cal-event-confirmed .cal-event-dot { background: #0891b2; }
.cal-event-completed { background: #d1fae5; }
.cal-event-completed .cal-event-dot { background: #059669; }
.cal-event-cancelled { background: #fee2e2; }
.cal-event-cancelled .cal-event-dot { background: #dc2626; }
.cal-event-no_show { background: #ffedd5; }
.cal-event-no_show .cal-event-dot { background: #ea580c; }
.cal-more { font-size: 0.625rem; color: #94a3b8; padding: 2px 4px; cursor: pointer; }
</style>
