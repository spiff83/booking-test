<script setup>
import { ref } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'

//import { ref } from 'vue'
//import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  service: Object,
  variants: Array,
  week: Array,
  w: Number,
  prevEnabled: Boolean,
  nextEnabled: Boolean,
})

// state
const currentDate = ref('')
const chosenDateRu = ref('')
const variantId = ref(props.variants?.[0]?.id || null)

const slots = ref([])
const busy = ref([])
const showTimeline = ref(false)
const loading = ref(false)
const errorMsg = ref('')

// booking form
const selectedSlot = ref('')
const showForm = ref(false)
const clientName = ref('')
const clientPhone = ref('')
const submitting = ref(false)
const submitError = ref('')

// helpers
function barStyle(startHHMM, endHHMM) {
  if (!startHHMM || !endHHMM) return {}
  const total = 10 * 60 // 10:00..20:00
  const toOff = (hhmm) => {
    const [H, M] = (hhmm || '0:0').split(':').map(Number)
    return (H - 10) * 60 + (isNaN(M) ? 0 : M)
  }
  const s = Math.max(0, toOff(startHHMM))
  const e = Math.max(s, Math.min(total, toOff(endHHMM)))
  const left = (s / total) * 100
  const width = Math.max(0, ((e - s) / total) * 100)
  return { left: left + '%', width: width + '%', background: '#fecaca' }
}

async function loadSlots() {
  if (!currentDate.value || !variantId.value) return
  loading.value = true
  errorMsg.value = ''
  slots.value = []
  busy.value = []
  showForm.value = false
  selectedSlot.value = ''

  try {
    const url = `/services/${props.service.id}/slots?date=${encodeURIComponent(currentDate.value)}&variant_id=${variantId.value}`
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const data = await res.json()
    slots.value = data.available || []
    busy.value = data.busy || []
    showTimeline.value = true
  } catch (e) {
    console.error(e)
    errorMsg.value = 'Не удалось загрузить слоты. Попробуйте обновить страницу.'
    showTimeline.value = false
  } finally {
    loading.value = false
  }
}

function pickDay(d) {
  currentDate.value = d.ymd
  chosenDateRu.value = d.date_ru
  loadSlots()
}

function pickSlot(s) {
  selectedSlot.value = s
  showForm.value = true
  submitError.value = ''
  setTimeout(() => {
    document.getElementById('bookForm')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }, 0)
}

function submitBooking(e) {
  e.preventDefault()
  if (!selectedSlot.value || !currentDate.value || !variantId.value) return
  submitting.value = true
  submitError.value = ''

  router.post('/bookings', {
    variant_id:  variantId.value,
    date:        currentDate.value,
    start_local: selectedSlot.value,
    client_name: clientName.value,
    client_phone: clientPhone.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // сброс формы и перезагрузка доступных слотов
      showForm.value = false
      clientName.value = ''
      clientPhone.value = ''
      loadSlots()
    },
    onError: () => {
      submitError.value = 'Не удалось создать бронь. Проверьте данные и попробуйте ещё раз.'
    },
    onFinish: () => {
      submitting.value = false
    },
  })
}
</script>

<template>
<Head title="Бронирование" />
  <div class="max-w-5xl mx-auto p-4">
    <div class="mb-5 flex items-center justify-between">
      <Link href="/"
            class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
                   bg-white hover:bg-gray-50 text-gray-800 border-gray-300">← Ко всем услугам</Link>
      <a href="/admin"
         class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
                bg-red-50 hover:bg-red-100 text-red-900 border-red-200">Админка →</a>
    </div>

    <h2 class="text-xl md:text-2xl font-bold mb-2">{{ props.service.name }}</h2>

    <div class="mb-4 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 text-[13px] md:text-sm">
      К каждому бронированию автоматически добавляется технический перерыв 30 минут. Учитывайте это при выборе времени.
    </div>

    <!-- Вариант -->
    <div class="mb-4">
      <label class="text-[13px] md:text-sm text-gray-600">Вариант:</label>
      <select v-model="variantId" class="mt-1 border rounded-lg px-3 py-2 text-sm" @change="loadSlots">
        <option v-for="v in variants" :key="v.id" :value="v.id">
          {{ v.name }} ({{ v.duration_min }} + {{ v.padding_min }} мин)
        </option>
      </select>
    </div>

    <!-- Переключатель недель (Inertia Link) -->
    <div class="mb-3 flex items-center gap-2">
      <Link :href="prevEnabled ? `/services/${props.service.id}?w=${props.w-1}` : '#'"
            :class="['px-3 py-2 rounded-lg border text-sm',
                     prevEnabled ? 'bg-white hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed']"
            :preserve-state="true" :preserve-scroll="true">
        ← Пред. неделя
      </Link>
      <div class="text-[13px] md:text-sm text-gray-600">
        Неделя: {{ props.w === 0 ? 'текущая' : (props.w < 0 ? ('прошлая ' + Math.abs(props.w)) : ('следующая ' + props.w)) }}
      </div>
      <Link :href="nextEnabled ? `/services/${props.service.id}?w=${props.w+1}` : '#'"
            :class="['px-3 py-2 rounded-lg border text-sm',
                     nextEnabled ? 'bg-white hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed']"
            :preserve-state="true" :preserve-scroll="true">
        След. неделя →
      </Link>
    </div>

    <!-- Календарь недели -->
    <div class="mb-2 text-[13px] md:text-sm text-gray-600">Выберите день, когда хотите приехать</div>
    <div v-if="!week || week.length === 0"
         class="rounded-lg bg-gray-50 border px-4 py-3 text-gray-600">
      В выбранную неделю нет доступных для бронирования дней.
    </div>
    <div v-else class="mb-4 flex gap-2 flex-wrap">
      <button v-for="d in week" :key="d.ymd"
              @click="pickDay(d)"
              :class="[
                'date-btn px-3 py-2 rounded-lg border transition text-sm',
                currentDate === d.ymd ? 'bg-red-50 border-red-300 text-red-900' : 'bg-white hover:bg-gray-50'
              ]">
        {{ d.label_ru }}
      </button>
    </div>

    <!-- Таймлайн -->
    <div v-if="showTimeline" class="bg-white rounded-xl shadow p-4 mb-4">
      <div class="text-[13px] md:text-sm text-gray-600 mb-2">День: <span>{{ chosenDateRu }}</span> (МСК)</div>

      <div class="relative border-t border-b py-4">
        <div class="flex text-[10px] md:text-xs text-gray-500 justify-between mb-2">
          <span>10:00</span><span>12:00</span><span>14:00</span><span>16:00</span><span>18:00</span><span>20:00</span>
        </div>
        <div class="relative">
          <div class="flex gap-0.5">
            <div v-for="i in 20" :key="i" class="h-3 flex-1 bg-gray-100 rounded"></div>
          </div>
          <div class="absolute inset-0 h-3 pointer-events-none">
            <div v-for="(b,idx) in busy" :key="idx"
                 class="absolute top-0 h-[12px] rounded"
                 :style="barStyle(b.start, b.end)">
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4 mb-1 text-[13px] md:text-sm text-gray-600">Доступные слоты начала бронирования</div>
      <div class="mb-2 text-[12px] md:text-xs text-gray-500">Слот учитывает длительность и технический перерыв (+30 минут).</div>

      <div id="slots" class="flex flex-wrap gap-2">
        <template v-if="loading">
          <div class="text-gray-500">Загрузка…</div>
        </template>
        <template v-else-if="errorMsg">
          <div class="text-red-700">{{ errorMsg }}</div>
        </template>
        <template v-else-if="!slots || slots.length===0">
          <div class="text-gray-500">Нет доступных слотов</div>
        </template>
        <template v-else>
          <button v-for="s in slots" :key="s"
                  class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm"
                  @click="pickSlot(s)">
            {{ s }}
          </button>
        </template>
      </div>

      <!-- Единая форма бронирования -->
      <form v-if="showForm" id="bookForm" class="space-y-3 mt-4" @submit="submitBooking">
        <div class="grid md:grid-cols-2 gap-3">
          <label class="block">
            <span class="text-sm text-gray-600">Ваше имя</span>
            <input v-model="clientName" type="text" class="mt-1 w-full border rounded-lg px-3 py-2" required>
          </label>
          <label class="block">
            <span class="text-sm text-gray-600">Телефон</span>
            <input v-model="clientPhone" type="text" class="mt-1 w-full border rounded-lg px-3 py-2" required>
          </label>
        </div>
        <div class="text-sm text-gray-600">
          Вы выбрали: <b>{{ chosenDateRu }}</b>, старт в <b>{{ selectedSlot }}</b>
        </div>
        <div v-if="submitError" class="text-red-700 text-sm">{{ submitError }}</div>
        <button :disabled="submitting"
                class="inline-flex items-center justify-center bg-red-800 hover:bg-red-900 disabled:opacity-60 text-white rounded-lg px-4 py-2">
          {{ submitting ? 'Отправка…' : 'Забронировать' }}
        </button>
      </form>
    </div>
  </div>
</template>
