<script setup>
import { Link, Head } from '@inertiajs/vue3'

defineProps({
  services: Array
})

</script>

<template>
<Head title="Список услуг" />
  <div class="max-w-5xl mx-auto p-4">
    <div class="mb-6 flex items-center justify-between">
      <h2 class="text-2xl font-bold">Выберите услугу</h2>
      <a href="/admin"
         class="inline-flex items-center rounded-lg border px-3 py-2 text-sm
                bg-red-50 hover:bg-red-100 text-red-900 border-red-200">
        Админка →
      </a>
    </div>

    <div v-if="!services || services.length===0"
         class="rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3">
      Сейчас нет активных услуг для бронирования.
    </div>

    <div v-else class="space-y-5">
      <div v-for="s in services" :key="s.id" class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <div class="md:flex">
          <div class="md:w-1/3">
            <img :src="s.image_path || '/images/default.jpg'" :alt="s.name" class="w-full h-48 md:h-full object-cover">
          </div>
          <div class="md:w-2/3 p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 class="text-xl font-semibold">{{ s.name }}</h3>
                <div class="text-xs text-gray-500">Часовой пояс: {{ s.timezone || 'Europe/Moscow' }}</div>
              </div>
              <a :href="`/services/${s.id}`"
                 class="shrink-0 inline-flex items-center rounded-lg px-3 py-2 text-sm text-white
                        bg-red-800 hover:bg-red-900">
                Бронировать →
              </a>
            </div>
            <p class="mt-4 text-sm text-gray-700">
              {{ s.teaser || 'Готовы к приключению? Выберите длительность и забронируйте удобное время.' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
