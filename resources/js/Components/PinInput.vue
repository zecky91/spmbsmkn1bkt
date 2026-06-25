<template>
  <div class="flex gap-3 justify-center">
    <input
      v-for="(_, i) in length"
      :key="i"
      ref="inputs"
      type="text"
      inputmode="numeric"
      maxlength="1"
      class="pin-box"
      :value="digits[i] || ''"
      :disabled="disabled"
      @input="handleInput(i, $event)"
      @keydown.backspace="handleBackspace(i, $event)"
      @paste.prevent="handlePaste"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  length: { type: Number, default: 4 },
  disabled: Boolean
});

const emit = defineEmits(['update:modelValue']);
const inputs = ref([]);

const digits = computed(() => props.modelValue.split(''));

function handleInput(index, event) {
  const val = event.target.value.replace(/\D/g, '');
  if (!val) return;
  const chars = props.modelValue.split('');
  chars[index] = val[0];
  const newVal = chars.join('').slice(0, props.length);
  emit('update:modelValue', newVal);
  if (index < props.length - 1) {
    nextTick(() => inputs.value[index + 1]?.focus());
  }
}

function handleBackspace(index, event) {
  if (!event.target.value && index > 0) {
    const chars = props.modelValue.split('');
    chars[index - 1] = '';
    emit('update:modelValue', chars.join(''));
    nextTick(() => inputs.value[index - 1]?.focus());
  }
}

function handlePaste(event) {
  const text = (event.clipboardData?.getData('text') || '').replace(/\D/g, '').slice(0, props.length);
  emit('update:modelValue', text);
  nextTick(() => {
    const focusIdx = Math.min(text.length, props.length - 1);
    inputs.value[focusIdx]?.focus();
  });
}
</script>