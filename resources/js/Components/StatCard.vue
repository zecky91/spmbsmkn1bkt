<template>
  <div class="stat-card elev-1 fade-in">
    <div class="flex items-center justify-between mb-3">
      <div class="w-11 h-11 rounded-xl flex items-center justify-center" :class="bgClass">
        <slot name="icon">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
          </svg>
        </slot>
      </div>
    </div>
    <div class="text-3xl font-extrabold" :class="textClass">{{ value }}</div>
    <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">{{ label }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: String,
  value: [Number, String],
  color: { type: String, default: 'primary' }
});

const colorMap = {
  primary: { bg: 'bg-primary', text: 'text-primary' },
  success: { bg: 'bg-success', text: 'text-success' },
  warning: { bg: 'bg-warning', text: 'text-warning' },
  danger: { bg: 'bg-danger', text: 'text-danger' },
  secondary: { bg: 'bg-secondary', text: 'text-secondary' },
  gray: { bg: 'bg-gray-400', text: 'text-gray-500' }
};

const bgClass = computed(() => (colorMap[props.color] || colorMap.primary).bg);
const textClass = computed(() => (colorMap[props.color] || colorMap.primary).text);
</script>