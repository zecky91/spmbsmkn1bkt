<template>
  <Teleport to="body">
    <div class="modal-backdrop" :class="{ open: show }" @click.self="closeIfCloseable">
      <div class="modal-card" :style="{ maxWidth }" v-if="show">
        <div class="px-6 pt-5 pb-3 border-b border-gray-100 relative" v-if="$slots.header">
          <slot name="header" />
          <button v-if="showCloseButton" @click="$emit('close')" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
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
  maxWidth: { type: String, default: '480px' },
  closeable: { type: Boolean, default: true },
  showCloseButton: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);

function closeIfCloseable() {
  if (props.closeable) {
    emit('close');
  }
}

function handleEscape(e) {
  if (e.key === 'Escape' && props.show && props.closeable) emit('close');
}

onMounted(() => document.addEventListener('keydown', handleEscape));
onUnmounted(() => document.removeEventListener('keydown', handleEscape));

watch(() => props.show, (val) => {
  document.body.style.overflow = val ? 'hidden' : '';
});
</script>