<template>
  <div class="chart-wrapper">
    <canvas ref="canvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  labels: { type: Array, default: () => [] },
  values: { type: Array, default: () => [] },
  title: { type: String, default: '' }
})

const canvas = ref(null)
let chart = null

function generateColors(n) {
  const palette = ['#4f46e5','#06b6d4','#f97316','#ef4444','#10b981','#8b5cf6','#eab308','#7c3aed','#f472b6','#06b6d4']
  return Array.from({ length: n }, (_, i) => palette[i % palette.length])
}

function buildOptions(isDark) {
  const textColor = isDark ? '#e6eef8' : '#111827'
  const gridColor = isDark ? 'rgba(255,255,255,0.03)' : 'rgba(0,0,0,0.06)'
  return {
    responsive: true,
    plugins: {
      title: { display: !!props.title, text: props.title, color: textColor },
      legend: { labels: { color: textColor } }
    }
  }
}

onMounted(() => {
  const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
  chart = new Chart(canvas.value.getContext('2d'), {
    type: 'pie',
    data: {
      labels: props.labels,
      datasets: [{ data: props.values, backgroundColor: generateColors(props.labels.length) }]
    },
    options: buildOptions(isDark)
  })

  // observe theme (html.class) changes so charts update when theme toggles
  if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver(() => {
      const nowDark = document.documentElement.classList.contains('dark')
      chart.options = buildOptions(nowDark)
      chart.update()
    })
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
    // store observer so we can disconnect on unmount
    canvas.value.__themeObserver = observer
  }
})

watch(() => [props.labels, props.values], () => {
  if (!chart) return
  chart.data.labels = props.labels
  chart.data.datasets[0].data = props.values
  chart.data.datasets[0].backgroundColor = generateColors(props.labels.length)
  chart.update()
})

onBeforeUnmount(() => {
  if (chart) {
    chart.destroy()
    chart = null
  }
  if (canvas.value && canvas.value.__themeObserver) {
    canvas.value.__themeObserver.disconnect()
    delete canvas.value.__themeObserver
  }
})
</script>

<style scoped>
.chart-wrapper { width: 100%; height: 100%; min-height: 220px; }
canvas { width: 100% !important; height: auto !important; }
</style>
