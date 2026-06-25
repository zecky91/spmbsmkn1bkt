<template>
  <Teleport to="body">
    <div class="modal-backdrop" :class="{ open: show }" @click.self="$emit('close')">
      <div class="modal-card" :style="{ maxWidth }" v-if="show">
        <div class="px-6 pt-5 pb-3 border-b border-gray-100" v-if="$slots.header">
          <slot name="header" />
        </div>
        <div class="px-6 py-4">
          <slot />
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3" v-if="$slots.footer">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  maxWidth: { type: String, default: '480px' }
});

const emit = defineEmits(['close']);

function handleEscape(e) {
  if (e.key === 'Escape' && props.show) emit('close');
}

onMounted(() => document.addEventListener('keydown', handleEscape));
onUnmounted(() => document.removeEventListener('keydown', handleEscape));

watch(() => props.show, (val) => {
  document.body.style.overflow = val ? 'hidden' : '';
});
</script>