<template>
  <Modal :show="show" @close="$emit('close')" max-width="420px">
    <div class="text-center py-2">
      <div class="w-14 h-14 rounded-full mx-auto mb-4 flex items-center justify-center" :class="iconBg">
        <svg class="w-7 h-7" :class="iconColor" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path v-if="variant === 'danger'" d="M12 9v4m0 4h.01M12 2L2 20h20L12 2z" />
          <path v-else d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
        </svg>
      </div>
      <h3 class="text-lg font-bold text-gray-800 mb-2">{{ title }}</h3>
      <p class="text-sm text-gray-500 mb-6">{{ message }}</p>
      <div class="flex gap-3 justify-center">
        <button @click="$emit('close')" class="btn px-5 py-2.5 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 text-sm">
          {{ cancelText }}
        </button>
        <button @click="$emit('confirm')" class="btn px-5 py-2.5 rounded-xl text-white text-sm" :class="btnClass">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed } from 'vue';
import Modal from './Modal.vue';

const props = defineProps({
  show: Boolean,
  title: { type: String, default: 'Konfirmasi' },
  message: { type: String, default: 'Apakah Anda yakin?' },
  confirmText: { type: String, default: 'Ya, Lanjutkan' },
  cancelText: { type: String, default: 'Batal' },
  variant: { type: String, default: 'danger' }
});

defineEmits(['close', 'confirm']);

const iconBg = computed(() => ({
  'bg-red-50': props.variant === 'danger',
  'bg-yellow-50': props.variant === 'warning',
  'bg-blue-50': props.variant === 'primary'
}));
const iconColor = computed(() => ({
  'text-danger': props.variant === 'danger',
  'text-warning': props.variant === 'warning',
  'text-primary': props.variant === 'primary'
}));
const btnClass = computed(() => ({
  'bg-danger hover:bg-red-700': props.variant === 'danger',
  'bg-warning hover:bg-amber-600 !text-gray-800': props.variant === 'warning',
  'bg-primary hover:bg-primary-dark': props.variant === 'primary'
}));
</script>