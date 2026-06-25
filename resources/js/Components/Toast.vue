<template>
  <div
    class="toast font-semibold"
    :class="{ show: visible }"
    :style="{ background: bgColor, color: textColor }"
  >
    <span v-html="message"></span>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('info');
let timeoutId;

const bgColor = computed(() => {
  const colors = {
    success: '#4CAF50',
    error: '#E53935',
    danger: '#E53935',
    warning: '#FFC107',
    info: '#1E3A5F'
  };
  return colors[type.value] || colors.info;
});

const textColor = computed(() => {
  return type.value === 'warning' ? '#3a2e00' : '#fff';
});

function showToast(msg, msgType = 'info') {
  if (!msg) return;
  message.value = msg;
  type.value = msgType;
  visible.value = true;
  clearTimeout(timeoutId);
  timeoutId = setTimeout(() => {
    visible.value = false;
  }, 3000);
}

// Watch for Inertia flash messages
watch(() => page.props.flash, (flash) => {
  if (!flash) return;
  if (flash.success) showToast(flash.success, 'success');
  else if (flash.error) showToast(flash.error, 'error');
  else if (flash.warning) showToast(flash.warning, 'warning');
  else if (flash.info) showToast(flash.info, 'info');
}, { deep: true, immediate: true });
</script>
